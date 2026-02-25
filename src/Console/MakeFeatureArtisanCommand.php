<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeFeatureArtisanCommand extends Command
{
    protected $signature = 'make:feature-command
                            {name : The feature and command name (e.g. Messaging/ProcessJourneyStep, Events/SendDigest)}';

    protected $description = 'Create a new Artisan command inside an existing feature (Features/FeatureName/Console)';

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if (strpos($name, '/') === false) {
            $this->error('The name must be in the form Feature/CommandName (e.g. Messaging/ProcessJourneyStep).');
            return self::FAILURE;
        }

        [$featureName, $commandName] = explode('/', $name, 2);
        $featureName = trim($featureName);
        $commandName = trim($commandName);

        if (! $this->isValidFeatureName($featureName) || ! $this->isValidClassName($commandName)) {
            $this->error('Feature and CommandName must be PascalCase (letters and numbers only).');
            return self::FAILURE;
        }

        $featurePath = app_path('Features/' . $featureName);
        if (! is_dir($featurePath)) {
            $this->error("Feature [{$featureName}] does not exist. Create it with: php artisan make:feature {$featureName}");
            return self::FAILURE;
        }

        $consolePath = $featurePath . '/Console';
        if (! is_dir($consolePath)) {
            mkdir($consolePath, 0755, true);
        }

        $commandPath = $consolePath . '/' . $commandName . '.php';
        if (file_exists($commandPath)) {
            $this->warn("Console command [{$commandName}] already exists at app/Features/{$featureName}/Console/{$commandName}.php");
            return self::SUCCESS;
        }

        $featureNamespace = 'App\\Features\\' . $featureName;
        $commandSignature = Str::kebab($featureName) . ':' . Str::kebab($commandName);
        $content = $this->getStubContent($featureNamespace, $commandName, $commandSignature);
        file_put_contents($commandPath, $content);

        $this->info('Console command created successfully.');
        $this->line('  <comment>created</comment> app/Features/' . $featureName . '/Console/' . $commandName . '.php');

        return self::SUCCESS;
    }

    private function isValidFeatureName(string $name): bool
    {
        return $name !== '' && preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }

    private function isValidClassName(string $name): bool
    {
        return $name !== '' && preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }

    private function getStubContent(string $featureNamespace, string $commandName, string $commandSignature): string
    {
        $stubPath = __DIR__ . '/../Stubs/console-command.stub';
        $stub = file_get_contents($stubPath);
        return str_replace(
            ['{{FeatureNamespace}}', '{{CommandName}}', '{{command_signature}}'],
            [$featureNamespace, $commandName, $commandSignature],
            $stub
        );
    }
}
