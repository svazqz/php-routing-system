<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Carbon\Carbon;

class Blog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'blogs';

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
        'title',
        'content',
        'author',
        'published_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published_at' => 'datetime'
    ];

    /**
     * Create the blogs table in memory (for demonstration)
     */
    public static function createTable()
    {
        $schema = Capsule::schema();
        
        if (!$schema->hasTable('blogs')) {
            $schema->create('blogs', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->string('author');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Seed the table with sample data
     */
    public static function seedData()
    {
        self::createTable();
        
        // Clear existing data
        self::truncate();
        
        // Insert sample data
        $samplePosts = [
            [
                'title' => 'Getting Started with PHP Routing',
                'content' => 'This is a comprehensive guide to understanding PHP routing systems. We\'ll explore how to create clean URLs and handle different HTTP methods effectively.',
                'author' => 'John Doe',
                'published_at' => Carbon::now()->subDays(5)
            ],
            [
                'title' => 'Advanced Eloquent Techniques',
                'content' => 'Learn advanced Eloquent ORM techniques including relationships, scopes, and query optimization. This post covers best practices for database interactions.',
                'author' => 'Jane Smith',
                'published_at' => Carbon::now()->subDays(3)
            ],
            [
                'title' => 'Building RESTful APIs',
                'content' => 'A complete guide to building RESTful APIs with PHP. We\'ll cover authentication, validation, and proper HTTP status codes.',
                'author' => 'Mike Johnson',
                'published_at' => Carbon::now()->subDay()
            ]
        ];
        
        foreach ($samplePosts as $post) {
            self::create($post);
        }
    }

    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', Carbon::now());
    }

    /**
     * Scope for recent posts
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('published_at', '>=', Carbon::now()->subDays($days));
    }
}