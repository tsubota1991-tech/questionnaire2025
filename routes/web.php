<?php

use Illuminate\Support\Facades\Route;
// ===== 管理画面コントローラ =====
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\ScreenController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ScreenQuestionController;
use App\Http\Controllers\Admin\ResponseController;
use App\Http\Middleware\ShareNavForm; // navForm 共有ミドルウェア


// .env → config を参照
$loginPath   = config('auth.login_path', 'login');
$adminPrefix = config('app.admin_prefix', 'admin'); // デフォルトは 'admin'

// ===== 認証コントローラ =====
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// ===== ゲスト用（ログイン画面） =====
Route::middleware('guest')->group(function () use ($loginPath) {
    Route::get($loginPath,  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post($loginPath, [AuthenticatedSessionController::class, 'store'])->name('login.post');
});

// ===== ログアウト =====
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ===== パラメータ制約 =====
Route::pattern('form',     '[0-9]+');
Route::pattern('screen',   '[0-9]+');
Route::pattern('question', '[0-9]+');
// ULID: 26文字英数字（Crockford Base32）
Route::pattern('response', '[0-9A-HJKMNP-TV-Z]{26}');

Route::scopeBindings();

// ===== 管理画面（要ログイン） =====
Route::middleware(['auth', ShareNavForm::class, 'throttle:120,1'])
     ->prefix($adminPrefix)
     ->group(function () {

    // ダッシュボード相当：/admin → /admin/forms
    Route::get('/', fn() => redirect()->route('forms.index'))->name('admin.home');

    // ===== フォーム =====
    Route::resource('forms', FormController::class);
    Route::post('forms/{form}/archive',   [FormController::class, 'archive'])->name('forms.archive');

    // ===== 画面（フォーム配下）=====
    Route::resource('forms.screens', ScreenController::class)->shallow();
    Route::match(['put','patch'], 'screens/{screen}/reorder', [ScreenController::class, 'reorder'])
        ->name('screens.reorder');

    // ===== 質問（フォーム配下）=====
    Route::resource('forms.questions', QuestionController::class)->shallow();
    Route::match(['put','patch'], 'questions/{question}/reorder', [QuestionController::class, 'reorder'])
        ->name('questions.reorder');

    // ===== 画面への質問配置 =====
    Route::get('screens/{screen}/layout',  [ScreenQuestionController::class, 'edit'])->name('screen_questions.edit');
    Route::post('screens/{screen}/layout', [ScreenQuestionController::class, 'update'])->name('screen_questions.update');

    // ===== 回答 =====
    Route::get('forms/{form}/responses',       [ResponseController::class, 'index'])->name('responses.index');
    Route::get('responses/{response}',         [ResponseController::class, 'show'])->name('responses.show');
    Route::post('responses/{response}/status', [ResponseController::class, 'changeStatus'])->name('responses.changeStatus');
    Route::get('forms/{form}/analytics',       [ResponseController::class, 'analytics'])->name('responses.analytics');
    Route::get('forms/{form}/export',          [ResponseController::class, 'export'])->name('responses.export');

    // ===== プレビュー =====
    Route::get('forms/{form}/preview', [FormController::class, 'preview'])->name('forms.preview');
});
