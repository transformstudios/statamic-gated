<?php

namespace TransformStudios\Gated\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PasswordController
{
    public function store(Request $request)
    {
        Session::put('gated.validated_password', $request->password);

        return redirect($request->redirect);
    }
}
