<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2/14/2020
 * Time: 1:28 PM
 */

namespace Crocodic\LaravelModel\Core;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class Model extends ModelAbstract
{
    use ModelSetter;

    public function __construct($row = null)
    {
        if(!is_null($row)) {
            foreach($row as $key=>$value) {
                $this->{$key} = $value;
            }
        }
    }

    public static function getTable()
    {
        $static = new static;
        return $static->setTable() ? $static->setTable() : $static->getTableFromClass();
    }

    public static function getPrimaryKey()
    {
        $static = new static;
        return $static->setPrimaryKey() ? $static->setPrimaryKey() : $static->getDefaultPrimaryKey();
    }

    public static function getConnection()
    {
        $static = new static;
        return $static->setConnection() ? $static->setConnection() : config("database.default", "mysql");
    }

    /**
     * Get last record id
     * @return mixed
     */
    public static function lastId() {
        return app('db')->table(static::getTable())->max(static::getPrimaryKey());
    }

    /**
     * A one-to-many relationship
     * @param string $modelName Parent model class name
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @param callable|null $condition Add condition with Builder Query
     * @return mixed
     */
    public function hasMany(string $modelName, string $foreignKey = null, string $localKey = null, callable $condition = null) {
        $childModel = new $modelName();
        $parentModel = new static();
        $foreignKey = ($foreignKey) ? $foreignKey : $parentModel::getTable()."_".$parentModel::getPrimaryKey();
        $localKey = ($localKey) ? $localKey : $parentModel::getPrimaryKey();
        $localKey = $this->$localKey;
        $childQuery =  $childModel::where($foreignKey, "=", $localKey);
        if(isset($condition) && is_callable($condition)) $childQuery = call_user_func($condition, $childQuery);
        return $childQuery->get();
    }

    /**
     * A one-to-one relationship
     * @param string $modelName
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return static
     */
    public function belongsTo(string $modelName, string $foreignKey = null, string $localKey = null) {
        $childModel = new $modelName();
        $parentModel = new static();
        $foreignKey = ($foreignKey) ? $foreignKey : $parentModel::getTable()."_".$parentModel::getPrimaryKey();
        $localKey = ($localKey) ? $localKey : $parentModel::getPrimaryKey();
        $localKey = $this->$localKey;
        return new static($childModel::where($foreignKey, "=", $localKey)->first());
    }

    /**
     * @return \Crocodic\LaravelModel\Core\Builder
     */
    public static function table()
    {
        return app('db')->table(static::getTable());
    }

    /**
     * @param int $limit
     * @param callable|null $query
     * @return LengthAwarePaginator
     */
    public static function paginate(int $limit, callable $query = null): LengthAwarePaginator
    {
        $data = static::table();
        if(!is_null($query) && is_callable($query)) {
            $data = call_user_func($query, $data);
        }
        return $data->paginate($limit);
    }

    /**
     * @param $foreignTable
     * @param $foreignTablePrimary
     * @param $foreignColumn
     * @return Builder
     */
    public static function join($foreignTable, $foreignTablePrimary, $foreignColumn)
    {
        return static::table()->join($foreignTable, $foreignTablePrimary, $foreignColumn);
    }

    /**
     * @param $foreignTable
     * @param $foreignTablePrimary
     * @param $foreignColumn
     * @return Builder
     */
    public static function leftJoin($foreignTable, $foreignTablePrimary, $foreignColumn)
    {
        return static::table()->leftJoin($foreignTable, $foreignTablePrimary, $foreignColumn);
    }

    /**
     * @param $foreignTable
     * @param $foreignTablePrimary
     * @param $foreignColumn
     * @return Builder
     */
    public static function rightJoin($foreignTable, $foreignTablePrimary, $foreignColumn)
    {
        return static::table()->rightJoin($foreignTable, $foreignTablePrimary, $foreignColumn);
    }

    /**
     * @param $column1
     * @param $operator
     * @param $column2
     * @return Builder
     */
    public static function where($column1, $operator, $column2)
    {
        return static::table()->where($column1, $operator, $column2);
    }


    /**
     * @param string $column
     * @param array $arrayData
     * @return Builder
     */
    public static function whereIn(string $column, array $arrayData)
    {
        return static::table()->whereIn($column, $arrayData);
    }

    /**
     * Find all data by speicific column and value, also you can specify sorting option
     * @param array|string $column
     * @param string|null $value
     * @param string $sorting_column
     * @param string $sorting_dir
     * @return Collection
     */
    public static function findAllBy($column, $value = null, $sorting_column = "id", $sorting_dir = "desc") {
        if(is_array($column)) {
            $result = app('db')->table(static::getTable());
            foreach($column as $key=>$value) {
                $result->where($key, $value);
            }
            $result = $result->orderBy($sorting_column, $sorting_dir)->get();
        } else {

            $result = app('db')->table(static::getTable())->where($column, $value)->orderBy($sorting_column, $sorting_dir)->get();
        }

        return $result;
    }

    /**
     * Count the all data
     * @return integer
     */
    public static function count() {
        $total = app("LaravelModelTemporary")->get(static::class, "count", static::getTable());
        if(!isset($total)) {
            $total = app('db')->table(static::getTable())->count();
            app("LaravelModelTemporary")->put(static::class, "count", static::getTable());
        }
        return $total;
    }

