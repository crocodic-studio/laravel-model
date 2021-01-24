<?php
namespace Crocodic\LaravelModel;

use Crocodic\LaravelModel\Commands\MakeModel;
use Crocodic\LaravelModel\Core\LaravelModelTemporary;
use Illuminate\Support\ServiceProvider;

class LaravelModelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */

    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('LaravelModel', function () {
            return true;
        });

        $this->commands([ MakeModel::class ]);

        $this->app->singleton('LaravelModelTemporary',LaravelModelTemporary::class);

    }

}
