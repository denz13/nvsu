<?php

namespace App\Http\Middleware;

use Closure;

class LoggedIn
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // If guards provided, use them; otherwise check both common session guards
        $guardsToCheck = !empty($guards) ? $guards : ['web', 'students'];

        foreach ($guardsToCheck as $guard) {
            if (auth()->guard($guard)->check()) {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
