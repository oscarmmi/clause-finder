<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClauseHit extends Model
{
    protected $fillable = [
        'agreement_id',
        'query',
        'snippet',
        'score',
        'page_label',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    /** @return BelongsTo<Agreement, $this> */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
