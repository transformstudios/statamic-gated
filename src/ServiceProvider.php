<?php

namespace TransformStudios\Gated;

use Statamic\Providers\AddonServiceProvider;
use TransformStudios\Gated\Http\Middleware\AddRolesToQueryString;
use TransformStudios\Gated\Tags\Gate;

class ServiceProvider extends AddonServiceProvider
{
    protected $middlewareGroups = [
        'web' => [
            AddRolesToQueryString::class,
        ],
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
    ];

    protected $tags = [
        Gate::class,
    ];
}
