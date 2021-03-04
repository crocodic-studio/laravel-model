<?php


namespace Crocodic\LaravelModel\Core;


use Crocodic\LaravelModel\Helpers\Helper;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class CrocodicBuilder extends Builder
{

    private static function getFields(string $table)
    {
        $modelName = Str::studly($table)."Model";
        $modelClass = new ("\App\Models\\".$modelName)();
        return get_object_vars($modelClass);
    }

    public function addSelectTable(string $table)
    {
        $fields = $this->getFields($table);
        foreach($fields as $field) {
            $this->addSelect($table.".".$field." as ".$table."_".$field);
        }
        return $this;
    }
}