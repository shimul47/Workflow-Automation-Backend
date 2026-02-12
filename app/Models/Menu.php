<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'icon',
        'route',
        'parent_id',
        'order',
        'is_active'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class);
    }
}

