<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;
use App\Models\Question;
use App\Models\ScreenQuestion;

class ScreenQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $screens = Screen::all();
        if ($screens->isEmpty()) {
            $this->command->warn('画面が存在しません。先に ScreenSeeder を実行してください。');
            return;
        }

        foreach ($screens as $screen) {
            $questions = Question::where('form_id', $screen->form_id)
                                 ->orderBy('display_order')
                                 ->get();

            $order = 1;
            foreach ($questions as $q) {
                ScreenQuestion::firstOrCreate(
                    ['screen_id' => $screen->id, 'question_id' => $q->id],
                    ['display_order' => $order++]
                );
            }
        }
    }
}
