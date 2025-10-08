<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 全コントローラの基底クラス
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
