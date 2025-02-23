<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model {
    use HasFactory;

    protected $fillable = ['name'];

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'user_authors')->withTimestamps();
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }


}
