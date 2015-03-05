<?php

class Account extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->library('session');

		$this->load->model('User');
	}

	/*
		Signup page with a signup form. With an optional status message.

		@param string $status a status message to display to the user at the form.
	*/
	function signup( $status = Null )
	{
		$data['title'] = 'Signup';
		if( $status == "failed" )
		{
			$data['message'] = "Error, you entered something in wrong!.";	
		}
		
		$this->load->view('templates/header', $data);
		$this->load->view('pages/signup', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/*
		Loads a login page with an optional status.

		@param string $status a status message to display to the user at the form.
	*/	
	function login( $status = Null )
	{
		$data['title'] = 'Login';
		
		if( $status == "failed" )
		{
			data['message'] = 'Error, invalid username or password';
		}

		$this->load->view('templates/header', $data);
		$this->load->view('pages/login', $data);
		$this->load->view('templates/footer', $data);
	}

	/*
		Checks the user session cookie for a login, if there is none then
		the controller will store the post data from the signin form, and
		call its login function. If the login attempt fails then it will
		display an error, if it succeeds then it will redirect to a homepage.
	*/
	function login_form()
	{
		//Check the session cookie for a username.
		if( $this->checkCookieLogin() )
		{
			echo 'logged in!';
		}
		else
		{
			//Grab the post data from the form.
			$email = $this->input->post('email');
			$password = $this->input->post( 'password' );

			$this->load->helper('url');
			//Call the login function to attempt a login.
			if( $this->authenticate( $email, $password ) )
			{
				redirect('home/user_dashboard');
			}
			else
			{
				//Just for testing.
				echo 'failed to login';

				redirect('account/signup/failed');
			}

		}
	}		
	
	/*
		The function collects the user form data and passes it to
		the signup function in the User model, if it it fails it will
		show an error, otherwise the user will be redirected.
	*/
	function signup_form()
	{
		//Collect post data.
		$username = $this->input->form('username');
		$password = $this->input->form('password');
		$email = $this->input->form('email');

		//Call the signup function to attempt to login
		if( $this->User->signup($username, $password, $email) )
		{
			echo 'success!';
		}	
		else
		{
			echo 'failure';
		}
	}

	/*
		Check the cookie for login.

		@return true if the cookie has a login sessiona and is a valid cookie, or otherwise false.
	*/
	public function checkCookieLogin()
	{
		//Check to see if the email in the cookie is set, if it is, then the user logged in already.
		if( $this->session->userdata('email') != NULL )
		{
			return True;	
		}
		else
		{
			return False;
		}		
	}

	/*
		@param $email string the user's email to authenticate.
		@param $password string the password to authenticate the email with.
	
		@return boolean True if there was a successful login, or
			 return false if the login attempt failed.
	*/
	function authenticate( $email, $password )
	{
		//Call the authentication method in the model which checks
		//for an email password matching the one passed.
		$user_id = $this->User->authenticate_user( $email, $password );

		//If there was something returned then set the cookie to hold the email password and user id.
		//If it doesn't then return false.
		if( $user_id != NULL )
		{
			$cookieInfo = array
			(
					'email' => $email,
					'password' => $password,
					'user_id' => $user_id
			);
	
			//Set the session cookie to the cookieInfo array.
			$CI->session->userdata($cookieInfo);

			//All authenticated, return true to show that it successfully completed.
			return True;
		}
		else
		{
			//Authentication failed, return false.
			return False;
		}
	}
}

?>
