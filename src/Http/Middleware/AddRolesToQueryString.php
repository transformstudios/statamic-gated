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
        // if it doesn't have roles, redirect to add roles to query string
        // @todo should check for get as well, don't want to add to non-page routes, like the "review" url
        // can we check the name of the route to see if it starts w/ `statamic`?
        $qsRoles = $request->query('roles');

        /** @var \Statamic\Auth\User */
        $user = $request->user();

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
            if (collect($qsRoles)->diff($userRoles)->isEmpty()) {
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
