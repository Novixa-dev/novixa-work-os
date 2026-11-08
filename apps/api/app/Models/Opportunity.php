<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int|null $product_id
 * @property string $title_ar
 * @property string|null $title_en
 * @property string $stage
 * @property numeric|null $estimated_value
 * @property string $currency
 * @property int|null $owner_id
 * @property Carbon|null $expected_close_date
 * @property Carbon|null $next_follow_up_date
 * @property string|null $source
 * @property string|null $notes
 * @property int|null $won_delivery_project_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 * @property-read User|null $owner
 * @property-read Product|null $product
 * @property-read Project|null $wonDeliveryProject
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereEstimatedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereExpectedCloseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereNextFollowUpDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Opportunity whereWonDeliveryProjectId($value)
 *
 * @mixin \Eloquent
 */
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

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<Project, $this> */
    public function wonDeliveryProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'won_delivery_project_id');
    }
}
