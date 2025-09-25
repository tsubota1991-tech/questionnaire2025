<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_user_id',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    // リレーション
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function screens()
    {
        return $this->hasMany(Screen::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
