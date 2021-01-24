<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2/25/2020
 * Time: 10:11 PM
 */

namespace Crocodic\LaravelModel\Core;


abstract class ModelAbstract
{
    public function setConnection()
    {
        return null;
    }

    public function setTable()
    {
        return null;
    }

    public function setPrimaryKey()
    {
        return null;
    }

}