<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_id',
        'type',          // single_choice / multi_choice / free_text / number / date
        'title',         // 質問文
        'help_text',     // 補足説明
        'is_required',   // 必須フラグ
        'max_select',    // 選択肢最大数
        'display_order', // 並び順
        'is_active',     // 有効/無効
    ];

    protected $casts = [
        'is_required'   => 'boolean',
        'is_active'     => 'boolean',
        'max_select'    => 'integer',
        'display_order' => 'integer',
    ];

    // リレーション

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function screenQuestions()
    {
        return $this->hasMany(ScreenQuestion::class);
    }
}
