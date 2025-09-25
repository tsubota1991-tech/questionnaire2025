<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Screen extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_id',
        'created_by_user_id',
        'title',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];
    
    // リレーション
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function screenQuestions()
    {
        return $this->hasMany(ScreenQuestion::class);
    }
}
