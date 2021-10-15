<?php

namespace TransformStudios\Gated\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\User as UserFacade;
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

        if (! $user = $request->user()) {
            // if roles on qs redirect w/o roles
            return $qsRoles ? redirect($request->fullUrlWithoutQuery('roles')) : $next($request);
        }

        /** @var \Statamic\Auth\User */
        $user = UserFacade::fromUser($user);

        if (! $userRoles = $user->roles()->keys()->all()) {
            return $next($request);
        }

        if (! $qsRoles) {
            return redirect($request->fullUrlWithQuery(['roles' => $userRoles]));
        }

        // if the roles are the same, don't do anything
        if (array_diff($qsRoles, $userRoles) == array_diff($userRoles, $qsRoles)) {
            return $next($request);
        }

        // if the roles are different, remove existing roles and put user ones back on
        return redirect($request->url().'?'.Arr::query(array_merge(
            Arr::except($request->query(), 'roles'),
            ['roles' => $userRoles]
        )));

        return $next($request);
    }
}
