<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\APIResponderTrait;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Validator;

class LocalAuthController extends Controller
{

    use ThrottlesLogins;
    use APIResponderTrait;

    protected $registrationRules = [
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255',
        'password' => 'required|string|min:6|confirmed',
    ];

    protected $loginRules = [
        'email'    => 'required|string',
        'password' => 'required|string',
    ];

    protected $emailcheckRules = [
        'email' => 'required|string',
    ];

    public function register(Request $request)
    {

        $email = $request->input('email', null);

        $check = \App\Models\User::where('email', $email)->whereNotNull('password')->first();
        if ($check) {
            //user exists in system
            return $this->clientErrorResponse('email already registered');
        }

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->registrationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }
            $data = $this->create($request->all());
            return $this->createdResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }

    public function login(Request $request)
    {

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->loginRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }

        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            //$this->fireLockoutEvent($request);
            //return $this->sendLockoutResponse($request);
            return $this->clientErrorResponse('too many attempts');
        }

        $email    = $request->input('email', null);
        $password = $request->input('password', null);

        $check = \App\Models\User::where('email', $email)->whereNotNull('password')->first();
        if ($check) {
            if (!$check->confirmed) {
                return $this->clientErrorResponse('user has not been confirmed');
            }
            if (Hash::check($password, $check->password)) {
                $JWT = JWTAuth::fromUser($check);
                return response()->json(compact('JWT'));
            }

        }

        $this->incrementLoginAttempts($request);

        return $this->clientErrorResponse('email or password incorrect');
    }

    public function emailcheck(Request $request)
    {
        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->emailcheckRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }

        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
        $email    = $request->input('email', null);
        $check    = \App\Models\User::where('email', $email)->get();
        $accounts = [];
        foreach ($check as $key => $value) {
            if ($value->password) {$accounts['local'] = 1;}
            if ($value->facebook_id) {$accounts['facebook'] = 1;}
            if ($value->google_id) {$accounts['google'] = 1;}
        }

        return $this->listResponse($accounts);
    }

    protected function create(array $data)
    {
        return \App\Models\User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => bcrypt($data['password']),
            'confirmed' => env('AUTOCONFIRMUSER', false),
        ]);
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);
    }

    public function username()
    {
        return 'email';
    }

}