    /**
     * Count the data by specific column and value
     * @param array|string $column
     * @param string|null $value
     * @return integer
     */
    public static function countBy($column, $value = null) {
        if(is_array($column)) {
            $result = app('db')->table(static::getTable());
            foreach($column as $key=>$value) {
                $result->where($key, $value);
            }
            $result = $result->count();
        } else {

            $result = app('db')->table(static::getTable())
                ->where($column, $value)
                ->count();
        }
        return $result;
    }

    /**
     * Find all data with descending sorting
     * @param $column
     * @return Collection
     */
    public static function findAllDesc($column = "id") {
        return app('db')->table(static::getTable())->orderBy($column,"desc")->get();
    }

    /**
     * Find all the data with ascending sorting
     * @param string $column
     * @return Collection
     */
    public static function findAllAsc($column = "id") {
        return app('db')->table(static::getTable())->orderBy($column,"asc")->get();
    }

    /**
     * Find all the data without sorting by default
     * @param callable|null $query Query Builder
     * @return Collection
     */
    public static function findAll(callable $query = null) {
        if(is_callable($query)) {
            $result = call_user_func($query, static::table());
            $result = $result->get();
        } else {
            $result = static::table()->get();
        }
        return $result;
    }

    /**
     * Get all latest data
     * @return Collection
     */
    public static function latest() {
        return app('db')->table(static::getTable())->orderBy(static::getPrimaryKey(),"desc")->get();
    }

    /**
     * Get all oldest data
     * @return Collection
     */
    public static function oldest() {
        return app('db')->table(static::getTable())->orderBy(static::getPrimaryKey(),"asc")->get();
    }

    /**
     * Convert model output to array output
     * @return array
     */
    public function toArray() {
        $result = [];
        foreach($this as $key=>$val) {
            $result[$key] = $val;
        }
        return $result;
    }

    /**
     * Find a data by primary key condition with Model output
     * @param $id
     * @return static
     */
    public static function findById($id) {
        $row = app("LaravelModelTemporary")->get(static::class, "findById", $id);
        if(!$row) {
            $row = app('db')->table(static::getTable())
                ->where(static::getPrimaryKey(),$id)
                ->first();
            app("LaravelModelTemporary")->put(static::class, "findById", $id, $row);
        }

        return static::objectSetter($row);
    }

    /**
     * Find a data by primary key condition with Model output
     * @param $id
     * @return static
     */
    public static function find($id) {
        return static::findById($id);
    }

    /**
     * Find a data by specific column and value with Model output
     * @param array|string $column
     * @param string|null $value
     * @return static
     */
    public static function findBy($column, $value = null) {
        if(is_array($column)) {
            $row = app('db')->table(static::getTable())
                ->where($column)
                ->first();
        } else {
            $row = app('db')->table(static::getTable())
                ->where($column,$value)
                ->first();
        }

        return static::objectSetter($row);
    }

    /**
     * Save many data
     * @param Model[] $data
     */
    public static function bulkInsert(array $data) {
        $insertData = [];
        foreach($data as $row) {
            /** @var Model $row */
            $dataArray = $row->toArray();
            unset($dataArray[static::getPrimaryKey()]);
            if(isset($dataArray['created_at']) && empty($dataArray['created_at'])) {
                $dataArray['created_at'] = date('Y-m-d H:i:s');
            }
            $insertData[] = $dataArray;
        }
        app('db')->table(static::getTable())->insertOrIgnore($insertData);
    }

    public function save() {
        $data = [];
        foreach($this as $key=>$val) {
            if(!in_array($key,[static::getPrimaryKey()])) {
                if(isset($this->{$key})) {
                    $data[$key] = $val;
                }
            }
        }

        unset($data['table'], $data['connection'], $data['primary_key']);

        if($this->{static::getPrimaryKey()}) {
            if(isset($data['created_at'])) {
                unset($data['created_at']);
            }
            app('db')->table(static::getTable())->where(static::getPrimaryKey(), $this->{static::getPrimaryKey()})->update($data);
            $id = $this->{static::getPrimaryKey()};
        } else {
            if(property_exists($this,'created_at')) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            $id = app('db')->table(static::getTable())->insertGetId($data);
        }

        $this->{static::getPrimaryKey()} = $id;
        return ($id)?true:false;
    }

    /**
     * Delete the data by specific primary key condition
     * @param $id
     */
    public static function deleteById($id) {
        app('db')->table(static::getTable())->where(static::getPrimaryKey(),$id)->delete();
    }

    /**
     * Delete the data by specific column and value
     * @param string|array $column
     * @param null $value
     */
    public static function deleteBy($column, $value = null) {
        if(is_array($column)) {
            $result = app('db')->table(static::getTable());
            foreach($column as $key=>$value) {
                $result->where($key, $value);
            }
            $result->delete();
        } else {
            if(!$value) {
                throw new \InvalidArgumentException("Missing argument 2 value");
            }

            app('db')->table(static::getTable())->where($column,$value)->delete();
        }
    }

    /**
     * Delete all data from table
     */
    public static function deleteAll()
    {
        app('db')->table(static::getTable())->delete();
    }

    /**
     * Delete the data selected
     */
    public function delete() {
        app('db')->table(static::getTable())->where(static::getPrimaryKey(), $this->{static::getPrimaryKey()})->delete();
    }


}