<?php

namespace App\Models;

use App\Enums\MenuCategoryGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort_order', 'group'];

    protected $casts = [
        'group' => MenuCategoryGroup::class,
    ];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }
}
