<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            //回答ID
            $table->ulid('id')->primary();
            //対象フォーム
            $table->foreignId('form_id')->constrained('forms')->cascadeOnUpdate()->restrictOnDelete();
            //匿名回答者キー
            $table->string('respondent_key', 100)->nullable(); // 匿名キー（Cookie/トークン）
            //回答時IP
            $table->string('client_ip', 45)->nullable();
            //ブラウザUA
            $table->text('user_agent')->nullable();
            //進行中、提出済み
            $table->enum('status', ['in_progress','submitted','invalid'])
                   ->default('submitted')
                   ->comment('回答ステータス in_progress:途中保存 / submitted:提出済み / invalid:不正');
            //提出時刻
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
