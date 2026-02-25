<?php

namespace MMT\LaravelFeatureScaffold\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeFeatureCommand extends Command
{
    protected $signature = 'make:feature
                            {name : The name of the feature (e.g. Events, Notifications)}
                            {--f|--files : Generate stub files in addition to directories}';

    protected $description = 'Create the directory structure for a new feature (optionally with stub files)';

    private string $featureBasePath;

    private string $featureName;

    private string $featureSingular;

    private string $featureNamespace;

    public function handle(): int
    {
        $this->featureName = trim($this->argument('name'));

        if (! $this->isValidName($this->featureName)) {
            $this->error('The name must be PascalCase (letters and numbers only, no spaces).');
            return self::FAILURE;
        }

        $this->featureBasePath = app_path('Features/' . $this->featureName);
        $this->featureNamespace = 'App\\Features\\' . $this->featureName;
        $this->featureSingular = Str::singular($this->featureName);

        if (is_dir($this->featureBasePath)) {
            $this->warn("Feature [{$this->featureName}] already exists.");
            if (! $this->option('files')) {
                return self::SUCCESS;
            }
        }

        $this->createDirectories();
        $created = $this->getCreatedDirectories();

        if ($this->option('files')) {
            $created = array_merge($created, $this->generateFiles());
        }

        $this->info('Feature structure created successfully.');
        foreach ($created as $item) {
            $this->line('  <comment>created</comment> ' . $item);
        }

        return self::SUCCESS;
    }

    private function isValidName(string $name): bool
    {
        return $name !== '' && preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }

    private function getDirectories(): array
    {
        return [
            'Console',
            'Enums',
            'Events',
            'Factories',
            'Http/V1/Commands',
            'Http/V1/Controllers',
            'Http/V1/Requests',
            'Jobs',
            'Listeners',
            'Models',
            'Repositories/Contracts',
            'Services',
            'UseCases',
        ];
    }

    private function createDirectories(): void
    {
        foreach ($this->getDirectories() as $dir) {
            $path = $this->featureBasePath . '/' . $dir;
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    private function getCreatedDirectories(): array
    {
        $list = [];
        foreach ($this->getDirectories() as $dir) {
            $list[] = 'app/Features/' . $this->featureName . '/' . $dir;
        }
        return $list;
    }

    private function getReplacements(): array
    {
        $singular = $this->featureSingular;
        return [
            '{{FeatureNamespace}}'   => $this->featureNamespace,
            '{{FeatureName}}'        => $this->featureName,
            '{{FeatureSingular}}'    => $singular,
            '{{ModelName}}'          => $singular . 'Log',
            '{{StoreCommandName}}'   => 'Store' . $singular . 'Command',
            '{{StoreRequestName}}'   => 'Store' . $singular . 'Request',
            '{{StoreUseCaseName}}'   => 'Store' . $singular . 'UseCase',
            '{{ControllerName}}'     => $singular . 'IngestController',
            '{{EnumName}}'           => $singular . 'StatusEnum',
        ];
    }

    private function getStubsPath(): string
    {
        return __DIR__ . '/../Stubs';
    }

    private function generateFiles(): array
    {
        $replacements = $this->getReplacements();
        $stubsPath = $this->getStubsPath();
        $created = [];

        $files = [
            'model.stub' => 'Models/{{ModelName}}.php',
            'repository-contract.stub' => 'Repositories/Contracts/{{ModelName}}RepositoryInterface.php',
            'use-case.stub' => 'UseCases/{{StoreUseCaseName}}.php',
            'command.stub' => 'Http/V1/Commands/{{StoreCommandName}}.php',
            'request.stub' => 'Http/V1/Requests/{{StoreRequestName}}.php',
            'controller.stub' => 'Http/V1/Controllers/{{ControllerName}}.php',
            'enum.stub' => 'Enums/{{EnumName}}.php',
        ];

        foreach ($files as $stubFile => $relativeTarget) {
            $targetPath = $this->featureBasePath . '/' . str_replace(
                array_keys($replacements),
                array_values($replacements),
                $relativeTarget
            );
            $stub = file_get_contents($stubsPath . '/' . $stubFile);
            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $stub
            );
            if (! file_exists($targetPath)) {
                file_put_contents($targetPath, $content);
                $created[] = 'app/Features/' . $this->featureName . '/' . str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $relativeTarget
                );
            }
        }

        return $created;
    }
}
