<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    public $incrementing = false; // ULID等
    protected $keyType = 'string';

    protected $fillable = [
        'id',         // ULID 生成をアプリ側でするなら fillable
        'form_id',
        'status',     // submitted / invalid / in_progress など
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function items()
    {
        return $this->hasMany(ResponseItem::class);
    }
}
