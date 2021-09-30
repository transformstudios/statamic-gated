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
        /* @var \Statamic\Auth\User */
        if (! $user = $request->user()) {
            if ($request->query('roles')) {
                return redirect($request->fullUrlWithoutQuery('roles'));
            }

            return $next($request);
        }

        $roles = $user->roles()->keys()->all();
        if (! $request->has('roles') && $roles) {
            return redirect($request->fullUrlWithQuery(['roles' => $roles]));
        }

        if ($request->has('roles') && $roles) {
            if ($request->get('roles') == $roles) {
                return $next($request);
            }
            // remove existing roles params
            $query = Arr::except($request->query(), 'roles');

            return redirect($request->url().'?'.Arr::query(array_merge($query, ['roles' => $roles])));
        }

        return $next($request);
    }
}
