<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids; // ← 正しい名前空間
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    use HasUlids;                 // id未指定時のみ自動採番。手動採番しても問題なし
    public $incrementing = false; // 主キーは数値増分ではない
    protected $keyType = 'string';// 主キー型は文字列

    // ステータス定数（DBのENUMと一致させる）
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED   = 'submitted';
    public const STATUS_INVALID     = 'invalid';

    protected $fillable = [
        'id',
        'form_id',
        'respondent_key',
        'client_ip',
        'user_agent',
        'status',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /* ===== Relations ===== */

    /** この回答が属するフォーム */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /** 回答の明細（選択肢/自由入力など） */
    public function items(): HasMany
    {
        return $this->hasMany(ResponseItem::class);
    }
}
