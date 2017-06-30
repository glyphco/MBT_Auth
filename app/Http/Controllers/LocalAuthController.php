<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LocalAuthController extends Controller
{

    protected $request;
    protected $user;

    protected $validationRules = [
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ];

    public function __construct(\Illuminate\Http\Request $request, User $user)
    {
        $this->request = $request;
        $this->user    = $user;
    }

    public function register(Request $request)
    {

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
        ?: redirect($this->redirectPath());

    }

}
