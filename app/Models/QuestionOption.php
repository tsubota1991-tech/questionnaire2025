<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    use SoftDeletes;

    // デフォルトで 'question_options' を参照するので $table 指定は不要
    protected $fillable = [
        'question_id',
        'label',
        'value',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
    ];
    // リレーション
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
