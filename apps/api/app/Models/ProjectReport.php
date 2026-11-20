<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A progress report, suggestion, review, or risk note tied to a project.
 *
 * @property int $id
 * @property int $project_id
 * @property int|null $user_id
 * @property string $title
 * @property string $type
 * @property string $content
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProjectReport extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'type',
        'content',
        'status',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
