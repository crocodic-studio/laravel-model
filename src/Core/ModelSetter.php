<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/18/2020
 * Time: 12:17 AM
 */

namespace Crocodic\LaravelModel\Core;

trait ModelSetter
{

    /**
     * ModelSetter constructor.
     * @param null $row
     */
    public function __construct($row = null)
    {
        if($row) {
            foreach($row as $key=>$value) {
                $this->{$key} = $value;
            }
        }
    }

    public function set($column, $value) {
        $this->{$column} = $value;
    }

    /**
     * @return string|string[]
     * @throws \ReflectionException
     */
    private function getTableFromClass(): string
    {
        return str_replace("_model","", $this->convertPascalCaseToKebabCase( (new \ReflectionClass($this))->getShortName() ));
    }

    private function getDefaultPrimaryKey()
    {
        return "id";
    }

    private function convertPascalCaseToKebabCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    /**
     * @param $result
     * @return static[]
     */
    private static function listSetter($result) {
        $final = [];
        foreach($result as $item) {
            $model = new static();
            foreach($item as $key=>$val) {
                $model->set($key,$val);
            }
            $final[] = $model;
        }
        return $final;
    }

    /**
     * @param $result
     * @return static
     */
    private static function objectSetter($result) {
        $model = new static();
        if($result) {
            foreach($result as $key=>$val) {
                $model->set($key,$val);
            }
        }
        return $model;
    }

    public function __toString()
    {
        return $this->id;
    }

}