<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;
use App\Models\Form;
use App\Models\User;

class ScreenSeeder extends Seeder
{
    public function run(): void
    {
        // サンプルユーザを取得
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) {
            $this->command->warn('サンプルユーザが存在しません。先に DatabaseSeeder を実行してください。');
            return;
        }

        // サンプルフォームを全件取得
        $forms = Form::all();
        if ($forms->isEmpty()) {
            $this->command->warn('フォームが存在しません。先に FormSeeder を実行してください。');
            return;
        }

        foreach ($forms as $form) {
            // フォームごとに複数画面を投入
            Screen::create([
                'form_id'            => $form->id,
                'created_by_user_id' => $user->id,
                'title'              => $form->title . ' - 基本情報',
                'display_order'      => 1,
                'is_active'          => true,
            ]);

            Screen::create([
                'form_id'            => $form->id,
                'created_by_user_id' => $user->id,
                'title'              => $form->title . ' - 詳細情報',
                'display_order'      => 2,
                'is_active'          => true,
            ]);
        }
    }
}
