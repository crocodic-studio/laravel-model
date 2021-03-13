<?php


namespace Crocodic\LaravelModel\Core;

/**
 * Class Builder
 * @package Crocodic\LaravelModel\Core
 * @method Builder addSelectTable(string $table)
 * @method Builder withTable($table)
 * @method Builder like($column, $keyword)
 */
abstract class Builder extends \Illuminate\Database\Query\Builder
{

}