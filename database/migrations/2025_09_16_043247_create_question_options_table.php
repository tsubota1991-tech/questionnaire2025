<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            //選択肢ID
            $table->foreignId('question_id')->constrained('questions')->cascadeOnUpdate()->cascadeOnDelete();
            //所属質問
            $table->string('label', 200);
            //表示ラベル
            $table->string('value', 200)->nullable();
            //保存値
            $table->unsignedInteger('display_order')->default(0);
            //有効/無効
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['question_id', 'display_order']);
            $table->index(['question_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
