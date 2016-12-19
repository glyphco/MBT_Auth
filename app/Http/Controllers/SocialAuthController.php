<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;
use Redirect;

class SocialAuthController extends Controller {

   protected $request;

   public function __construct(\Illuminate\Http\Request $request)
   {
       $this->request = $request;
   }

	public function redirect($service) {
		return Socialite::driver ( $service )->stateless()->redirect();
	}

	public function callback($service) {
	$request = $this->request->all();

	if (array_key_exists('doit', $request)) {
		$user = Socialite::driver($service)->userFromToken($request['token']);
		return view ( 'home' )->withDetails ( $user )->withService ( $service );
	}

$user = Socialite::with ( $service )->stateless()->user ();

	$away = 'http://mbtweb.dev/callback/'.$service.'?doit&token='.$user['token'];

	return redirect()->away($away);

	}
}