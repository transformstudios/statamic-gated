<?php

namespace TransformStudios\Gated\Tags;

use Statamic\Tags\Tags;

class Gate extends Tags
{
    /**
     * @return bool
     */
    public function authorized()
    {
        if (! $this->context->value('is_gated')) {
            return true;
        }

        return ! empty(array_intersect(
            request('roles', []),
            $this->context->raw('authorized_roles')
        ));
    }
}
