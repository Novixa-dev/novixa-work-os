<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An info card, note, plan, or credential vault entry tied to a project.
 *
 * @property int $id
 * @property int $project_id
 * @property int|null $user_id
 * @property string $title
 * @property string $type
 * @property string|null $content
 * @property string|null $url
 * @property string|null $username
 * @property string|null $password
 * @property string|null $environment
 * @property string|null $color
 * @property bool $is_pinned
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProjectNote extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'type',
        'content',
        'url',
        'username',
        'password',
        'environment',
        'color',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

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
