<?php
namespace Crocodic\LaravelModel;

use Crocodic\LaravelModel\Commands\MakeModel;
use Crocodic\LaravelModel\Core\LaravelModelTemporary;
use Crocodic\LaravelModel\Helpers\BuilderMacro;
use Crocodic\LaravelModel\Helpers\Helper;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar;
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

        BuilderMacro::registerMacro();
    }
}
