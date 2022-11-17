<?php

namespace TransformStudios\Gated\Tags;

use Statamic\Tags\Concerns\RendersForms;
use Statamic\Tags\Tags;

class Gate extends Tags
{
    use RendersForms;

    /**
     * @return bool
     */
    public function authorized()
    {
        if ($this->context->raw('gated_by') !== 'roles') {
            return true;
        }

        return ! empty(array_intersect(
            request('roles', []),
            $this->context->raw('authorized_roles')
        ));
    }

    public function passwordForm()
    {
        $action = route('statamic.gated.password.store');
        $method = 'POST';

        $html = $this->formOpen($action, $method);

        $html .= '<input type="hidden" name="redirect" value="'.request()->query('redirect').'" />';
        $html .= '<input type="hidden" name="id" value="'.request()->query('id').'" />';

        $html .= $this->parse();

        $html .= $this->formClose();

        return $html;
    }
}
