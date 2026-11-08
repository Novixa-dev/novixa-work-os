<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    protected $fillable = [
        'client_id',
        'product_id',
        'title_ar',
        'title_en',
        'stage',
        'estimated_value',
        'currency',
        'owner_id',
        'expected_close_date',
        'next_follow_up_date',
        'source',
        'notes',
        'won_delivery_project_id',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'next_follow_up_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function wonDeliveryProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'won_delivery_project_id');
    }
}
