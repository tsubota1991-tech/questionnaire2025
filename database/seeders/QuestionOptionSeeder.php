<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;

class QuestionOptionSeeder extends Seeder
{
    public function run(): void
    {
        // 選択肢が必要なタイプのみ対象
        $questions = Question::whereIn('type', ['single_choice', 'multi_choice'])->get();

        foreach ($questions as $question) {
            // 5段階評価の例
            $labels = [
                ['label' => 'とても満足',      'value' => '5'],
                ['label' => '満足',            'value' => '4'],
                ['label' => 'どちらとも言えない','value' => '3'],
                ['label' => '不満',            'value' => '2'],
                ['label' => 'とても不満',      'value' => '1'],
            ];

            $order = 1;
            foreach ($labels as $item) {
                QuestionOption::create([
                    'question_id'   => $question->id,
                    'label'         => $item['label'],
                    'value'         => $item['value'],
                    'display_order' => $order++,
                    'is_active'     => true,
                ]);
            }
        }
    }
}
