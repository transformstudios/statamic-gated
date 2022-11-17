<?php

namespace TransformStudios\Gated\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\User as UserFacade;
use Statamic\Structures\Page;
use Statamic\Support\Arr;

class HandleGate
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Page */
        if (! $page = EntryFacade::findByUri($request->getRequestUri())) {
            return $next($request);
        }

        return match ($this->gate($page)) {
            'password' => $this->handlePassword($request, $next, $page),
            'roles' => $this->handleRoles($request, $next, $page),
            default => $next($request),
        };

        return $next($request);
    }

    private function handlePassword(Request $request, Closure $next, Page $page)
    {
        if (session('gated.validated_password') === $page->password) {
            return $next($request);
        }

        return abort(redirect()->route(
            'statamic.gated.password.show',
            [
                'redirect' => $request->getRequestUri(),
                'id' => $page->id(),
            ]
        ));
    }

    private function handleRoles(Request $request, Closure $next, Page $page)
    {
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
    }

    private function gate(Page $page): ?string
    {
        return $page->gated_by?->value();
    }
}
