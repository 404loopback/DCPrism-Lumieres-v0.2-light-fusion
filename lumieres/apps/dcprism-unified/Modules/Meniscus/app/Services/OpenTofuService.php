<?php

namespace Modules\Meniscus\app\Services\Infrastructure;

class OpenTofuService
{
    public function plan($configPath, $vars = [])
    {
        return ['success' => false, 'message' => 'OpenTofu plan not implemented yet'];
    }

    public function apply($configPath, $vars = [])
    {
        return ['success' => false, 'message' => 'OpenTofu apply not implemented yet'];
    }

    public function destroy($configPath, $vars = [])
    {
        return ['success' => false, 'message' => 'OpenTofu destroy not implemented yet'];
    }

    public function validate($configPath)
    {
        return ['valid' => false, 'errors' => []];
    }

    public function getState($workspace = 'default')
    {
        return [];
    }

    public function import($address, $id, $configPath)
    {
        return ['success' => false, 'message' => 'OpenTofu import not implemented yet'];
    }

    public function getWorkspaces()
    {
        return ['default'];
    }

    public function createWorkspace($name)
    {
        return ['success' => false, 'message' => 'Workspace creation not implemented yet'];
    }

    public function selectWorkspace($name)
    {
        return ['success' => false, 'message' => 'Workspace selection not implemented yet'];
    }

    public function getOutput($workspace = 'default')
    {
        return [];
    }

    public function getVersion()
    {
        return 'unknown';
    }
}
