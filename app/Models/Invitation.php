<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'colocation_id',
        'invited_email',
        'token',
        'status',
        'expires_at',
        'sent_by',
        'accepted_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
    public function colocation(): BelongsTo{
        return $this->belongsTo(Colocation::class);
    }

    public function sender(): BelongsTo{
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function accepter(): BelongsTo{
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
