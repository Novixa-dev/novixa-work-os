<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int|null $contact_id
 * @property int $user_id
 * @property string $channel
 * @property string $summary
 * @property Carbon $interaction_date
 * @property Carbon|null $next_follow_up_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 * @property-read Contact|null $contact
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereInteractionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereNextFollowUpDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereUserId($value)
 *
 * @mixin \Eloquent
 */
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

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
