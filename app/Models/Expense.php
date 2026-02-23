<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'colocation_id', 'category_id', 'payer_id',
        'title', 'amount', 'spent_at', 'note',
    ];

    public function colocation(): BelongsTo{
        return $this->belongsTo(Colocation::class);
    }

    public function category(): BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function payer(): BelongsTo{
        return $this->belongsTo(User::class, 'payer_id');
    }
}
