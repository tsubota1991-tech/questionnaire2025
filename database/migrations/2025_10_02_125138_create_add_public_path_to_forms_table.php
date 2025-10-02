<?php
// database/migrations/2025_10_02_000000_add_public_path_to_forms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1) カラム追加
        Schema::table('forms', function (Blueprint $table) {
            // 公開URLのパス断片（例: /f/{public_path}）
            $table->string('public_path', 32)
                  ->nullable()
                  ->after('description');

            // いつ生成/更新したか（任意）
            $table->timestamp('public_path_generated_at')
                  ->nullable()
                  ->after('public_path');

            // ソフトデリートと組み合わせたユニーク制約
            // → 削除済みなら同じパスを再利用可
            $table->unique(['public_path', 'deleted_at'], 'uq_forms_public_path_deleted');
        });

        // 2) 既存データのバックフィル（NULL のものにだけ付与）
        DB::transaction(function () {
            $exists = fn (string $slug): bool =>
                DB::table('forms')
                    ->where('public_path', $slug)
                    ->whereNull('deleted_at')
                    ->exists();

            $makeUnique = function (int $length = 16) use ($exists): string {
                do {
                    // 英数(Base62)で URL 安全なランダム文字列
                    $slug = Str::random($length);
                } while ($exists($slug));
                return $slug;
            };

            $now = now();
            DB::table('forms')
                ->whereNull('public_path')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($now, $makeUnique) {
                    foreach ($rows as $row) {
                        DB::table('forms')
                            ->where('id', $row->id)
                            ->update([
                                'public_path'               => $makeUnique(16),
                                'public_path_generated_at'  => $now,
                            ]);
                    }
                });
        });
    }

    public function down(): void
    {
        // 先にインデックスを落としてからカラム削除
        Schema::table('forms', function (Blueprint $table) {
            $table->dropUnique('uq_forms_public_path_deleted');
            $table->dropColumn(['public_path', 'public_path_generated_at']);
        });
    }
};
