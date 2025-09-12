<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Parameter;

class CreateBasicParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parameters:create-basic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create basic parameters for festivals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating basic parameters...');

        $parameters = [
            [
                'name' => 'Resolution',
                'code' => 'RES',
                'type' => Parameter::TYPE_STRING,
                'category' => Parameter::CATEGORY_TECHNICAL,
                'possible_values' => ['2K', '4K', 'HD'],
                'default_value' => '2K',
                'description' => 'Resolution technique du DCP',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Language',
                'code' => 'LANG',
                'type' => Parameter::TYPE_STRING,
                'category' => Parameter::CATEGORY_AUDIO,
                'possible_values' => ['FR', 'EN', 'ES', 'DE', 'IT'],
                'default_value' => 'FR',
                'description' => 'Langue principale du film',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Aspect Ratio',
                'code' => 'AR',
                'type' => Parameter::TYPE_STRING,
                'category' => Parameter::CATEGORY_VIDEO,
                'possible_values' => ['1.85', '2.39', '1.33', '1.78'],
                'default_value' => '1.85',
                'description' => 'Format d\'image du film',
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Sound Format',
                'code' => 'SOUND',
                'type' => Parameter::TYPE_STRING,
                'category' => Parameter::CATEGORY_AUDIO,
                'possible_values' => ['5.1', '7.1', 'STEREO', 'MONO'],
                'default_value' => '5.1',
                'description' => 'Format sonore du DCP',
                'is_required' => false,
                'is_active' => true,
            ],
        ];

        foreach ($parameters as $paramData) {
            $param = Parameter::firstOrCreate(
                ['name' => $paramData['name']],
                $paramData
            );
            
            if ($param->wasRecentlyCreated) {
                $this->info("Created parameter: {$paramData['name']}");
            } else {
                $this->comment("Parameter already exists: {$paramData['name']}");
            }
        }

        $this->info('Basic parameters created successfully!');
        return 0;
    }
}
