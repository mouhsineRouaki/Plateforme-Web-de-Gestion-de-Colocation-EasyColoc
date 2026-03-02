<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'is_banned',
        'reputation_score'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function createdColocations(): HasMany
    {
        return $this->hasMany(Colocation::class, 'created_by');
    }

    public function colocations(): BelongsToMany
    {
        return $this->belongsToMany(Colocation::class, 'colocation_user')->withPivot(['role_in_colocation', 'joined_at', 'left_at'])->withTimestamps();
    }
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'sent_by');
    }

    public function paidExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'payer_id');
    }
    public function paymentsFrom(): HasMany
    {
        return $this->hasMany(Payment::class, 'from_user_id');
    }
    public function paymentsTo(): HasMany
    {
        return $this->hasMany(Payment::class, 'to_user_id');
    }
    public function createdPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'created_by');
    }
    public function debtsOwed(): HasMany
    {
        return $this->hasMany(Debt::class, 'from_user_id');
    }

    public function debtsToReceive(): HasMany
    {
        return $this->hasMany(Debt::class, 'to_user_id');
    }
}
