<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;

class MakeFeatureServiceCommand extends Command
{
    protected $signature = 'make:feature-service
                            {name : The feature and service name (e.g. Messaging/SegmentEvaluatorService, Events/EventIngestService)}';

    protected $description = 'Create a new service class inside an existing feature (Features/FeatureName/Services)';

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if (strpos($name, '/') === false) {
            $this->error('The name must be in the form Feature/ServiceName (e.g. Messaging/SegmentEvaluatorService).');
            return self::FAILURE;
        }

        [$featureName, $serviceName] = explode('/', $name, 2);
        $featureName = trim($featureName);
        $serviceName = trim($serviceName);

        if (! $this->isValidFeatureName($featureName) || ! $this->isValidClassName($serviceName)) {
            $this->error('Feature and ServiceName must be PascalCase (letters and numbers only).');
            return self::FAILURE;
        }

        $featurePath = app_path('Features/' . $featureName);
        if (! is_dir($featurePath)) {
            $this->error("Feature [{$featureName}] does not exist. Create it with: php artisan make:feature {$featureName}");
            return self::FAILURE;
        }

        $this->ensureServicesDirectoryExists($featurePath);

        $servicePath = $featurePath . '/Services/' . $serviceName . '.php';
        if (file_exists($servicePath)) {
            $this->warn("Service [{$serviceName}] already exists at app/Features/{$featureName}/Services/{$serviceName}.php");
            return self::SUCCESS;
        }

        $featureNamespace = 'App\\Features\\' . $featureName;
        $content = $this->getStubContent($featureNamespace, $serviceName);
        file_put_contents($servicePath, $content);

        $this->info('Service created successfully.');
        $this->line('  <comment>created</comment> app/Features/' . $featureName . '/Services/' . $serviceName . '.php');

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

    private function ensureServicesDirectoryExists(string $featurePath): void
    {
        $servicesPath = $featurePath . '/Services';
        if (! is_dir($servicesPath)) {
            mkdir($servicesPath, 0755, true);
        }
    }

    private function getStubContent(string $featureNamespace, string $serviceName): string
    {
        $stubPath = __DIR__ . '/../Stubs/service.stub';
        $stub = file_get_contents($stubPath);
        return str_replace(
            ['{{FeatureNamespace}}', '{{ServiceName}}'],
            [$featureNamespace, $serviceName],
            $stub
        );
    }
}
