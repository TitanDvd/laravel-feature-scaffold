<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;

class MakeFeatureModelCommand extends Command
{
    protected $signature = 'make:feature-model
                            {name : The feature and model name (e.g. Event/EventLog, Events/NotificationLog)}';

    protected $description = 'Create a new Eloquent model inside an existing feature';

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if (strpos($name, '/') === false) {
            $this->error('The name must be in the form Feature/ModelName (e.g. Event/EventLog).');
            return self::FAILURE;
        }

        [$featureName, $modelName] = explode('/', $name, 2);
        $featureName = trim($featureName);
        $modelName = trim($modelName);

        if (! $this->isValidFeatureName($featureName) || ! $this->isValidModelName($modelName)) {
            $this->error('Feature and ModelName must be PascalCase (letters and numbers only).');
            return self::FAILURE;
        }

        $featurePath = app_path('Features/' . $featureName);
        if (! is_dir($featurePath)) {
            $this->error("Feature [{$featureName}] does not exist. Create it with: php artisan make:feature {$featureName}");
            return self::FAILURE;
        }

        $modelsPath = $featurePath . '/Models';
        if (! is_dir($modelsPath)) {
            mkdir($modelsPath, 0755, true);
        }

        $modelPath = $modelsPath . '/' . $modelName . '.php';
        if (file_exists($modelPath)) {
            $this->warn("Model [{$modelName}] already exists at app/Features/{$featureName}/Models/{$modelName}.php");
            return self::SUCCESS;
        }

        $featureNamespace = 'App\\Features\\' . $featureName;
        $content = $this->getModelStubContent($featureNamespace, $modelName);
        file_put_contents($modelPath, $content);

        $this->info('Model created successfully.');
        $this->line('  <comment>created</comment> app/Features/' . $featureName . '/Models/' . $modelName . '.php');

        return self::SUCCESS;
    }

    private function isValidFeatureName(string $name): bool
    {
        return $name !== '' && preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }

    private function isValidModelName(string $name): bool
    {
        return $name !== '' && preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }

    private function getModelStubContent(string $featureNamespace, string $modelName): string
    {
        $stubPath = __DIR__ . '/../Stubs/model.stub';
        $stub = file_get_contents($stubPath);
        return str_replace(
            ['{{FeatureNamespace}}', '{{ModelName}}'],
            [$featureNamespace, $modelName],
            $stub
        );
    }
}
