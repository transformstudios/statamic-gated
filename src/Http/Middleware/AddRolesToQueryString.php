<?php

namespace TransformStudios\Gated\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use Statamic\Support\Arr;

class AddRolesToQueryString
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! config('gated.enabled')) {
            return $next($request);
        }

        if (Route::currentRouteName() !== 'statamic.site') {
            return $next($request);
        }

        $qsRoles = $request->query('roles');

        /** @var \Statamic\Auth\User */
        $user = \Statamic\Facades\User::fromUser($request->user());

        $userRoles = $user?->roles()->keys()->all();

        // not logged in, but there are roles, likely someone shared this link
        if (! $user && $qsRoles) {
            return redirect($request->fullUrlWithoutQuery('roles'));
        }

        // not logged, move on
        if (! $user) {
            return $next($request);
        }

        // if no roles on qs and the user has roles, redirect w/ roles
        if (! $qsRoles && $userRoles) {
            return redirect($request->fullUrlWithQuery(['roles' => $userRoles]));
        }

        // if there are roles on the qs and the user has roles
        if ($qsRoles && $userRoles) {
            // if the roles are the same, don't do anything
            if (array_diff($qsRoles, $userRoles) == array_diff($userRoles, $qsRoles)) {
                return $next($request);
            }

            // if the roles are different, remove existing roles and put user ones back on
            return redirect($request->url().'?'.Arr::query(array_merge(
                Arr::except($request->query(), 'roles'),
                ['roles' => $userRoles]
            )));
        }

        return $next($request);
    }
}
