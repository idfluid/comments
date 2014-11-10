<?php namespace Idfluid\Comments\Controllers;
use \Illuminate\Routing\Controllers\Controller;
class UsersController extends \BaseController {

	/**
	 * Store a newly created user in database.
	 *
	 * @return Response
	 */
	public function postRegister()
	{
		try
		{	$exp_email = explode('@', \Input::get('email'));
			$username = $exp_email[0];
			$redirect = $this->currentURL(\Input::get('url-back'));

			$user = \Sentry::createUser(array(
		        'first_name' => \Input::get('first_name'),
		    	'last_name'  => \Input::get('last_name'),
		    	'username'   => $username,
		        'email'      => \Input::get('email'),
		        'password'   => \Input::get('password'),
		        'activated' => true,
		    ));

			if ($user) {
				$login = \Sentry::findUserById($user->id);

			    // Log the user in
			    \Sentry::login($login, false);
		    	return \Redirect::to($redirect);
		    } else {
	        	return \Redirect::to($redirect)->with('message', 'The following errors occurred')->withErrors($validator)->withInput();
	    	}
		}
		catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
		    echo 'Login field is required.';
		}
		catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
		{
		    echo 'Password field is required.';
		}
		catch (Cartalyst\Sentry\Users\UserExistsException $e)
		{
		    echo 'User with this login already exists.';
		}
	}


	/**
	 * Checked user to database.
	 *
	 * @return Response
	 */
	public function postLogin()
	{
		try
		{
			$redirect = $this->currentURL(\Input::get('url-back'));
		    // Login credentials
		    $credentials = array(
		        'email'    => \Input::get('email'),
		        'password' => \Input::get('password'),
		    );

		    // Authenticate the user
		    $user = \Sentry::authenticate($credentials, false);

		    if($user)
		    {
		    	return \Redirect::to($redirect);
		    }
		}
		catch (Cartalyst\Sentry\Users\WrongPasswordException $e)
		{
		    echo 'Wrong password, try again.';
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		    echo 'User was not found.';
		}
		catch (Cartalyst\Sentry\Users\UserNotActivatedException $e)
		{
		    echo 'User is not activated.';
		}

		// The following is only required if the throttling is enabled
		catch (Cartalyst\Sentry\Throttling\UserSuspendedException $e)
		{
		    echo 'User is suspended.';
		}
		catch (Cartalyst\Sentry\Throttling\UserBannedException $e)
		{
		    echo 'User is banned.';
		}

		
	}

	public function facebookLogin()
	{
		// get data from input
	    $code = \Input::get( 'code' );
	   	$redirect = $this->currentURL(\Input::get('url-back'));

	    // get fb service
	    $fb = \OAuth::consumer( 'Facebook' );

	    // check if code is valid

	    // if code is provided get user data and sign in
	    if ( !empty( $code ) ) {

	        // This was a callback request from facebook, get the token
	        $token = $fb->requestAccessToken( $code );

	        // Send a request with it
	        $result = json_decode( $fb->request( '/me' ), true );

	        try
			{
			    $user = \Sentry::findUserByLogin($result['email']);

			    if($user){
			    	// Log the user in
    				\Sentry::login($user, false);
    				return \Redirect::to($redirect);
			    }
			    else {
			    	$register = \Sentry::createUser(array(
				        'first_name' => $result['first_name'],
				    	'last_name'  => $result['last_name'],
				    	'username'   => $result['username'],
				    	'provider'   => 'facebook',
				        'email'      => $result['email'],
				        'password'   => $result['email'],
				        'activated' => true,
				    ));
				    if ($register) {
						$login = \Sentry::findUserById($register->id);

					    // Log the user in
					    \Sentry::login($login, false);
				    	return \Redirect::to($redirect);
				    } else {
			        	return \Redirect::to($redirect)->with('message', 'The following errors occurred')->withErrors($validator)->withInput();
			    	}
			    }
			}
			catch (Cartalyst\Sentry\Users\WrongPasswordException $e)
			{
			    echo 'Wrong password, try again.';
			}

	    }
	    // if not ask for permission first
	    else {
	        // get fb authorization
	        $url = $fb->getAuthorizationUri();

	        // return to facebook login url
	         return \Redirect::to( (string)$url );
	    }
	}

	/**
	 * logut.
	 *
	 * @return Response
	 */
	public function logout()
	{
		\Sentry::logout();
		return \Redirect::to('/');
	}

	public function currentURL($url)
	{	
		$base = \URL::to('/');
		$current = str_replace($base,"",$url);
		return $current;
	}

}
