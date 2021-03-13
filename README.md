# Crocodic Laravel Model
An alternative laravel eloquent. Crocodic Laravel Model using Model, Repository and Service pattern. Model is a class that define anything about table columns. 
Repository is a class that define your own query method. And Service is a class that should define your own business logic query method.

### Requirement
Laravel / Lumen 5.* | 6.* | 7.* | 8.*

### Install Command
``composer require crocodic/laravel-model``

### LUMEN USER: 
after install with the composer then add this bellow to `/bootstrap/app.php` at section `Register Service Providers`

``$app->register(\Crocodic\LaravelModel\LaravelModelServiceProvider::class);``

### 1. Create a model

*Create a model from a table*<br/>
``php artisan create:model foo_bar_table``

*Create model for all tables*<br/>
``php artisan create:model``

*Create a model with other connection*<br/>
``php artisan create:model foo_bar_table --connection=con2``

I assume that you have a ```books``` table with the structure like bellow:
```
id (Integer) Primary Key
created_at (Timestamp)
name (Varchar) 255
```

It will create a new model class file at ```/app/Models/BooksModel.php``` with the following file structure : 

```php
<?php
namespace App\Models;

use DB;
use Crocodic\LaravelModel\Core\Model;

class BooksModel extends Model
{
    public $id;
    public $created_at;
    public $name;
}
```
Also create a new Repository class file, and a new Service class file. 
`/app/Repositories/Books.php`
`/app/Services/BooksService.php`

You can set custom connection, table and primary key name

```php
<?php
namespace App\Models;

use DB;
use Crocodic\LaravelModel\Core\Model;

class BooksModel extends Model
{
    public $id;
    public $created_at;
    public $name;
    
    public function setConnection(){
         return "mysql";
    }

    public function setTable() {
        return "books";
    }   

    public function setPrimaryKey(){
        return "id";
    }
}
```

### 2. Using Crocodic Laravel Model class on your Controller
Crocodic Laravel Model use Model, Repository and Service pattern. If you want make a query, please use the Repository one instead of Model Class. Like example bellow: 
Insert ```use App\Repositories\Books; ``` at top of your controller class name.

```php
<?php 
namespace App\Http\Controllers;

use App\Repositories\Books;

class FooController extends Controller {
    
    public function index() 
    {
        $books = Books::latest();
        return view("books", ["bookData"=>$books]);
    }
    
    public function detail($id)
    {
        $book = Books::find($id);
        return view("book_detail", ["book"=>$book]);
    }
    
    public function delete($id)
    {
        Books::deleteById($id);
        
        return redirect()->back()->with(["message"=>"Book ".$book->name." has been deleted!"]);
    }
}
?>
```

### 3. Using Crocodic Laravel Model class that has a relation
I assume you have a table ```categories``` for book relation like bellow : 
```
id (Integer) Primary Key
name (Varchar) 255
```
and your book structure to be like bellow:
```
id (Integer) Primary Key
created_at (Timestamp)
categories_id (Integer)
name (Varchar) 255
```
Now you have to create a model for ```categories``` table, you can following previous steps.

I assume that you have create a ```categories``` model, so make sure that now we have two files in the ```/app/Models/```
``` 
/BooksModel.php
/CategoriesModel.php
```
Open the Books model , and add this bellow method
```php
    /**
    * @return App\Models\Categories
    */
    public function category() {
        return $this->belongsTo("App\Models\Categories");
    }

    // or 
    /**
    * @return App\Models\Categories
    */
    public function category() {
        return Categories::find($this->categories_id);
    }
```
Then open the FooController 
```php
<?php 
namespace App\Http\Controllers;

use App\Repositories\Books;

class FooController extends Controller {
    
    
    public function detail($id)
    {
        $book = Books::find($id);
        
        $data = [];
        $data['book_id'] = $book->id;
        $data['book_name'] = $book->name;
        $data['book_category_id'] = $book->category()->id;
        $data['book_category_name'] = $book->category()->name;
        
        return view("book_detail",$data);
    }
    
}
?>
```
As you can see now we can get the category name by using ```->category()->name``` without any SQL Query or even Database Builder syntax. Also you can recursively go down to your relation with NO LIMIT.

### 4. How to Casting DB Builder Collection output to Crocodic Laravel Model Class?
You can easily cast your simple database builder collection to cb model class.

```php 
$row = DB::table("books")->where("id",1)->first();

//Cast to Crocodic Laravel Model
$model = new Books($row);

//And then you can use cb model normally
echo $model->name;
```

