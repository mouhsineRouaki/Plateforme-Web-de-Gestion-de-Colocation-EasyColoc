<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = ['colocation_id', 'name', 'color'];

    public function colocation():BelongsTo{
        return $this->belongsTo(Colocation::class);
    }
    public function expense():BelongsTo{
        return $this->belongsTo(Expense::class);
    }
}
