<?php

namespace Crocodic\LaravelModel\Helpers;

use Illuminate\Database\Query\Builder;

class BuilderMacro
{
    public static function registerMacro()
    {
        Builder::macro("addSelectTable", function($table) {
            $fields = Helper::getFields($table);
            foreach($fields as $field) {
                $this->addSelect($table.".".$field." as ".$table."_".$field);
            }
            return $this;
        });
    }
}