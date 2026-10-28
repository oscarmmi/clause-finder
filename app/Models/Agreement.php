<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    protected $fillable = [
        'title',
        'body',
    ];

    /** @return HasMany<ClauseHit, $this> */
    public function clauseHits(): HasMany
    {
        return $this->hasMany(ClauseHit::class);
    }
}
