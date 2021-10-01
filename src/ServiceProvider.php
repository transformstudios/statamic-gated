<?php

namespace TransformStudios\Gated;

use Statamic\Providers\AddonServiceProvider;
use TransformStudios\Gated\Http\Middleware\AddRolesToQueryString;
use TransformStudios\Gated\Modifiers\Intersect;
use TransformStudios\Gated\Tags\Gate;

class ServiceProvider extends AddonServiceProvider
{
    protected $middlewareGroups = [
        'web' => [
            AddRolesToQueryString::class,
        ],
    ];

    protected $modifiers = [
        Intersect::class,
    ];

    protected $tags = [
        Gate::class,
    ];
}
