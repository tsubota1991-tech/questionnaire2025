<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Form;
use App\Models\Screen;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ScreenQuestion;
use Illuminate\Support\Facades\DB;

class SampleSurveySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) {
            $this->command->warn('test@example.com のユーザーが見つかりません。先に DatabaseSeeder を実行してください。');
            return;
        }

        DB::transaction(function () use ($user) {

            // 既に同タイトルがあれば削除（何度でも流せるように）
            Form::where('title', 'サンプルアンケート')->delete();

            // フォーム
            $form = Form::create([
                'owner_user_id' => $user->id,
                'title'         => 'サンプルアンケート',
                'description'   => 'デモ用のアンケートフォームです。',
                'is_active'     => true,
                'public_path'   => Str::random(16), // ランダム公開URL
            ]);

            // 画面1：個人情報
            $screen1 = Screen::create([
                'form_id'            => $form->id,
                'created_by_user_id' => $user->id,
                'title'              => '個人情報',
                'display_order'      => 1,
                'is_active'          => true,
            ]);

            // 画面2：アンケート
            $screen2 = Screen::create([
                'form_id'            => $form->id,
                'created_by_user_id' => $user->id,
                'title'              => 'アンケート',
                'display_order'      => 2,
                'is_active'          => true,
            ]);

            // ---- 画面1の設問 ----
            // 設問1（文字列）：名前
            $q1 = Question::create([
                'form_id'       => $form->id,
                'type'          => 'free_text',
                'title'         => '名前',
                'help_text'     => null,
                'is_required'   => true,
                'max_select'    => 6,
                'display_order' => 1,
                'is_active'     => true,
            ]);

            // 設問2（文字列）：都道府県
            $q2 = Question::create([
                'form_id'       => $form->id,
                'type'          => 'free_text',
                'title'         => '都道府県',
                'help_text'     => null,
                'is_required'   => false,
                'max_select'    => 6,
                'display_order' => 2,
                'is_active'     => true,
            ]);

            // 設問3（選択）：性別
            $q3 = Question::create([
                'form_id'       => $form->id,
                'type'          => 'single_choice',
                'title'         => '性別',
                'help_text'     => null,
                'is_required'   => false,
                'max_select'    => 1,
                'display_order' => 3,
                'is_active'     => true,
            ]);

            // 性別の選択肢
            QuestionOption::insert([
                [
                    'question_id'   => $q3->id,
                    'label'         => '男性',
                    'value'         => 'male',
                    'display_order' => 1,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q3->id,
                    'label'         => '女性',
                    'value'         => 'female',
                    'display_order' => 2,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q3->id,
                    'label'         => 'その他',
                    'value'         => 'other',
                    'display_order' => 3,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
            ]);

            // 画面1への設問配置
            ScreenQuestion::insert([
                [
                    'screen_id'      => $screen1->id,
                    'question_id'    => $q1->id,
                    'display_order'  => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ],
                [
                    'screen_id'      => $screen1->id,
                    'question_id'    => $q2->id,
                    'display_order'  => 2,
                    'created_at'     => now(), 'updated_at' => now(),
                ],
                [
                    'screen_id'      => $screen1->id,
                    'question_id'    => $q3->id,
                    'display_order'  => 3,
                    'created_at'     => now(), 'updated_at' => now(),
                ],
            ]);

            // ---- 画面2の設問 ----
            // 設問1（複数選択）：購入商品を選択してください。
            $q4 = Question::create([
                'form_id'       => $form->id,
                'type'          => 'multi_choice',
                'title'         => '購入商品を選択してください。',
                'help_text'     => null,
                'is_required'   => false,
                'max_select'    => 3, // 複数可
                'display_order' => 1,
                'is_active'     => true,
            ]);

            QuestionOption::insert([
                [
                    'question_id'   => $q4->id,
                    'label'         => '商品A',
                    'value'         => 'A',
                    'display_order' => 1,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q4->id,
                    'label'         => '商品B',
                    'value'         => 'B',
                    'display_order' => 2,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q4->id,
                    'label'         => '商品C',
                    'value'         => 'C',
                    'display_order' => 3,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
            ]);

            // 設問2（選択）：製品の満足度を入力してください
            $q5 = Question::create([
                'form_id'       => $form->id,
                'type'          => 'single_choice',
                'title'         => '製品の満足度を入力してください',
                'help_text'     => null,
                'is_required'   => true,
                'max_select'    => 1,
                'display_order' => 2,
                'is_active'     => true,
            ]);

            QuestionOption::insert([
                [
                    'question_id'   => $q5->id,
                    'label'         => 'すごくいい',
                    'value'         => '5',
                    'display_order' => 1,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q5->id,
                    'label'         => 'いい',
                    'value'         => '4',
                    'display_order' => 2,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q5->id,
                    'label'         => '普通',
                    'value'         => '3',
                    'display_order' => 3,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q5->id,
                    'label'         => '悪い',
                    'value'         => '2',
                    'display_order' => 4,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
                [
                    'question_id'   => $q5->id,
                    'label'         => 'すごく悪い',
                    'value'         => '1',
                    'display_order' => 5,
                    'is_active'     => true,
                    'created_at'    => now(), 'updated_at' => now(),
                ],
            ]);

            // 設問3（文字列）：商品に対するご意見
            $q6 = Question::create([
                'form_id'       => $form->id,
                'type'          => 'free_text',
                'title'         => '商品に対するご意見',
                'help_text'     => 'ご自由にお書きください',
                'is_required'   => false,
                'max_select'    => 6,
                'display_order' => 3,
                'is_active'     => true,
            ]);

            // 画面2への設問配置
            ScreenQuestion::insert([
                [
                    'screen_id'      => $screen2->id,
                    'question_id'    => $q4->id,
                    'display_order'  => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ],
                [
                    'screen_id'      => $screen2->id,
                    'question_id'    => $q5->id,
                    'display_order'  => 2,
                    'created_at'     => now(), 'updated_at' => now(),
                ],
                [
                    'screen_id'      => $screen2->id,
                    'question_id'    => $q6->id,
                    'display_order'  => 3,
                    'created_at'     => now(), 'updated_at' => now(),
                ],
            ]);

            $this->command->info('サンプルアンケートの初期データを投入しました。公開URL: /f/'.$form->public_path);
        });
    }
}
