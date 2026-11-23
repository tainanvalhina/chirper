<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    protected $fillable = ['user_id'];
    
    //cada curtida pertence a 1 usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //cada curtida pertence a um chirp.
    public function chirp(): BelongsTo
    {
        return $this->belongsTo(Chirp::class);
    }
}