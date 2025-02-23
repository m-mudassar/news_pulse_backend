<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'url'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sources')->withTimestamps();
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }
}
