<?php

namespace App\Http\Controllers\Auth;

use App\Auth\ArcGISAuth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
 * The code of this class is from https://github.com/jotaelesalinas/laravel-simple-ldap-auth
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username() {
        return 'username';
    }

    protected function validateLogin(Request $request) {
        $this->validate($request, [
            $this->username() => 'required|string|regex:/^\w+$/',
            'password' => 'required|string',
        ]);
    }

    protected function attemptLogin(Request $request) {
        $credentials = $request->only($this->username(), 'password');
        $username = $credentials[$this->username()];
        $password = $credentials['password'];
        
        // Instead of validating against LDAP, we use a class to validate against an ArcGIS Server
        $token = ArcGISAuth::attempt($username, $password, config('auth.passwords.users.expire'));
        if ($token) {
            $user = \App\User::where($this->username(), $username) -> first();
            if (!$user) {
                // the user doesn't exist in the local database, so we have to create one
                $user = new \App\User();
                $user->username = $username;
                $user->password = '';
            }
            $user->remember_token = $token;
            
            // by logging the user we create the session, so there is no need to login again (in the configured time).
            // pass false as second parameter if you want to force the session to expire when the user closes the browser.
            // have a look at the section 'session lifetime' in `config/session.php` for more options.
            $this->guard()->login($user, true);
            
            // if the user has logged correctly, save/update the user information in the local database 
            $user->save();
            return true;
        }
        
        // the user doesn't exist in the ArcGIS server or the password is wrong
        // log error
        return false;
    }
        
    protected static function accessProtected ($obj, $prop) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}
