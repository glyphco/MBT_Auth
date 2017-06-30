<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;

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

    public function gettoken($service)
    {
        $request        = $this->request->all();
        $socialize_user = Socialite::driver($service)->userFromToken($request['token']);

        //Used for authenticator disambiguation
        //$company_alias  = $request['alias'];

        //Holder for the service ID field in the Database (ex: facebook_id)
        $service_ID = $service . '_id';

        //tie in the user model
        $user = User::where($service_ID, $socialize_user->getId())->first();

        // register (if no user)
        if (!$user) {
            $user = $this->resisterUser($service, $socialize_user);
        }

        $JWT = JWTAuth::fromUser($user);
        return response()->json(compact('JWT'));
    }

    private function resisterUser($service, $socialize_user)
    {

        env('AUTOCONFIRMUSER', 0);
        $service_ID = $service . '_id';
        $user       = new $this->user;

//TEST WITH EMPTY EMAIL!!!!!
        //remove the socialid@social.com fake email.
        $fill = [
            $service_ID => $socialize_user->getId(),
            'name'      => $socialize_user->getName(),
            'email'     => $socialize_user->getEmail() ?? $socialize_user->getId() . '@' . $service . '.com',
            'avatar'    => $socialize_user->getAvatar(),
            'confirmed' => env('AUTOCONFIRMUSER', 0),
        ];

//use Validator;
        // $validator = Validator::make($input, $rules);

        // if ($validator->fails()) {
        //     return response([
        //         'error'   => true,
        //         'message' => "Could not save.",
        //         'errors'  => $validator->messages()],
        //         422
        //     );

        //  $this->validate($fill, [
        //      $service_ID => 'required',
        //      'name' => 'required',
        // 'email' => 'required',
        // 'avatar' => 'required',
        //  ]);

        if ($this->isglyph($service, $socialize_user)) {
            $fill['confirmed'] = 1;
            $fill['username']  = 'glypher';
        }
        $user = $user->fill($fill);
        $user->save();
        return $user;
    }

    private function isglyph($service, $socialize_user)
    {
        if (env('glyph_' . $service, 0) == $socialize_user->getId()) {
            return true;
        }
        return false;
    }
}
