<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use Socialite;

class SocialAuthController extends Controller
{

    protected $request;

    public function __construct(\Illuminate\Http\Request $request)
    {
        $this->request = $request;
    }

    public function redirect($service)
    {
        //return Socialite::driver($service)->stateless()->redirect();
        //Not using stateless in this case to optionally include the
        //Authenticator alias (used for authenticatior disambiguation)
        $this->setAlias('AROLOGIN');
        return Socialite::driver($service)->redirect();
    }

    public function callback($service)
    {

        //$user = Socialite::with($service)->stateless()->user();
        //Not using stateless in this case to optionally include the
        //Authenticator alias (used for authenticatior disambiguation)
        $alias = $this->getAlias();
        $user  = Socialite::with($service)->user();

//get token through guzzle (todo) (for now redirect to auth server)
        $authserver = env('AUTH_SERVER', 'http://mbtweb.dev');
        $away       = $authserver . '/gettoken/' . $service . '?alias=' . $alias . '&token=' . $user->token;

        return redirect()->away($away);
    }

    private function setAlias($alias)
    {
        $this->request->session()->put('company_alias', $alias);
    }

    private function getAlias()
    {
        return $this->request->session()->get('company_alias', 'VANILLA');
    }

}
