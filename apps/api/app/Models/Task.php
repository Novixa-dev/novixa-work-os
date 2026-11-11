<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $title_ar
 * @property string|null $title_en
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property int|null $project_id
 * @property int|null $product_id
 * @property int|null $client_id
 * @property int|null $milestone_id
 * @property int|null $parent_id
 * @property string $status
 * @property string $priority
 * @property int|null $assignee_id
 * @property int|null $reporter_id
 * @property bool $is_my_day
 * @property Carbon|null $start_date
 * @property Carbon|null $due_date
 * @property numeric|null $estimated_hours
 * @property numeric|null $actual_hours
 * @property int $kanban_order
 * @property Carbon|null $archived_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $claimed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $assignee
 * @property-read Collection<int, TaskAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read Client|null $client
 * @property-read Collection<int, TaskComment> $comments
 * @property-read int|null $comments_count
 * @property-read Collection<int, Task> $dependencies
 * @property-read int|null $dependencies_count
 * @property-read Collection<int, Task> $dependents
 * @property-read int|null $dependents_count
 * @property-read Collection<int, TaskLabel> $labels
 * @property-read int|null $labels_count
 * @property-read ProjectMilestone|null $milestone
 * @property-read Task|null $parent
 * @property-read Product|null $product
 * @property-read Project|null $project
 * @property-read User|null $reporter
 * @property-read Collection<int, Task> $subtasks
 * @property-read int|null $subtasks_count
 * @property-read Collection<int, User> $watchers
 * @property-read int|null $watchers_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereActualHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereAssigneeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereEstimatedHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereIsMyDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereKanbanOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Task extends Model
{
    protected $fillable = [
        'code',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'project_id',
        'product_id',
        'client_id',
        'milestone_id',
        'parent_id',
        'status',
        'priority',
        'assignee_id',
        'reporter_id',
        'is_my_day',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'kanban_order',
        'archived_at',
        'completed_at',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_my_day' => 'boolean',
            'start_date' => 'date',
            'due_date' => 'date',
            'claimed_at' => 'datetime',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'kanban_order' => 'integer',
            'archived_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<ProjectMilestone, $this> */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    /** @return BelongsTo<Task, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /** @return HasMany<Task, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** @return BelongsToMany<TaskLabel, $this> */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label_pivot', 'task_id', 'label_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_watchers')
            ->withPivot('created_at');
    }

    /** @return HasMany<TaskComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    /** @return HasMany<TaskAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    /** @return BelongsToMany<Task, $this> */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('type', 'created_at');
    }

    /** @return BelongsToMany<Task, $this> */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
            ->withPivot('type', 'created_at');
    }

    public function isBlocked(): bool
    {
        return $this->dependencies()->where('status', '!=', 'done')->exists();
    }
}