### 5. How to insert the data with Crocodic Laravel Model
You can easily insert the data with method ```->save()``` like bellow:
```php 
$book = new Books();
$book->created_at = date("Y-m-d H:i:s"); //this created_at is a magic method you can ignore this
$book->name = "New Book";
$book->categories_id = 1;
$book->save();
```
Then if you want to get the last insert id you can do like bellow:
```php
...
$book->save();
$lastInsertId = $book->id; // get the id from id property
...
```

### 5. How to update the data with Crocodic Laravel Model
You can easily update the data, just find it for first : 
```php 
$book = Books::findById(1);
$book->name = "New Book";
$book->categories_id = 1;
$book->save();
```
### 5. How to delete the data?
You can easily delete the data, just find it for first : 
```php 
$book = Books::findById(1);
$book->delete();
```
or
```php 
Books::deleteById(1);
```

## Model Method Available
```php
/**
* Find all data by specific condition.
*/ 
$result = FooBar::findAllBy($column, $value = null, $sorting_column = "id", $sorting_dir = "desc");
// or 
$result = FooBar::findAllBy(['foo'=>1,'bar'=>2]);

/**
* Find all data without sorting
*/
$result = FooBar::findAll();

/**
* Count the records of table
*/ 
$result = FooBar::count();

/**
* Count the records with specific condition 
*/
$result = FooBar::countBy($column, $value = null);
// or
$result = FooBar::countBy(['foo'=>1,'bar'=>2]);

/**
* Find all datas and ordering the data to descending
*/
$result = FooBar::findAllDesc($column = "id");
// or simply
$result = FooBar::latest();

/**
* Find all datas and ordering the data to ascending
*/
$result = FooBar::findAllAsc($column = "id");
// or simply
$result = FooBar::oldest();

/** 
* Find/Fetch a record by a primary key value
*/
$result = FooBar::findById($id);
// or simply
$result = FooBar::find($id);

/**
* Create a custom query, and result laravel Query Builder collection
*/
$result = FooBar::where("foo","=",1)->first();
// or
$result = FooBar::table()->where("foo","=",1)->first();

/**
* Join a table with a simple step
*/
$result = FooBar::table()->withTable("related_table")->first();

/**
* Auto select all from a table, and make them prefix with its table name
*/
$result = FooBar::table()
->join("related_table","related_table.id","=","related_table_id")
->addSelect("foo_bar.*")
->addSelectTable("related_table") // This will produce: related_table_id, related_table_created_at, etc
->first();

/**
* Add like condition to the query
*/
$result = FooBar::table()->like("column",$yourKeyword)->get();
// It will produce same as FooBar::table()->where("columne","like","%".$yourKeyword."%")->get()

/**
* Find a record by a specific condition
*/
$result = Foobar::findBy($column, $value = null);
// or 
$result = Foobar::findBy(['foo'=>1,'bar'=>2]);

/**
* To run the insert SQL Query
*/
$fooBar = new FooBar();
$fooBar->name = "Lorem ipsum";
$fooBar->save();

/**
* To bulk insert
*/
$data = [];
$foo = new FooBar();
$foo->name = "Lorem ipsum 1";
array_push($data, $foo);
$bar = new FooBar();
$bar->name = "Lorem ipsum 2";
array_push($data, $bar);
FooBar::bulkInsert($data);


/**
* To run the update SQL Query
*/
$fooBar = FooBar::findById($value);
$fooBar->name = "Lorem ipsum";
$fooBar->save();

/**
* To delete the record by a primary key value
*/
FooBar::deleteById($value);

/**
* To delete the record by a specific condition
*/
FooBar::deleteBy($column, $value = null);
// or
Foobar::deleteBy(['foo'=>1,'bar'=>2]);

/**
* To delete after you fetch the record 
*/
$fooBar = FooBar::findById($value);
$fooBar->delete();
```

## A One-To-Many Relationship
```php
class Posts extends Model {
    // etc
    
    /**
    * @return Illuminate\Support\Collection
    */
    public function comments() {
        return $this->hasMany(Comments::class);
    }
    
    // or with full option
    /**
    * @return Illuminate\Support\Collection
    */
    public function comments() {
        return $this->hasMany(Comments::class, "foreign_key", "local_key", function($condition) {
            return $condition->where("status","Active");
        });
    }
}
```

## A One-To-One Relationship
```php
class Comments extends Model {
    // etc
    
    /**
    * @return App\Models\Posts
    */
    public function post() {
        return $this->belongsTo(Posts::class);
    }
    
    // or with full option
    /**
    * @return App\Models\Posts
    */
    public function post() {
        return $this->belongsTo(Posts::class, "foreign_key", "local_key");
    }
}
```

## Other Useful
1. [CRUDBooster Laravel CRUD Generator](https://github.com/crocodic-studio/crudbooster)
