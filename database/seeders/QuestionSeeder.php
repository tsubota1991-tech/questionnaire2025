<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $forms = Form::all();
        if ($forms->isEmpty()) {
            $this->command->warn('フォームが存在しません。先に FormSeeder を実行してください。');
            return;
        }

        foreach ($forms as $form) {
            // Q1: 自由入力
            Question::create([
                'form_id'       => $form->id,
                'type'          => 'free_text',                // ← enumに存在する値
                'title'         => $form->title . ' に関する自由記述',
                'help_text'     => 'ご自由にお書きください。',
                'is_required'   => false,
                'max_select'    => 6,                          // 使われないが必須カラムなのでデフォルトで
                'display_order' => 1,
                'is_active'     => true,
            ]);

            // Q2: 単一選択（5段階評価）
            Question::create([
                'form_id'       => $form->id,
                'type'          => 'single_choice',            // ← enumに存在する値
                'title'         => $form->title . ' の満足度（5段階）',
                'help_text'     => '直感的にお選びください。',
                'is_required'   => true,
                'max_select'    => 1,                          // 単一選択なので1
                'display_order' => 2,
                'is_active'     => true,
            ]);
        }
    }
}
