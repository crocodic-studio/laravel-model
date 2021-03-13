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

        Builder::macro("withTable", function($table) {
            /** @var \Crocodic\LaravelModel\Core\Builder $this */
            if(is_array($table)) {
                foreach($table as $tbl) {
                    $this->leftJoin($tbl,$tbl.".id","=",$this->from."_id");
                    $this->addSelectTable($tbl);
                }
            } else {
                $this->leftJoin($table, $table.".id", "=", $table."_id");
                $this->addSelectTable($table);
            }
            return $this;
        });

        Builder::macro("like", function($column, $keyword) {
            /** @var \Crocodic\LaravelModel\Core\Builder $this */
            if(substr($keyword,0,1) != "%" && substr($keyword,-1,1) != "%") {
                $keyword = "%".$keyword."%";
            }
            $this->whereRaw($column." like '".$keyword."'");
        });
    }
}