<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('screens', function (Blueprint $table) {
            //画面ID
            $table->id();
            //所属フォーム
            $table->foreignId('form_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
            //作成者
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            //画面タイトル
            $table->string('title', 200);
            //フォーム内での表示順序
            $table->unsignedInteger('display_order')->default(0);
            //有効/無効
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['form_id', 'display_order']);
            $table->index(['form_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
