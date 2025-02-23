<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title',
        'content',
        'description',
        'author_id',
        'source_id',
        'category_id',
        'url',
        'image_url',
        'published_at',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
