<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('screen_questions', function (Blueprint $table) {
            //ID
            $table->id();
            //画面
            $table->foreignId('screen_id')->constrained('screens')->cascadeOnUpdate()->cascadeOnDelete();
            //質問
            $table->foreignId('question_id')->constrained('questions')->cascadeOnUpdate()->cascadeOnDelete();
            //画面内の表示順
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['screen_id', 'question_id']);
            $table->unique(['screen_id', 'display_order']);
            $table->index(['question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_questions');
    }
};
