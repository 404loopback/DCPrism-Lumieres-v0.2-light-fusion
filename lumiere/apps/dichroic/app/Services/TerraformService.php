<?php

namespace App\Services;

use App\Models\InfrastructureDeployment;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class TerraformService
{
    private string $terraformPath;
    private string $workingDirectory;

    public function __construct()
    {
        $this->terraformPath = config('app.terraform_path', '/terraform');
        $this->workingDirectory = storage_path('terraform-workspaces');
        
        // Create working directory if it doesn't exist
        if (!is_dir($this->workingDirectory)) {
            mkdir($this->workingDirectory, 0755, true);
        }
    }

    /**
     * Generate Terraform configuration for a deployment
     */
    public function generateConfiguration(InfrastructureDeployment $deployment): string
    {
        $config = $deployment->generateTerraformConfig();
        
        // Create workspace directory
        $workspaceId = $this->getWorkspaceId($deployment);
        $workspacePath = $this->getWorkspacePath($deployment);
        
        if (!is_dir($workspacePath)) {
            mkdir($workspacePath, 0755, true);
        }

        // Generate terraform.tfvars content
        $tfvarsContent = $this->generateTfvarsContent($config);
        
        // Write configuration files
        file_put_contents($workspacePath . '/terraform.tfvars', $tfvarsContent);
        
        // Copy main configuration files
        $this->copyTerraformFiles($workspacePath, $deployment->scenario);
        
        return $workspacePath;
    }

    /**
     * Run terraform init
     */
    public function init(InfrastructureDeployment $deployment): array
    {
        $workspacePath = $this->generateConfiguration($deployment);
        
        $result = Process::path($workspacePath)
            ->run(['terraform', 'init', '-no-color']);
            
        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
        ];
    }

    /**
     * Run terraform plan
     */
    public function plan(InfrastructureDeployment $deployment): array
    {
        $workspacePath = $this->getWorkspacePath($deployment);
        
        $result = Process::path($workspacePath)
            ->timeout(300) // 5 minutes
            ->run(['terraform', 'plan', '-no-color', '-out=plan.tfplan']);
            
        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
        ];
    }

    /**
     * Run terraform apply
     */
    public function apply(InfrastructureDeployment $deployment): array
    {
        $workspacePath = $this->getWorkspacePath($deployment);
        
        // Update deployment status
        $deployment->update(['status' => InfrastructureDeployment::STATUS_DEPLOYING]);
        
        $result = Process::path($workspacePath)
            ->timeout(1800) // 30 minutes
            ->run(['terraform', 'apply', '-auto-approve', '-no-color', 'plan.tfplan']);
            
        if ($result->successful()) {
            // Get outputs
            $outputsResult = Process::path($workspacePath)
                ->run(['terraform', 'output', '-json']);
                
            $outputs = $outputsResult->successful() 
                ? json_decode($outputsResult->output(), true) 
                : [];
                
            // Update deployment
            $deployment->update([
                'status' => InfrastructureDeployment::STATUS_DEPLOYED,
                'terraform_outputs' => $outputs,
                'deployed_at' => now(),
            ]);
        } else {
            $deployment->update(['status' => InfrastructureDeployment::STATUS_FAILED]);
        }
            
        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
        ];
    }

    /**
     * Run terraform destroy
     */
    public function destroy(InfrastructureDeployment $deployment): array
    {
        $workspacePath = $this->getWorkspacePath($deployment);
        
        // Update deployment status
        $deployment->update(['status' => InfrastructureDeployment::STATUS_DESTROYING]);
        
        $result = Process::path($workspacePath)
            ->timeout(1800) // 30 minutes
            ->run(['terraform', 'destroy', '-auto-approve', '-no-color']);
            
        if ($result->successful()) {
            $deployment->update([
                'status' => InfrastructureDeployment::STATUS_DESTROYED,
                'destroyed_at' => now(),
            ]);
        } else {
            $deployment->update(['status' => InfrastructureDeployment::STATUS_FAILED]);
        }
            
        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
        ];
    }

    /**
     * Get workspace ID for a deployment
     */
    private function getWorkspaceId(InfrastructureDeployment $deployment): string
    {
        return 'deployment_' . $deployment->id . '_' . Str::slug($deployment->name);
    }

    /**
     * Get workspace path for a deployment
     */
    private function getWorkspacePath(InfrastructureDeployment $deployment): string
    {
        $workspaceId = $this->getWorkspaceId($deployment);
        return $this->workingDirectory . '/' . $workspaceId;
    }

    /**
     * Generate terraform.tfvars content
     */
    private function generateTfvarsContent(array $config): string
    {
        $content = "# Generated by DCParty Infrastructure Wizard\n";
        $content .= "# Generated at: " . now()->toISOString() . "\n\n";
        
        foreach ($config as $key => $value) {
            if (is_string($value)) {
                $content .= "{$key} = \"{$value}\"\n";
            } elseif (is_bool($value)) {
                $content .= "{$key} = " . ($value ? 'true' : 'false') . "\n";
            } elseif (is_numeric($value)) {
                $content .= "{$key} = {$value}\n";
            } elseif (is_array($value)) {
                $content .= "{$key} = " . json_encode($value) . "\n";
            }
        }
        
        return $content;
    }

    /**
     * Copy Terraform configuration files to workspace
     */
    private function copyTerraformFiles(string $workspacePath, string $scenario): void
    {
        $sourcePath = base_path('../terraform');
        
        // Copy main files
        if (file_exists($sourcePath . '/main-new.tf')) {
            copy($sourcePath . '/main-new.tf', $workspacePath . '/main.tf');
        }
        
        if (file_exists($sourcePath . '/variables-new.tf')) {
            copy($sourcePath . '/variables-new.tf', $workspacePath . '/variables.tf');
        }
        
        if (file_exists($sourcePath . '/backend.tf')) {
            copy($sourcePath . '/backend.tf', $workspacePath . '/backend.tf');
        }
        
        // Copy modules and scenarios (create symlinks for better performance)
        $modulesSource = $sourcePath . '/modules';
        $modulesTarget = $workspacePath . '/modules';
        
        $scenariosSource = $sourcePath . '/scenarios';
        $scenariosTarget = $workspacePath . '/scenarios';
        
        if (is_dir($modulesSource) && !is_dir($modulesTarget)) {
            symlink($modulesSource, $modulesTarget);
        }
        
        if (is_dir($scenariosSource) && !is_dir($scenariosTarget)) {
            symlink($scenariosSource, $scenariosTarget);
        }
    }

    /**
     * Validate Terraform configuration
     */
    public function validate(InfrastructureDeployment $deployment): array
    {
        $workspacePath = $this->getWorkspacePath($deployment);
        
        $result = Process::path($workspacePath)
            ->run(['terraform', 'validate', '-no-color']);
            
        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
        ];
    }

    /**
     * Clean up workspace
     */
    public function cleanup(InfrastructureDeployment $deployment): void
    {
        $workspacePath = $this->getWorkspacePath($deployment);
        
        if (is_dir($workspacePath)) {
            // Remove directory recursively
            $this->removeDirectory($workspacePath);
        }
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
}
