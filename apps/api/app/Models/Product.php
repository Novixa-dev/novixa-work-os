<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name_ar
 * @property string $name_en
 * @property string $slug
 * @property string|null $tagline_ar
 * @property string|null $tagline_en
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property string $lifecycle_status
 * @property int|null $owner_id
 * @property string|null $repository_url
 * @property string|null $website_url
 * @property string|null $demo_url
 * @property string|null $documentation_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Opportunity> $opportunities
 * @property-read int|null $opportunities_count
 * @property-read User|null $owner
 * @property-read Collection<int, Project> $projects
 * @property-read int|null $projects_count
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDemoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDocumentationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLifecycleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRepositoryUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTaglineAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTaglineEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWebsiteUrl($value)
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'tagline_ar',
        'tagline_en',
        'description_ar',
        'description_en',
        'lifecycle_status',
        'owner_id',
        'repository_url',
        'website_url',
        'demo_url',
        'documentation_url',
    ];

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @return HasMany<Opportunity, $this> */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
