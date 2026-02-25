<?php

namespace MMT\LaravelFeatureScaffold;

use Illuminate\Support\ServiceProvider;

class LaravelFeatureScaffoldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MakeFeatureCommand::class,
                Console\MakeFeatureModelCommand::class,
                Console\MakeFeatureJobCommand::class,
                Console\MakeFeatureArtisanCommand::class,
                Console\MakeFeatureEventCommand::class,
                Console\MakeFeatureListenerCommand::class,
                Console\MakeFeatureServiceCommand::class,
            ]);
        }
    }
}
