<?php

namespace Crocodic\LaravelModel\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Helper
{
    public static function getFields(string $table)
    {
        $modelName = "\App\Models\\".Str::studly($table)."Model";
        $modelClass = new $modelName();
        return array_keys(get_object_vars($modelClass));
    }

    public static function findPrimaryKey($table, $connection = null)
    {
        $connection = $connection?:config("database.default");

        $pk = DB::connection($connection)->getDoctrineSchemaManager()->listTableDetails($table)->getPrimaryKey();
        if(!$pk) {
            return null;
        }
        return $pk->getColumns()[0];
    }


    public static function appPath($path)
    {
        if(!function_exists("app_path")) {
            return app()->path() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
        } else {
            return app_path($path);
        }
    }

}