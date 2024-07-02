<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feed extends Model
{
    use HasFactory;

    // Removing the timestamps columns from the model
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name'
    ];

    // One to One relationship with InstagramSource
    public function instagramSource(): HasOne {
        return $this->hasOne(InstagramSource::class);
    }

    // One to One relationship with TikTokSource
    public function tiktokSource(): HasOne {
        return $this->hasOne(TikTokSource::class);
    }

    // One to Many relationship with Post
    public function posts(): HasMany {
        return $this->hasMany(Post::class);
    }
}
