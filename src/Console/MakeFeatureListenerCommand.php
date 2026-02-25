<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;

class MakeFeatureListenerCommand extends Command
{
    protected $signature = 'make:feature-listener
                            {name : The feature and listener name (e.g. Messaging/SendJourneyNotification, Events/LogEvent)}';

    protected $description = 'Create a new event listener class inside an existing feature (Features/FeatureName/Listeners)';

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if (strpos($name, '/') === false) {
            $this->error('The name must be in the form Feature/ListenerName (e.g. Messaging/SendJourneyNotification).');
            return self::FAILURE;
        }

        [$featureName, $listenerName] = explode('/', $name, 2);
        $featureName = trim($featureName);
        $listenerName = trim($listenerName);

        if (! $this->isValidFeatureName($featureName) || ! $this->isValidClassName($listenerName)) {
            $this->error('Feature and ListenerName must be PascalCase (letters and numbers only).');
            return self::FAILURE;
        }

        $featurePath = app_path('Features/' . $featureName);
        if (! is_dir($featurePath)) {
            $this->error("Feature [{$featureName}] does not exist. Create it with: php artisan make:feature {$featureName}");
            return self::FAILURE;
        }

        $this->ensureListenersDirectoryExists($featurePath);

        $listenerPath = $featurePath . '/Listeners/' . $listenerName . '.php';
        if (file_exists($listenerPath)) {
            $this->warn("Listener [{$listenerName}] already exists at app/Features/{$featureName}/Listeners/{$listenerName}.php");
            return self::SUCCESS;
        }

        $featureNamespace = 'App\\Features\\' . $featureName;
        $content = $this->getStubContent($featureNamespace, $listenerName);
        file_put_contents($listenerPath, $content);

        $this->info('Listener created successfully.');
        $this->line('  <comment>created</comment> app/Features/' . $featureName . '/Listeners/' . $listenerName . '.php');

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

    private function ensureListenersDirectoryExists(string $featurePath): void
    {
        $listenersPath = $featurePath . '/Listeners';
        if (! is_dir($listenersPath)) {
            mkdir($listenersPath, 0755, true);
        }
    }

    private function getStubContent(string $featureNamespace, string $listenerName): string
    {
        $stubPath = __DIR__ . '/../Stubs/listener.stub';
        $stub = file_get_contents($stubPath);
        return str_replace(
            ['{{FeatureNamespace}}', '{{ListenerName}}'],
            [$featureNamespace, $listenerName],
            $stub
        );
    }
}
