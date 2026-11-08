<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientInteraction extends Model
{
    protected $fillable = [
        'client_id',
        'contact_id',
        'user_id',
        'channel',
        'summary',
        'interaction_date',
        'next_follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'interaction_date' => 'datetime',
            'next_follow_up_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
