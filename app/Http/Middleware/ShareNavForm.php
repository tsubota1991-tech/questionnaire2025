<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\Screen;
use App\Models\Question;

class ShareNavForm
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $navForm = null;

        if ($route) {
            // /forms/{form}/... のように Form が直接来る
            $paramForm = $route->parameter('form');
            if ($paramForm instanceof Form) {
                $navForm = $paramForm;
            } else {
                // /screens/{screen}（shallow）
                $paramScreen = $route->parameter('screen');
                if ($paramScreen instanceof Screen) {
                    $navForm = $paramScreen->form;
                } else {
                    // /questions/{question}（shallow）
                    $paramQuestion = $route->parameter('question');
                    if ($paramQuestion instanceof Question) {
                        $navForm = $paramQuestion->form;
                    }
                }
            }
        }

        view()->share('navForm', $navForm);
        return $next($request);
    }
}
