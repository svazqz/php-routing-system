<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class Sample extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'samples';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email', 
        'description'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        // Add attributes to hide from JSON output
        // Example: 'password', 'remember_token'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // Add attribute casting here
        // Example: 'email_verified_at' => 'datetime'
    ];

    /**
     * Example relationship method
     * Uncomment and modify as needed
     */
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    /**
     * Example scope method
     * Uncomment and modify as needed
     */
    // public function scopeActive($query)
    // {
    //     return $query->where('active', 1);
    // }

    /**
     * Create the samples table in memory (for demonstration)
     */
    public static function createTable()
    {
        $schema = Capsule::schema();
        
        if (!$schema->hasTable('samples')) {
            $schema->create('samples', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }
}
