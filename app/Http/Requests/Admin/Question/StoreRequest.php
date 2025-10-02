<?php

namespace App\Http\Requests\Admin\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $n    = max(0, min((int)$this->input('max_select', 0), 50));

        $rules = [
            'type'          => ['required', Rule::in(['single_choice','multi_choice','free_text','number','date'])],
            'title'         => ['required','string','max:300'],
            'help_text'     => ['nullable','string'],
            'is_required'   => ['nullable','boolean'],
            'max_select'    => ['nullable','integer','min:0','max:50'],
            'display_order' => ['nullable','integer','min:0'],
            'is_active'     => ['nullable','boolean'],
            'options'       => ['array'],
            'options.*.label' => ['nullable','string','max:200'],
            'options.*.value' => ['nullable','string','max:200'],
        ];

        if (in_array($type, ['single_choice','multi_choice'], true) && $n > 0) {
            $rules['options'][] = 'min:'.$n;
            for ($i = 0; $i < $n; $i++) {
                $rules["options.$i.label"] = ['required','string','max:200'];
            }
        }
        return $rules;
    }

    protected function prepareForValidation(): void
    {
        // 日本語 UI の場合に備えて type を英語コードへ正規化（任意）
        $map = [
            '単一選択'=>'single_choice','複数選択'=>'multi_choice',
            '自由入力'=>'free_text','数値入力'=>'number','日付入力'=>'date',
        ];
        $type = $this->input('type');
        $type = $map[$type] ?? $type;

        // max_select の安全な数値化
        $max = (int) $this->input('max_select', 0);
        $max = max(0, min($max, 50));

        $this->merge([
            'type'        => $type,
            'max_select'  => $max,
            'is_required' => (bool) $this->boolean('is_required'),
            'is_active'   => (bool) $this->boolean('is_active', true),
        ]);
    }

    public function messages(): array
    {
        return [
            'options.min'             => '選択肢は :min 件以上入力してください。',
            'options.*.label.required'=> '選択肢ラベルは必須です。',
            'options.*.label.max'     => '選択肢ラベルは200文字以内で入力してください。',
        ];
    }
}
