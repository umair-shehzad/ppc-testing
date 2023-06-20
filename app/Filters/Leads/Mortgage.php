<?php

namespace App\Filters\Leads;

use Closure;

class Mortgage
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('mortgage')) {
            return $next($request);
        }

        return $next($request)->where('mortgage', request()->input('mortgage'));
    }
}
