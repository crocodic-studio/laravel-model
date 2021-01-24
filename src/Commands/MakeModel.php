<?php namespace Crocodic\LaravelModel\Commands;

use App;
use Crocodic\LaravelModel\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModel extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'create:model {table=ALL} {--connection=mysql : The connection database, default is mysql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crocodic Laravel Model: Create a new model class';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $table = $this->argument("table");
        $repoName = null;
        $connection = $this->option('connection');

        if($table == "ALL") {
            $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
            foreach($tables as $table) {
                $this->generateByTable($table, $connection, $repoName);
            }
        }else{
            $this->generateByTable($table, $connection, $repoName);
        }
    }

    private function generateByTable($table, $connection, $repoName) {
        $path = Helper::appPath('Models');

        if(!file_exists($path)) {
            @mkdir($path,0755);
        }

        $pathRepositories = Helper::appPath("Repositories");
        if(!file_exists($pathRepositories)) {
            @mkdir($pathRepositories, 0755);
        }

        $pathServices = Helper::appPath("Services");
        if(!file_exists($pathServices)) {
            @mkdir($pathServices, 0755);
        }

        $primary_key = Helper::findPrimaryKey($table,$connection);

        $template = file_get_contents(__DIR__.'/../Stubs/template.blade.php.stub');
        $repoTemplate = file_get_contents(__DIR__.'/../Stubs/repo_template.blade.php.stub');
        $serviceTemplate = file_get_contents(__DIR__.'/../Stubs/service_template.blade.php.stub');
        $tableStudly = Str::studly($table);

        //Assign Class name
        $template = str_replace('{class_name}',$tableStudly, $template);
        $repoTemplate = str_replace('{class_name}', $tableStudly, $repoTemplate);
        $serviceTemplate = str_replace('{class_name}', $tableStudly, $serviceTemplate);

        //Assign Table Name
        $template = str_replace('{table}',$table, $template);
        $repoTemplate = str_replace('{table}', $table, $repoTemplate);
        $serviceTemplate = str_replace('{table}', $table, $serviceTemplate);

        // Assign Primary Key
        $template = str_replace('{primary_key}',$primary_key, $template);

        // Assign Connection
        $template = str_replace('{connection}', $connection, $template);

        // Assign Columns Properties
        $columns = DB::connection($connection)->getSchemaBuilder()->getColumnListing($table);
        $properties = "\n";
        foreach($columns as $column)
        {
            $properties .= "\tpublic \$".$column.";\n";
        }
        $template = str_replace('{properties}', $properties, $template);

        //Put the file
        if(!$repoName) {
            $repoName = $tableStudly;
        }

        file_put_contents($path.'/'.$repoName.'Model.php', $template);
        if(file_exists($path.'/'.$repoName.'Model.php')) {
            $this->info($repoName." model has been updated!");
        }else{
            $this->info($repoName." model has been created!");
        }

        //create repository
        if(!file_exists($pathRepositories.'/'.$repoName.'.php')) {
            file_put_contents($pathRepositories.'/'.$repoName.'.php', $repoTemplate);
            $this->info($repoName." repository has been created!");
        }


        //create service
        if(!file_exists($pathServices.'/'.$repoName.'Service.php')) {
            file_put_contents($pathServices.'/'.$repoName.'Service.php', $serviceTemplate);
            $this->info($repoName.' service has been created!');
        }
    }
}
