<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            //フォームID
            $table->id();
            //管理ユーザ
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            //フォームタイトル
            $table->string('title', 200);
            //説明文
            $table->text('description')->nullable();
            //有効/無効
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
