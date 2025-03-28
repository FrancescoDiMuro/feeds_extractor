<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokSource extends Model
{
    use HasFactory;

    // Set the fillable model's attributes
    protected $fillable = [
        'id',
        'name',
        'fan_count',
        'feed_id'
    ];

    // Removing the timestamps columns from the model
    public $timestamps = false;

    // Inverse One to One relationship with Feed
    public function feed(): BelongsTo {
        return $this->belongsTo(Feed::class);
    }
}
