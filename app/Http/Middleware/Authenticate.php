<?php

namespace App\Http\Middleware;

use Closure;

class Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // If specific guards are provided (e.g., auth:web,students), honor them.
        // Otherwise, check both 'web' and 'students' by default.
        $guardsToCheck = !empty($guards) ? $guards : ['web', 'students'];

        foreach ($guardsToCheck as $guard) {
            if (auth()->guard($guard)->check()) {
                return $next($request);
            }
        }

        return redirect('login');
    }
}
