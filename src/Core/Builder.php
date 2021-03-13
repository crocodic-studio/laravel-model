<?php


namespace Crocodic\LaravelModel\Core;

/**
 * Class Builder
 * @package Crocodic\LaravelModel\Core
 * @method Builder addSelectTable(string $table, string $prefix = null, array $exceptColumns = [])
 * @method Builder withTable(string $table, string $selectPrefix = null, array $exceptColumns = [], string $first = null, string $operator = null, string $second = null)
 * @method Builder like(string $column, string $keyword)
 */
abstract class Builder extends \Illuminate\Database\Query\Builder
{

}