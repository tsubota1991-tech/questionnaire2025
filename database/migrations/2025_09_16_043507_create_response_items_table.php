<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('response_items', function (Blueprint $table) {
            // 回答明細ID
            $table->id();

            // 回答単位 ※ULID を参照（responses.id が ULID 前提）
            $table->ulid('response_id');
            $table->foreign('response_id')
                  ->references('id')->on('responses')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // 回答対象の質問
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            // 選択肢ID（選択式のみ）
            $table->foreignId('option_id')
                  ->nullable()
                  ->constrained('question_options')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            // 自由入力回答（255文字まで）
            $table->string('free_text', 255)->nullable();

            // 数値回答
            $table->decimal('numeric_value', 18, 6)->nullable();

            // 日付回答
            $table->date('date_value')->nullable();

            $table->timestamps();

            // 同一回答・同一質問での重複登録防止（選択肢 or 自由入力）
            $table->unique(
                ['response_id', 'question_id', 'option_id', 'free_text'],
                'uniq_resp_q_opt_text'
            );

            $table->index(['question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_items');
    }
};
