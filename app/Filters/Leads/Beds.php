<?php

namespace App\Filters\Leads;

use Closure;

class Beds
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('beds')) {
            return $next($request);
        }

        return $next($request)->where('beds', request()->input('beds'));
    }
}
