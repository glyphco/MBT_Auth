<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;
use Socialite;

class JWTAuthController extends Controller
{

    protected $request;
    protected $user;

    public function __construct(\Illuminate\Http\Request $request, User $user)
    {
        $this->request = $request;
        $this->user    = $user;
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
            $user = new $this->user;
            $fill = [
                $service_ID => $socialize_user->getId(),
                'name'      => $socialize_user->getName(),
                'email'     => $socialize_user->getEmail(),
                'avatar'    => $socialize_user->getAvatar(),
            ];
            $user = $user->fill($fill);
            $user->save();
        }

        $JWT = JWTAuth::fromUser($user);

        return response()->json(compact('JWT'));

    }

}
