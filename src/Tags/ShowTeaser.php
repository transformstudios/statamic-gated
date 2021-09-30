<?php

namespace TransformStudios\Gated\Tags;

use Statamic\Tags\Tags;

class ShowTeaser extends Tags
{
    /**
     * The {{ now_or_param }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        if (! $this->context->value('is_gated')) {
            return false;
        }

        return ! empty(array_intersect(
            request('roles', []),
            $this->context->raw('authorized_roles')
        ));
    }
}
