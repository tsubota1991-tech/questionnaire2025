<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            //質問ID
            $table->id();
            //所属フォーム
            $table->foreignId('form_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
            //設問の種別
            $table->enum('type', ['single_choice', 'multi_choice', 'free_text', 'number', 'date'])->default('single_choice');
            //質問文
            $table->string('title', 300);
            //補足説明
            $table->text('help_text')->nullable();
            //必須フラグ
            $table->boolean('is_required')->default(false);
            //選択肢最大数（6つ想定）
            $table->unsignedTinyInteger('max_select')->default(6);
            //並び順
            $table->unsignedInteger('display_order')->default(0);
            //有効/無効
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['form_id', 'display_order']);
            $table->index(['form_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
