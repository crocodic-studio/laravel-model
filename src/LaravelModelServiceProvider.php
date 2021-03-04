<?php
namespace Crocodic\LaravelModel;

use Crocodic\LaravelModel\Commands\MakeModel;
use Crocodic\LaravelModel\Core\LaravelModelTemporary;
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

        Builder::macro('with', function($table, $first, $foreignKey) {
            $result = $this->getConnection()
                ->leftJoin($table, $first, "=", $foreignKey);
            $fields = Helper::getFields($table);
            foreach($fields as $field) {
                $result->addSelect($table.".".$field." as ".$table."_".$field);
            }
            return $result;
        });

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


    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db2.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db2', function ($app) {
            $dbm = new DatabaseManager($app, $app['db2.factory']);
            $dbm->extend('mysql', function($config, $name) use ($app) {
                //Create default connection from factory
                $connection = $app['db2.factory']->make($config, $name);
                //Instantiate our connection with the default connection data
                $new_connection = new Connection(
                    $connection->getPdo(),
                    $connection->getDatabaseName(),
                    $connection->getTablePrefix(),
                    $config
                );
                //Set the appropriate grammar object
                $new_connection->setQueryGrammar(new Grammar());
                $new_connection->setSchemaGrammar(new MySqlGrammar());
                return $new_connection;
            });

            $dbm->extend('pgsql', function($config, $name) use ($app) {
                //Create default connection from factory
                $connection = $app['db2.factory']->make($config, $name);
                //Instantiate our connection with the default connection data
                $new_connection = new Connection(
                    $connection->getPdo(),
                    $connection->getDatabaseName(),
                    $connection->getTablePrefix(),
                    $config
                );
                //Set the appropriate grammar object
                $new_connection->setQueryGrammar(new Grammar());
                $new_connection->setSchemaGrammar(new PostgresGrammar());
                return $new_connection;
            });

            $dbm->extend('sqlsrv', function($config, $name) use ($app) {
                //Create default connection from factory
                $connection = $app['db2.factory']->make($config, $name);
                //Instantiate our connection with the default connection data
                $new_connection = new Connection(
                    $connection->getPdo(),
                    $connection->getDatabaseName(),
                    $connection->getTablePrefix(),
                    $config
                );
                //Set the appropriate grammar object
                $new_connection->setQueryGrammar(new Grammar());
                $new_connection->setSchemaGrammar(new SqlServerGrammar());
                return $new_connection;
            });
            return $dbm;
        });

        $this->app->bind('db2.connection', function ($app) {
            return $app['db2']->connection();
        });

        $this->app->singleton('db2.transactions', function ($app) {
            return new DatabaseTransactionsManager;
        });
    }

}
