<?php

namespace Crocodic\LaravelModel\Helpers;

use Illuminate\Database\Query\Builder;

class BuilderMacro
{
    public static function registerMacro()
    {
        Builder::macro("addSelectTable", function($table, $prefix = null, $exceptColumns = []) {
            $fields = Helper::getFields($table);
            foreach($fields as $field) {
                if(count($exceptColumns) && in_array($field,$exceptColumns)) continue;
                $prefix = ($prefix) ? $prefix : $table;
                $this->addSelect($table.".".$field." as ".$prefix."_".$field);
            }
            return $this;
        });

        Builder::macro("withTable", function($table, $selectionPrefix = null, $exceptColumns = [], $first = null, $operator = "=", $second = null) {
            /** @var \Crocodic\LaravelModel\Core\Builder $this */
            if(is_array($table)) {
                foreach($table as $tbl) {
                    $first = ($first) ? $first : $tbl.".id";
                    $second = ($second) ? $second : $this->from."_id";
                    $this->leftJoin($tbl,$first,$operator,$second);
                    $this->addSelectTable($tbl, $selectionPrefix, $exceptColumns);
                }
            } else {
                $first = ($first) ? $first : $table.".id";
                $second = ($second) ? $second : $table."_id";
                $this->leftJoin($table, $first, $operator, $second);
                $this->addSelectTable($table, $selectionPrefix, $exceptColumns);
            }
            return $this;
        });

        Builder::macro("like", function($column, $keyword) {
            /** @var \Crocodic\LaravelModel\Core\Builder $this */
            if(substr($keyword,0,1) != "%" && substr($keyword,-1,1) != "%") {
                $keyword = "%".$keyword."%";
            }
            $this->where($column, "like", "%".$keyword."%");
        });
    }
}