<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstagramSource extends Model
{
    use HasFactory;

    // Removing the timestamps columns from the model
    public $timestamps = false;

    // Inverse One to One relationship with Feed
    public function feed(): BelongsTo {
        return $this->belongsTo(Feed::class);
    }
}
