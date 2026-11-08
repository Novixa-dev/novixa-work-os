<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'type',
        'industry',
        'status',
        'website',
        'phone',
        'email',
        'city',
        'country',
        'account_manager_id',
        'notes',
    ];

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ClientInteraction::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
