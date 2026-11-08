<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property string $title_ar
 * @property string|null $title_en
 * @property Carbon|null $due_date
 * @property string $status
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project $project
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMilestone whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id',
        'title_ar',
        'title_en',
        'due_date',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }
}
