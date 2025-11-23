<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTemporaryRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
{
    $user = $request->user();
    
    if ($user->temporaryRoles()->where('end_date', '>', now())->exists()) {
        $user->load('temporaryRoles.role');
        foreach ($user->temporaryRoles as $assignment) {
            $user->assignRole($assignment->role);
        }
    }
    
    return $next($request);
}
}
