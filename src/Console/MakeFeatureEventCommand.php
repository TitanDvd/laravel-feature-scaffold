<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;

class MakeFeatureEventCommand extends Command
{
    protected $signature = 'make:feature-event
                            {name : The feature and event name (e.g. Messaging/JourneyCompleted, Events/EventRecorded)}';

    protected $description = 'Create a new event class inside an existing feature (Features/FeatureName/Events)';

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if (strpos($name, '/') === false) {
            $this->error('The name must be in the form Feature/EventName (e.g. Messaging/JourneyCompleted).');
            return self::FAILURE;
        }

        [$featureName, $eventName] = explode('/', $name, 2);
        $featureName = trim($featureName);
        $eventName = trim($eventName);

        if (! $this->isValidFeatureName($featureName) || ! $this->isValidClassName($eventName)) {
            $this->error('Feature and EventName must be PascalCase (letters and numbers only).');
            return self::FAILURE;
        }

        $featurePath = app_path('Features/' . $featureName);
        if (! is_dir($featurePath)) {
            $this->error("Feature [{$featureName}] does not exist. Create it with: php artisan make:feature {$featureName}");
            return self::FAILURE;
        }

        $this->ensureEventsDirectoryExists($featurePath);

        $eventPath = $featurePath . '/Events/' . $eventName . '.php';
        if (file_exists($eventPath)) {
            $this->warn("Event [{$eventName}] already exists at app/Features/{$featureName}/Events/{$eventName}.php");
            return self::SUCCESS;
        }

        $featureNamespace = 'App\\Features\\' . $featureName;
        $content = $this->getStubContent($featureNamespace, $eventName);
        file_put_contents($eventPath, $content);

        $this->info('Event created successfully.');
        $this->line('  <comment>created</comment> app/Features/' . $featureName . '/Events/' . $eventName . '.php');

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

    private function ensureEventsDirectoryExists(string $featurePath): void
    {
        $eventsPath = $featurePath . '/Events';
        if (! is_dir($eventsPath)) {
            mkdir($eventsPath, 0755, true);
        }
    }

    private function getStubContent(string $featureNamespace, string $eventName): string
    {
        $stubPath = __DIR__ . '/../Stubs/event.stub';
        $stub = file_get_contents($stubPath);
        return str_replace(
            ['{{FeatureNamespace}}', '{{EventName}}'],
            [$featureNamespace, $eventName],
            $stub
        );
    }
}
