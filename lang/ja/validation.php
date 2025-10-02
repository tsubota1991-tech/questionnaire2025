<?php
/**
 * Laravel 11 用 日本語バリデーションメッセージ
 */
return [

    /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | ここにはバリデータークラスが使用するデフォルトのエラーメッセージを記述します。
    | 一部のルールには複数のバージョンがあります（サイズルールなど）。
    | 必要に応じてこれらのメッセージを自由に調整してください。
    |
    */
    'unique'               => ':attribute は既に使用されています。',
    'accepted'             => ':attribute を承認してください。',
    'accepted_if'          => ':other が :value のとき、:attribute を承認してください。',
    'active_url'           => ':attribute は有効なURLではありません。',
    'after'                => ':attribute には :date 以降の日付を指定してください。',
    'after_or_equal'       => ':attribute には :date 以降の日付を指定してください。',
    'alpha'                => ':attribute には英字のみ使用できます。',
    'alpha_dash'           => ':attribute には英数字・ハイフン・アンダースコアのみ使用できます。',
    'alpha_num'            => ':attribute には英数字のみ使用できます。',
    'array'                => ':attribute は配列である必要があります。',
    'before'               => ':attribute には :date 以前の日付を指定してください。',
    'before_or_equal'      => ':attribute には :date 以前の日付を指定してください。',
    'between'              => [
        'array'   => ':attribute の項目数は :min から :max 個で指定してください。',
        'file'    => ':attribute のファイルサイズは :min 〜 :max KBで指定してください。',
        'numeric' => ':attribute は :min 〜 :max の間で指定してください。',
        'string'  => ':attribute は :min 〜 :max 文字で指定してください。',
    ],
    'boolean'              => ':attribute には true または false を指定してください。',
    'confirmed'            => ':attribute と確認用入力が一致しません。',
    'current_password'     => 'パスワードが正しくありません。',
    'date'                 => ':attribute は正しい日付ではありません。',
    'date_equals'          => ':attribute には :date と同じ日付を指定してください。',
    'date_format'          => ':attribute の形式が :format と一致しません。',
    'declined'             => ':attribute は拒否する必要があります。',
    'declined_if'          => ':other が :value のとき、:attribute は拒否する必要があります。',
    'different'            => ':attribute と :other には異なる値を指定してください。',
    'digits'               => ':attribute は :digits 桁で指定してください。',
    'digits_between'       => ':attribute は :min 〜 :max 桁で指定してください。',
    'dimensions'           => ':attribute の画像サイズが不正です。',
    'distinct'             => ':attribute に重複した値があります。',
    'doesnt_end_with'      => ':attribute は次のいずれかで終わってはいけません: :values。',
    'doesnt_start_with'    => ':attribute は次のいずれかで始まってはいけません: :values。',
    'email'                => ':attribute には有効なメールアドレスを指定してください。',
    'ends_with'            => ':attribute は次のいずれかで終わる必要があります: :values。',
    'enum'                 => '選択した :attribute は無効です。',
    'exists'               => '選択した :attribute は存在しません。',
    'file'                 => ':attribute にはファイルを指定してください。',
    'filled'               => ':attribute は必須です。',
    'gt'                   => [
        'array'   => ':attribute の項目数は :value 個より多くしてください。',
        'file'    => ':attribute のファイルサイズは :value KBより大きくしてください。',
        'numeric' => ':attribute は :value より大きくしてください。',
        'string'  => ':attribute は :value 文字より多くしてください。',
    ],
    'gte'                  => [
        'array'   => ':attribute の項目数は :value 個以上にしてください。',
        'file'    => ':attribute のファイルサイズは :value KB以上にしてください。',
        'numeric' => ':attribute は :value 以上にしてください。',
        'string'  => ':attribute は :value 文字以上にしてください。',
    ],
    'image'                => ':attribute には画像ファイルを指定してください。',
    'in'                   => '選択された :attribute は無効です。',
    'in_array'             => ':attribute は :other に存在しません。',
    'integer'              => ':attribute には整数を指定してください。',
    'ip'                   => ':attribute には有効な IP アドレスを指定してください。',
    'ipv4'                 => ':attribute には有効な IPv4 アドレスを指定してください。',
    'ipv6'                 => ':attribute には有効な IPv6 アドレスを指定してください。',
    'json'                 => ':attribute には有効な JSON 文字列を指定してください。',
    'lowercase'            => ':attribute は小文字で入力してください。',
    'lt'                   => [
        'array'   => ':attribute の項目数は :value 個より少なくしてください。',
        'file'    => ':attribute のファイルサイズは :value KB未満にしてください。',
        'numeric' => ':attribute は :value 未満にしてください。',
        'string'  => ':attribute は :value 文字未満にしてください。',
    ],
    'lte'                  => [
        'array'   => ':attribute の項目数は :value 個以下にしてください。',
        'file'    => ':attribute のファイルサイズは :value KB以下にしてください。',
        'numeric' => ':attribute は :value 以下にしてください。',
        'string'  => ':attribute は :value 文字以下にしてください。',
    ],
    'mac_address'          => ':attribute には有効な MAC アドレスを指定してください。',
    'max'                  => [
        'array'   => ':attribute の項目数は :max 個以下にしてください。',
        'file'    => ':attribute のファイルサイズは :max KB以下にしてください。',
        'numeric' => ':attribute は :max 以下にしてください。',
        'string'  => ':attribute は :max 文字以下にしてください。',
    ],
    'mimes'                => ':attribute には :values タイプのファイルを指定してください。',
    'mimetypes'            => ':attribute には :values タイプのファイルを指定してください。',
    'min'                  => [
        'array'   => ':attribute の項目数は :min 個以上にしてください。',
        'file'    => ':attribute のファイルサイズは :min KB以上にしてください。',
        'numeric' => ':attribute は :min 以上にしてください。',
        'string'  => ':attribute は :min 文字以上にしてください。',
    ],
    'multiple_of'          => ':attribute は :value の倍数でなければなりません。',
    'not_in'               => '選択された :attribute は無効です。',
    'not_regex'            => ':attribute の形式が不正です。',
    'numeric'              => ':attribute には数値を指定してください。',
    'password'             => [
        'letters'       => ':attribute には英字を含めてください。',
        'mixed'         => ':attribute には大小の英字を含めてください。',
        'numbers'       => ':attribute には数字を含めてください。',
        'symbols'       => ':attribute には記号を含めてください。',
        'uncompromised' => '指定の :attribute は情報漏えいデータベースに存在します。別のパスワードを使用してください。',
    ],
    'present'              => ':attribute は存在している必要があります。',
    'prohibited'           => ':attribute は禁止されています。',
    'prohibited_if'        => ':other が :value のとき、:attribute は禁止されています。',
    'prohibited_unless'    => ':other が :values のいずれかでない限り、:attribute は禁止されています。',
    'prohibits'            => ':attribute は :other の入力を禁止します。',
    'regex'                => ':attribute の形式が不正です。',
    'required'             => ':attribute は必須です。',
    'required_array_keys'  => ':attribute には :values の項目が必要です。',
    'required_if'          => ':other が :value のとき、:attribute は必須です。',
    'required_if_accepted' => ':other が承認されたとき、:attribute は必須です。',
    'required_unless'      => ':other が :values のいずれかでない限り、:attribute は必須です。',
    'required_with'        => ':values が存在するとき、:attribute は必須です。',
    'required_with_all'    => ':values がすべて存在するとき、:attribute は必須です。',
    'required_without'     => ':values が存在しないとき、:attribute は必須です。',
    'required_without_all' => ':values がいずれも存在しないとき、:attribute は必須です。',
    'same'                 => ':attribute と :other が一致しません。',
    'size'                 => [
        'array'   => ':attribute の項目数は :size 個でなければなりません。',
        'file'    => ':attribute のファイルサイズは :size KBでなければなりません。',
        'numeric' => ':attribute は :size でなければなりません。',
        'string'  => ':attribute は :size 文字でなければなりません。',
    ],
    'starts_with'          => ':attribute は次のいずれかで始まる必要があります: :values。',
    'string'               => ':attribute は文字列で指定してください。',
    'timezone'             => ':attribute には有効なタイムゾーンを指定してください。',
    'uploaded'             => ':attribute のアップロードに失敗しました。',
    'url'                  => ':attribute には有効なURLを指定してください。',
    'uuid'                 => ':attribute には有効なUUIDを指定してください.',

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション言語行
    |--------------------------------------------------------------------------
    | 'attribute.rule' キーの規約で、特定属性×特定ルールの
    | メッセージを個別に指定できます。
    */
    'custom' => [
        // 例：画面の並び順（同一フォーム内の重複を明示的に）
        'display_order' => [
            'unique' => '同じフォーム内で :attribute が重複しています。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 属性名の置換
    |--------------------------------------------------------------------------
    | ここでは「title」→「タイトル」など、
    | エラーメッセージ内で表示される属性名を人間に分かりやすい日本語に置換します。
    */
    'attributes' => [
        // 共通
        'email'         => 'メールアドレス',
        'password'      => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'name'          => '氏名',
        'title'         => 'タイトル',
        'description'   => '説明',
        'is_active'     => '有効フラグ',
        'status'        => 'ステータス',

        // 本プロジェクトの主要カラム
        'display_order' => '表示順序',
        'form_id'       => 'フォームID',
        'screen_id'     => '画面ID',
        'question_id'   => '質問ID',
        'type'          => '種別',
        'help_text'     => '補足説明',
        'max_select'    => '選択肢最大数',
        'label'         => '表示ラベル',
        'value'         => '保存値',
        'free_text'     => '自由記述',
        'numeric_value' => '数値',
        'date_value'    => '日付',
    ],
];
