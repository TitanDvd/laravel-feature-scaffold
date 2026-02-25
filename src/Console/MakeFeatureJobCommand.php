<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;

class MakeFeatureJobCommand extends Command
{
    protected $signature = 'make:feature-job
                            {name : The feature and job name (e.g. Messaging/ProcessJourneyStep, Events/SendEventNotification)}';

    protected $description = 'Create a new job class inside an existing feature';

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if (strpos($name, '/') === false) {
            $this->error('The name must be in the form Feature/JobName (e.g. Messaging/ProcessJourneyStep).');
            return self::FAILURE;
        }

        [$featureName, $jobName] = explode('/', $name, 2);
        $featureName = trim($featureName);
        $jobName = trim($jobName);

        if (! $this->isValidFeatureName($featureName) || ! $this->isValidClassName($jobName)) {
            $this->error('Feature and JobName must be PascalCase (letters and numbers only).');
            return self::FAILURE;
        }

        $featurePath = app_path('Features/' . $featureName);
        if (! is_dir($featurePath)) {
            $this->error("Feature [{$featureName}] does not exist. Create it with: php artisan make:feature {$featureName}");
            return self::FAILURE;
        }

        $this->ensureDirectoriesExist($featurePath);

        $jobsPath = $featurePath . '/Jobs';
        $jobPath = $jobsPath . '/' . $jobName . '.php';
        if (file_exists($jobPath)) {
            $this->warn("Job [{$jobName}] already exists at app/Features/{$featureName}/Jobs/{$jobName}.php");
            return self::SUCCESS;
        }

        $featureNamespace = 'App\\Features\\' . $featureName;
        $content = $this->getJobStubContent($featureNamespace, $jobName);
        file_put_contents($jobPath, $content);

        $this->info('Job created successfully.');
        $this->line('  <comment>created</comment> app/Features/' . $featureName . '/Jobs/' . $jobName . '.php');

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

    private function ensureDirectoriesExist(string $featurePath): void
    {
        foreach (['Jobs', 'Events', 'Listeners'] as $dir) {
            $path = $featurePath . '/' . $dir;
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    private function getJobStubContent(string $featureNamespace, string $jobName): string
    {
        $stubPath = __DIR__ . '/../Stubs/job.stub';
        $stub = file_get_contents($stubPath);
        return str_replace(
            ['{{FeatureNamespace}}', '{{JobName}}'],
            [$featureNamespace, $jobName],
            $stub
        );
    }
}
