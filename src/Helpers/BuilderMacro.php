<?php


namespace Crocodic\LaravelModel\Helpers;


use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class BuilderMacro
{
    private static function getFields(string $table)
    {
        $modelName = "\App\Models\\".Str::studly($table)."Model";
        $modelClass = new $modelName();
        return get_object_vars($modelClass);
    }

    public static function registerMacro()
    {
        Builder::macro("addSelectTable", function($table) {
            $fields = static::getFields($table);
            foreach($fields as $field) {
                $this->addSelect($table.".".$field." as ".$table."_".$field);
            }
            return $this;
        });
    }
}