<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'type',
        'status',
        'priority',
        'lead_id',
        'product_id',
        'client_id',
        'start_date',
        'due_date',
        'progress_percentage',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'progress_percentage' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'joined_at');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function recalculateProgress(): int
    {
        $total = $this->tasks()->whereNull('parent_id')->count();
        if ($total === 0) {
            $this->update(['progress_percentage' => 0]);
            return 0;
        }

        $done = $this->tasks()->whereNull('parent_id')->where('status', 'done')->count();
        $percentage = (int) round(($done / $total) * 100);

        $this->update(['progress_percentage' => $percentage]);
        return $percentage;
    }
}
