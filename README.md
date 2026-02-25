# MMT Laravel Feature Scaffold

Artisan commands to scaffold a feature-based directory structure for Laravel applications, following the pattern used in `app/Features/Events`.

## Requirements

- PHP ^8.2
- Laravel ^11.0 or ^12.0
- Illuminate Support ^11.0 or ^12.0

## Commands

### make:feature

Creates the directory structure for a new feature under `app/Features/{Name}/`.

**Usage:**

```bash
# Create only the directory structure
php artisan make:feature Events

# Create directories and generate stub files (model, repository contract, use case, command, request, controller, enum)
php artisan make:feature Events -f
# or
php artisan make:feature Events --files
```

**Directories created:**

- Console
- Enums
- Events
- Factories
- Http/V1/Commands
- Http/V1/Controllers
- Http/V1/Requests
- Jobs
- Listeners
- Models
- Repositories/Contracts
- Services
- UseCases

With `-f` / `--files`, the command also generates placeholder classes (e.g. `EventLog`, `StoreEventCommand`, `EventIngestController`) that you can customize.

### make:feature-model

Creates an Eloquent model inside an existing feature.

**Usage:**

```bash
php artisan make:feature-model Event/EventLog
php artisan make:feature-model Events/NotificationLog
```

The feature (e.g. `Event` or `Events`) must already exist. The model is created at `app/Features/{Feature}/Models/{ModelName}.php`.

### make:feature-job

Creates a queued job class inside an existing feature. If the feature does not have `Jobs`, `Events`, or `Listeners` directories, they are created.

**Usage:**

```bash
php artisan make:feature-job Messaging/ProcessJourneyStep
php artisan make:feature-job Events/SendEventNotification
```

The job is created at `app/Features/{Feature}/Jobs/{JobName}.php` and implements `ShouldQueue` with the `Queueable` trait.

### make:feature-command

Creates an Artisan console command inside an existing feature (`Features/FeatureName/Console`). The command signature is generated as `{feature-kebab}:{command-kebab}` (e.g. `Messaging/ProcessJourneyStep` → `messaging:process-journey-step`).

**Usage:**

```bash
php artisan make:feature-command Messaging/ProcessJourneyStep
php artisan make:feature-command Events/SendDigest
```

The class is created at `app/Features/{Feature}/Console/{CommandName}.php`. Register the feature's `Console` directory in your app (e.g. `bootstrap/app.php` or a service provider) so Laravel loads these commands.

### make:feature-event

Creates an event class inside an existing feature (`Features/FeatureName/Events`).

**Usage:**

```bash
php artisan make:feature-event Messaging/JourneyCompleted
php artisan make:feature-event Events/EventRecorded
```

The event is created at `app/Features/{Feature}/Events/{EventName}.php` with `Dispatchable` and `SerializesModels`.

### make:feature-listener

Creates a queued event listener inside an existing feature (`Features/FeatureName/Listeners`).

**Usage:**

```bash
php artisan make:feature-listener Messaging/SendJourneyNotification
php artisan make:feature-listener Events/LogEvent
```

The listener is created at `app/Features/{Feature}/Listeners/{ListenerName}.php`, implements `ShouldQueue`, and has a `handle(object $event)` method.

### make:feature-service

Creates a service class inside an existing feature (`Features/FeatureName/Services`).

**Usage:**

```bash
php artisan make:feature-service Messaging/SegmentEvaluatorService
php artisan make:feature-service Events/EventIngestService
```

The service is created at `app/Features/{Feature}/Services/{ServiceName}.php` as a plain class in the `Services` namespace.

## Installation

### Via Packagist (recommended)

```bash
composer require mmt/laravel-feature-scaffold
```

Laravel will **auto-discover** the package's service provider (via `extra.laravel.providers` in the package's `composer.json`). The Artisan commands will be available immediately after `composer install` or `composer update`. No need to register the provider manually.

### Local / development (PSR-4 only)

If you use the package from a local path (e.g. `packages/mmt/laravel-feature-scaffold`) without adding it as a Composer dependency, auto-discovery does not run. You must:

1. **Autoload** – In your Laravel project `composer.json`, add the package path to `autoload.psr-4`:

   ```json
   "autoload": {
       "psr-4": {
           "MMT\\LaravelFeatureScaffold\\": "packages/mmt/laravel-feature-scaffold/src/"
       }
   }
   ```

2. **Register the service provider** – Add it in `bootstrap/providers.php` (create the file if it does not exist):

   ```php
   <?php

   return [
       \MMT\LaravelFeatureScaffold\LaravelFeatureScaffoldServiceProvider::class,
   ];
   ```

3. **Regenerate autoload:**

   ```bash
   composer dump-autoload
   ```

The commands `make:feature`, `make:feature-model`, `make:feature-job`, `make:feature-command`, `make:feature-event`, `make:feature-listener`, and `make:feature-service` will then be available.

## Package structure

```
packages/mmt/laravel-feature-scaffold/
├── composer.json
├── README.md
└── src/
    ├── LaravelFeatureScaffoldServiceProvider.php
    ├── Console/
    │   ├── MakeFeatureCommand.php
    │   ├── MakeFeatureModelCommand.php
    │   ├── MakeFeatureJobCommand.php
    │   ├── MakeFeatureArtisanCommand.php
    │   ├── MakeFeatureEventCommand.php
    │   ├── MakeFeatureListenerCommand.php
    │   └── MakeFeatureServiceCommand.php
    └── Stubs/
        ├── console-command.stub
        ├── event.stub
        ├── listener.stub
        ├── service.stub
        ├── job.stub
        ├── model.stub
        ├── repository-contract.stub
        ├── use-case.stub
        ├── command.stub
        ├── request.stub
        ├── controller.stub
        └── enum.stub
```

## License

MIT
