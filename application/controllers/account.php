<?php
//TODO: Sanitize input forms completely.
//TODO: Test login form authentication, after login form is working perfectly.

class Account extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->library('session');
		$this->load->library('input');
		
		$this->load->helper('url');

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
			$data['message'] = 'Error, invalid username or password';
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
		if( $this->check_cookie_login() )
		{
			//The user has already authenticated, send them to their profile home.
			redirect('user_profile/home', 'location');
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
				redirect('user_profile/home', 'location');
			}
			else
			{
				redirect('account/signup/failed', 'location');
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

		//Check the session cookie for if the user has already authenticated.
		if( $this->check_cookie_login() )
		{
			//The user has already authenticated, send them to their profile home.
			redirect('user_profile/home', 'location');
		}
		else
		{
			//Collect post data.
			$username = $this->input->post('username');
			$password = $this->input->post('password');
			$first_name = $this->input->post('first_name');
			$last_name = $this->input->post('last_name');
			$email = $this->input->post('email');
			

			/*
				First try to insert the user, if success, get their id and redirect.
				If that fails because there is already a user with $email as their $email,
				then try to authenticate with $email and $password, if that fails, then redirect
				them to a failed login page.
			*/
			if( $this->User->insert_user($username, $password, $email, $first_name, $last_name) )
			{
				//Add email and password to the cookie.
				$user_id = $this->User->get_id( $email );
				$this->add_cookie_data( $email, $user_id );

				redirect('user_profile/home', 'location');
			}	
			else if( $this->authenticate($email, $password) ) 
			{
				redirect('user_profile/home', 'location');
			}
			else
			{	
				redirect('account/signup/failure', 'location');
			}
			
		}
	}

	/*
		Check the cookie for login.

		@return true if the cookie has a login sessiona and is a valid cookie, otherwise false.
	*/
	public function check_cookie_login()
	{
		//Check to see if the email in the cookie is set, if it is,
		//then the user logged in by setting the email.
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

		//If there was something returned then set the cookie to hold the email password
		//and user id. If it doesn't then return false.
		if( is_numeric($user_id) )
		{
	
			//Set the session cookie to the cookieInfo array.
			if( $this->add_cookie_data( $email, $user_id ) )
			{
				//Cookie data added. Return true to show that it successfully completed.
				return True;
			}

		}

		//Authentication failed, return false.
		return False;
	}

	/*
		Add the $email and $id arguments to the cookie.
		
		@param string $email the email of the user.
		@param integer $id the id of the user.
		
		@return boolean false if adding the data to the cookie fails, and true if it succeeds.
	*/

	function add_cookie_data( $email, $id )
	{
		//Set the email and id keys to $email and $id arguments.
		$login_data = array
		(
			'email' => $email,
			'id' => $id
		);

		//Add $login_data contents to the session cookie to enable cookie login with cookies.
		$this->session->set_userdata( $login_data );

		//TODO: Add check to make sure the user's browser has cookies enabled. Until then
		//always return True.
		return True;
	}
}

?>
