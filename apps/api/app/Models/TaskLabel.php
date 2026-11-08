<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name_ar
 * @property string|null $name_en
 * @property string $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaskLabel extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'color',
    ];

    /** @return BelongsToMany<Task, $this> */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_label_pivot', 'label_id', 'task_id');
    }
}
