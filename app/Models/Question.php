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
    public const TYPE_MAP = [
        'single_choice' => '単一選択',
        'multi_choice'  => '複数選択',
        'free_text'     => '自由入力',
        'number'        => '数値入力',
        'date'          => '日付入力',
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
        return $this->hasMany(QuestionOption::class)->orderBy('display_order');
    }

    public function screenQuestions()
    {
        return $this->hasMany(ScreenQuestion::class);
    }
    /**
     * 補助：指定セレクト番号に対応した「選択肢テキスト」を
     *       option の配列にマージして返す（表示用の便利関数）
     *
     * 返り値の各要素：
     *  [
     *    'option'       => QuestionOption モデル,
     *    'display_text' => string（存在しなければ $option->label を返す）
     *  ]
     */
    public function optionsWithDisplayTextFor(int $selectIndex)
    {
        // options リレーションは既に存在している前提
        $this->loadMissing(['options.texts' => function ($q) use ($selectIndex) {
            $q->where('select_index', $selectIndex);
        }]);

        return $this->options->map(function ($opt) use ($selectIndex) {
            $override = $opt->texts->first(); // select_index で絞ってあるので1件想定
            return [
                'option'       => $opt,
                'display_text' => $override?->display_text ?? $opt->label,
            ];
        });
    }
    //$question->type_label で日本語が取れる}
    public function getTypeLabelAttribute(): string
    {
        // 余計な空白・大小文字ゆらぎを吸収
        $code = is_string($this->type) ? strtolower(trim($this->type)) : '';
        return self::TYPE_MAP[$code] ?? $code;
    }
}
