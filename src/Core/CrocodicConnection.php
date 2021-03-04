<?php
namespace Crocodic\LaravelModel\Core;

use Illuminate\Database\Connection;
class CrocodicConnection extends Connection
{

    /**
     * @return CrocodicBuilder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new CrocodicBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

}