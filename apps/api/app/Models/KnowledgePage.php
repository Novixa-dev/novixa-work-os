<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title_ar
 * @property string|null $title_en
 * @property string $slug
 * @property string $category
 * @property string $content_ar
 * @property string|null $content_en
 * @property int $author_id
 * @property int|null $project_id
 * @property int|null $product_id
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $author
 * @property-read Product|null $product
 * @property-read Project|null $project
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereContentAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereContentEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KnowledgePage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class KnowledgePage extends Model
{
    protected $fillable = [
        'title_ar',
        'title_en',
        'slug',
        'category',
        'content_ar',
        'content_en',
        'author_id',
        'project_id',
        'product_id',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
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
}
