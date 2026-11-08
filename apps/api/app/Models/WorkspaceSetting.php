<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $key
 * @property array<array-key, mixed> $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkspaceSetting whereValue($value)
 *
 * @mixin \Eloquent
 */
class WorkspaceSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::find($key);

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
