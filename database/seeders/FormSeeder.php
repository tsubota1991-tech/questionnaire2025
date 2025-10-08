<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;
use App\Models\User;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // すでに DatabaseSeeder で作成済みのサンプルユーザを取得
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->command->warn('サンプルユーザが存在しません。先に DatabaseSeeder を実行してください。');
            return;
        }

        // サンプルのフォームを複数作成
        Form::create([
            'owner_user_id' => $user->id,
            'title'         => '顧客満足度アンケート(サンプル)',
            'description'   => 'サービス利用後の満足度を調査するためのフォームです。',
            'is_active'     => true,
        ]);

        Form::create([
            'owner_user_id' => $user->id,
            'title'         => 'イベント参加申込フォーム(サンプル)',
            'description'   => '社内イベントの参加申込を受け付けるフォームです。',
            'is_active'     => true,
        ]);

        Form::create([
            'owner_user_id' => $user->id,
            'title'         => '退会理由アンケート(サンプル)',
            'description'   => '退会されるユーザ様に理由をお伺いするためのフォームです。',
            'is_active'     => false, // 無効サンプル
        ]);
    }
}
