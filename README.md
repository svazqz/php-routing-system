# PHP-RoutingSystem | PHP Framework for MVC pattern implementation

## Index

* <a href="#installation">Installation</a>
* <a href="#configuration">Configuration</a>
    * <a href="#configuration-file">Configuration file</a>
    * <a href="#friendly-urls">Friendly URLs</a>
        * <a href="#apache">Apache</a>
* <a href="#url-mapping">URL Mapping</a>
* <a href="#controllers">Controllers</a>
* <a href="#views">Views</a>
* <a href="#models">Models</a>
* <a href="#donate">Donate</a>

## Installation

**PHP-RoutingSystem** requires PHP 7+ and its only dependencies (at this moment) are defined in the *composer.json* file.
You just need to define the *public* folder as the root of your site if you are using apache (intructions for nginx will be added) and thats all you need to start working with this framework... well and run:

```shell
composer install
```

To download all the dependencies.

### Using docker compose
If you are using docker compose you can simply run:
```shell
docker compose up -d
```

And your app will be running on:
```
URL_ADDRESS:8000
```

## Configuration

### Configuration file

**PHP-RoutingSystem** uses a mechanisms named *Drivers* (located in core/drivers) to add functionalities to your app. So the configuration must be managed by the *Driver* named **Config.php**. Te configuration driver will load your configuration from "config.ini", file that must be placed in the root of the project.

You can copy `config.sample.ini` to `config.ini` and customize it for your environment. The configuration file should contain:

```
[defaults]
controller = "Home"
method = "main"

[database]
; Eloquent ORM settings
driver = "mysql"
host = "localhost"
port = 3306
database = "your_database_name"
username = "your_username"
password = "your_password"
charset = "utf8mb4"
collation = "utf8mb4_unicode_ci"
prefix = ""

[app]
debug = false
timezone = "UTC"
environment = "production"
```

If you want to place more configurations you have to define them inside your own section, and they can be accessed:

```
Drivers\Config::get()->var("section.var", "default");
```

## URL Mapping

In **PHP-RoutingSystem** every url will be mapped to a controller following the next patterns:

### Common controllers
- http://yourdomain.com/ (default controller, main method)
- http://yourdomain.com/controller (controller, main method)
- http://yourdomain.com/controller/method (controller, method)
- http://yourdomain.com/controller/method/arg1/arg2/... (controller, method with args)

### API controllers

The API controllers follow a RESTFUL pattern, so an API controller will have method that represents a HTTP verb directly. The verbs and methods are:

* GET => show
* POST => create
* PUT => update
* DELETE => delete

The way that an url is mapped for API controllers is made using the word "api" on the url. i.e.

* http://yourdomain.com/api/controller1 (Access to API Controller1 on the corresponding method that is related with the HTTP verb used for the request)
* http://yourdomain.com/api/controller1/arg1/arg2 (Access to API Controller1 on the corresponding method that is related with the HTTP verb used for the request, passing the args as parammeters for that method)

## Controllers

### Custom routes
In both API controllers as well as common controllers, you can define your own custom routes. To do this you have to add the following code to your controller constructor:
```php
public function __construct() {
    $this->__ROUTES__ = array(
        "/api/blog/post/:id" => "getPostData"
    );
}
```
This will map the url "/api/blog/post/:id" to the method "getPostData" in the controller. And all the defined args will be passed as parameters for that method.

### Common

All the controllers must be located in *app/controllers* folder, following the next convention:

If you want to create a new controller for your home page you have to create a new file named:

```
Home.php
```

And fill it with:

```php
<?php

namespace Controllers;

use Core;
use Models;

class Home extends Core\Controller {

    public function index() {
        echo "Home page!"
    }

}
```

If you have not defined the "Home" controller as your default controller you can access to it in this way:

```
http://youredomain.com/home
```

This url will automatically launch the index method in the controller, and you will see the content that you print inside of it.

### API

The API controllers must be located on the *api* folder inside the *app/controllers* folder. And the structure for a new  API controller is:

```php
<?php

namespace Controllers\API;

use Core;
use Models;

class Example extends Core\APIController {
    public function show() {
        
    }
    public function create() {
        
    }
    public function create() {
        
    }
    public function delete() {
        
    }
}

```

Note: It is not neccessary to define all methods, only those that are going to be used.

## Views

###Templates

**PHP-RoutingSystem** uses Twig as its template engine, but it's already integrated inside the framework's flow and file structure.

To make use of this mechanism it is neccessary to create the corresponding template files for each view inside the *app/templates* folder. i.e.

If you want to create a home template you may create a folder named "home" inside templates and then a file named "index.html", and then use:

```php
\View::renderHTML("home/index", array("var1" => $var1));
```

So the framework will load the correspondig file and pass the array of vars according the Twig documentation.

### View classes

The framework allow you to create custom view classes wich may be useful to present data in very specific ways. All the view classes must be placed inside *app/views* folder and every view is linked to a controller. If you create a view class for your *Home* controller it'll look like:

```php
<?php

namespace Views;

class Home {

    public function homePage($name) {
        echo "This is the home page!";
    }

}
```

Then, you can call this class from your controller in any method with:

```php
$mView = $this->getView();
```

And access the required method from the view with:

```php
$mView->homePage();
```

## Models

All the models must be placed in *app/models* and work with **Eloquent ORM** (Laravel's database ORM).

### Creating Models

All models should extend the framework's base `Model` class which provides Eloquent functionality. An example model for a "users" table would be:

```php
<?php

use Core\Classes\Model;

class User extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];
}
```

### Using Models

You can use all standard Eloquent methods:

```php
// Create a new user
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Find a user by ID
$user = User::find(1);
$user = User::findOrFail(1);

// Query users
$users = User::where('active', 1)->get();
$user = User::where('email', 'john@example.com')->first();

// Update a user
$user = User::find(1);
$user->update(['name' => 'Jane Doe']);

// Delete a user
$user = User::find(1);
$user->delete();
```

### Framework Helper Methods

The base Model class also provides some framework-specific helper methods:

```php
// Get all records with optional pagination
$users = User::getAll();
$users = User::getAll(10, 1); // 10 per page, page 1

// Create, update, delete with helper methods
$user = User::createRecord(['name' => 'John', 'email' => 'john@example.com']);
$user = User::updateRecord(1, ['name' => 'Jane']);
User::deleteRecord(1);
```

### Relationships

Eloquent supports all standard relationships:

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```


## Donate

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FDXA3CAML9EF2)
