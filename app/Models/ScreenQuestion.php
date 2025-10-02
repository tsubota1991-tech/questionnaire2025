<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreenQuestion extends Model
{
    protected $table = 'screen_questions';

    protected $fillable = [
        'screen_id',
        'question_id',
        'display_order',
    ];

    // リレーション
    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class);
    }
}
