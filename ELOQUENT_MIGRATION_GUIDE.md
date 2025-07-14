# Eloquent ORM Migration Guide

This guide explains the migration from ActiveRecord to Eloquent ORM in PHP-RoutingSystem.

## What Changed

### Before (ActiveRecord)
```php
<?php
class Sample extends ActiveRecord\Model
{
    // ActiveRecord configuration
    static $table_name = 'samples';
    static $primary_key = 'id';
}
```

### After (Eloquent)
```php
<?php
use Core\Classes\Model;

class Sample extends Model
{
    protected $table = 'samples';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'description'];
}
```

## Migration Steps

### 1. Update Dependencies
Eloquent ORM has been automatically installed via Composer:
- `illuminate/database`
- `illuminate/events`

### 2. Update Model Classes

**Old ActiveRecord Model:**
```php
<?php
namespace Models;
use ActiveRecord;

class User extends ActiveRecord\Model {
    static $table_name = 'users';
    static $primary_key = 'id';
    static $validates_presence_of = array(
        array('name'),
        array('email')
    );
}
```

**New Eloquent Model:**
```php
<?php
use Core\Classes\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'email', 'password'
    ];
    
    protected $hidden = [
        'password', 'remember_token'
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];
    
    // Validation can be handled via Laravel's validation or custom methods
}
```

### 3. Update Database Queries

#### Creating Records
**ActiveRecord:**
```php
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();

// Or
$user = User::create(array('name' => 'John', 'email' => 'john@example.com'));
```

**Eloquent:**
```php
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();

// Or
$user = User::create(['name' => 'John', 'email' => 'john@example.com']);

// Framework helper
$user = User::createRecord(['name' => 'John', 'email' => 'john@example.com']);
```

#### Finding Records
**ActiveRecord:**
```php
$user = User::find(1);
$user = User::find_by_email('john@example.com');
$users = User::all();
$users = User::where(array('active' => 1));
```

**Eloquent:**
```php
$user = User::find(1);
$user = User::where('email', 'john@example.com')->first();
$users = User::all();
$users = User::where('active', 1)->get();

// Framework helpers
$user = User::findById(1);
$users = User::getAll();
```

#### Updating Records
**ActiveRecord:**
```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Or
$user->update_attributes(array('name' => 'Jane Doe'));
```

**Eloquent:**
```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Or
$user->update(['name' => 'Jane Doe']);

// Framework helper
User::updateRecord(1, ['name' => 'Jane Doe']);
```

#### Deleting Records
**ActiveRecord:**
```php
$user = User::find(1);
$user->delete();
```

**Eloquent:**
```php
$user = User::find(1);
$user->delete();

// Framework helper
User::deleteRecord(1);
```

### 4. Relationships

**ActiveRecord:**
```php
class User extends ActiveRecord\Model {
    static $has_many = array(
        array('posts')
    );
}

class Post extends ActiveRecord\Model {
    static $belongs_to = array(
        array('user')
    );
}
```

**Eloquent:**
```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
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

### 5. Configuration Updates

Update your `config.ini` file to include Eloquent database settings:

```ini
[database]
; Legacy DB driver settings (for backward compatibility)
type = "mysql"
path = "/path/to/database.sqlite"

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
```

## Database Migrations

### Creating Tables
Use the provided migration system:

1. Create migration files in `database/migrations/`
2. Run migrations using: `php migrate.php`

Example migration:
```php
<?php
use Illuminate\Database\Schema\Blueprint;
use Core\Providers\EloquentServiceProvider;

EloquentServiceProvider::initialize();
$schema = EloquentServiceProvider::getConnection()->getSchemaBuilder();

$schema->create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```

## Backward Compatibility

The existing `DB` driver has been updated to work alongside Eloquent:

```php
// Legacy DB methods still work
$result = \Drivers\DB::execQueryObject("SELECT * FROM users");

// New Eloquent integration methods
$connection = \Drivers\DB::getEloquentConnection();
$users = \Drivers\DB::table('users')->where('active', 1)->get();
```

## Benefits of Eloquent

1. **Better Performance**: More efficient query building and caching
2. **Rich Feature Set**: Advanced relationships, scopes, mutators, accessors
3. **Active Development**: Part of Laravel ecosystem with regular updates
4. **Better Documentation**: Extensive documentation and community support
5. **Modern PHP**: Uses modern PHP features and follows PSR standards
6. **Query Builder**: Fluent, expressive query building interface
7. **Schema Builder**: Database-agnostic schema building and migrations

## Testing

All existing tests continue to pass. New Eloquent-specific tests have been added:
- `tests/Unit/Core/Providers/EloquentServiceProviderTest.php`
- `tests/Unit/App/Models/SampleTest.php`

Run tests with: `./run-tests.sh`

## Getting Help

- [Eloquent ORM Documentation](https://laravel.com/docs/eloquent)
- [Query Builder Documentation](https://laravel.com/docs/queries)
- [Schema Builder Documentation](https://laravel.com/docs/migrations#creating-tables)

## Migration Checklist

- [ ] Update model classes to extend `Core\Classes\Model`
- [ ] Replace ActiveRecord syntax with Eloquent syntax
- [ ] Update configuration file with database settings
- [ ] Create database migrations if needed
- [ ] Update relationship definitions
- [ ] Test all model operations
- [ ] Update any custom queries to use Eloquent syntax
- [ ] Run the test suite to ensure everything works