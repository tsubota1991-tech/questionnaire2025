<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Form;
use App\Models\User;
use App\Models\Question;
use App\Models\ScreenQuestion;

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

    // ===== リレーション =====

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

    /**
     * 画面に配置された質問（中間: screen_questions）
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'screen_questions', 'screen_id', 'question_id')
            ->withPivot('display_order')
            ->withTimestamps(); // ※ 中間テーブルに created_at/updated_at が無いなら外してください
    }

    /**
     * 中間テーブルの display_order で並び替え
     */
    public function questionsOrdered()
    {
        return $this->questions()->orderBy('screen_questions.display_order');
    }

    /**
     * 有効な質問のみ + 並び順
     */
    public function questionsActiveOrdered()
    {
        return $this->questionsOrdered()->where('questions.is_active', true);
    }
}
