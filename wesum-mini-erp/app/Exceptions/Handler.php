<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register()
    {
        $this->renderable(function (TokenMismatchException $e, $request) {
            // Redirect POST /login (and named login route) back to login form
            if ($request->is('login') || $request->routeIs('login')) {
                return redirect()
                    ->route('login')
                    ->withErrors(['session' => 'Your session has expired. Please log in again.']);
            }
            // Other CSRF failures → home
            return redirect('/')
                ->withErrors(['session' => 'Your session has expired. Please try again.']);
        });
    }
}
