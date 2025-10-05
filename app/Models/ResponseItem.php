<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseItem extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'option_id',
        'free_text',
        'numeric_value',
        'date_value',
    ];

    protected $casts = [
        'date_value' => 'date',
        // 'numeric_value' => 'decimal:6', // 表示時に丸めたいなら有効化
    ];

    // ===== リレーション =====
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }
}
