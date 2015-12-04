<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');

class Api extends REST_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	
	function __construct()
	 {
	   parent::__construct();
	   $this->load->model('user','',TRUE);
       $this->load->model('device','',TRUE);
       
       // if(!in_array($this->router->method, $this->config->item('allowed_calls_without_token')))
       // {
       //      $headers = getallheaders();
       //      if(isset($headers['Token']))
       //      {
       //          if(isset($headers['Userid']))
       //          {
       //              if(!$this->device->validToken($headers['Userid'],$headers['Token']))
       //              {
       //                  $data["header"]["error"] = "1";
       //                  $data["header"]["message"] = "Please provide valid token";
       //                  $this->response($data, 200);                     
       //              }    
       //          }   
       //          else
       //          {
       //              $data["header"]["error"] = "1";
       //              $data["header"]["message"] = "Please provide user id (header)";
       //              $this->response($data, 200);              
       //          } 
       //      } 
       //      else
       //      {
       //          $data["header"]["error"] = "1";
       //          $data["header"]["message"] = "Please provide access token";
       //          $this->response($data, 200);       
       //      }    
        
       // } 
        
       
	 }

	public function index()
	{
		$this->load->view('welcome_message');
	}

    

    function updateDevice_post()
    {
        $device_id = $this->post('device_id');
        $user_id = $this->post('user_id');     
        $type = $this->post('device_type');     
        
        if(!$device_id)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "Please provide device id";
            $this->response($data, 400);
        }

        if(!$user_id)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "Please provide user id";
            $this->response($data, 400);
        }

        $user_present = $this->user->checkUserById($user_id);
        
        

        if($user_present == false)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "No user present with this id.";
            $this->response($data, 400);
        }   
        else
        {
            
            $device = $this->device->get_user_device($user_id);
            if(count($device) > 0)
            {
                //update device table
                $device_data = array('uid'=>$device_id, 'type'=>$type);
                $this->device->edit_device($user_id, $device_data);
            }
            else
            {
                if(isset($type) && isset($device_id))
                {

                    //insert device table
                    $device_data = array('user_id'=>$user_id,'uid'=>$device_id, 'type'=>$type);
                    $this->device->insert_device($device_data);
                }
            }

            $data["header"]["error"] = "0";
            $data["header"]["message"] = "Device id updated.";
            $this->response($data, 200);
        }

        
            
    }

    function logout_post()
    {
        $headers = getallheaders();
        $user_id = $headers['Userid'];
        $token = $headers['Token'];
        $this->device->delete_device($user_id,$token);
        $data["header"]["error"] = "0";
        $data["header"]["message"] = "Username logout successfully";
        $this->response($data, 200);
    }

    function signup_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');
        $device_id = $this->post('device_id');
        $device_type = $this->post('device_type');
        $email = $username;
        $verified = 1;

        if(!$username)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "Username is required";
            $this->response($data, 400);
        }   
        if(!$password)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "Password is required";
            $this->response($data, 400);
        } 

        $user = array("username"=>$username,"password"=>md5($password),"email"=>$email,"verified"=>$verified);
        $user_id = $this->user->add_user($user);

        //insert device table
        if(isset($device_type) && isset($device_id))
        {
            $device_data = array('user_id'=>$user_id,'uid'=>$device_id, 'type'=>$device_type);
            $this->device->insert_device($device_data);
        }  

        $data["header"]["error"] = "0";
        $data["header"]["message"] = "Signup successfull";
        $this->response($data, 200);
    }

	function login_post()
    {
    	$data = array();

    	$username = $this->post('username');
    	$password = $this->post('password');
        $device_id = $this->post('device_id');
        $device_type = $this->post('device_type');

        if(!$username || !$password)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "Username or password is incorrect";
            $this->response($data, 400);
        }
        else
        {
            $result = $this->user->login($username, $password, 0);
            
            if($result !== false)
            {
                $user = (array) $result[0];
                $token = bin2hex(openssl_random_pseudo_bytes(16));    
                

                //insert device table
                if(isset($device_type) && isset($device_id))
                {
                    $device_data = array('user_id'=>$user['user_id'],'uid'=>$device_id, 'type'=>$device_type,'token'=>$token);
                    //$this->device->insert_device($device_data);

                    $device = $this->device->get_user_device($user['user_id'], $device_type);
                    if(count($device) > 0)
                    {
                        //update device table
                        $device_data = array('uid'=>$device_id, 'type'=>$device_type,'token'=>$token);
                        $this->device->edit_device($user['user_id'], $device_data);
                    }
                    else
                    {
                        //insert device table
                        $device_data = array('user_id'=>$user['user_id'],'uid'=>$device_id, 'type'=>$device_type,'token'=>$token);
                        $this->device->insert_device($device_data);
                    }    
                }    
                
                $array['user_id'] = $user['user_id'];
                $array['Token'] = $token;
                $array['user'] = $user;
                $data["header"]["error"] = "0";
                $data["header"]["message"] = "Login successfully";
                $data['body'] = $array;
            }
            else
            {
                $data["header"]["error"] = "1";
                $data["header"]["message"] = "Username or password is incorrect";
            }

            $this->response($data);
        }
    }

    function forgetPassword_post()
    {
    	$data = array();

        $username = $this->post('username');

        if(!$username)
        {
            $data["header"]["error"] = "1";
            $data["header"]["message"] = "Please provide username";
            $this->response($data,400);
        }
        else
        {
         
            $user = $this->user->checkUser($username);
            if(!$user)
            {
                $data["header"]["error"] = "1";
                $data["header"]["message"] = "No user found with this username";
                $this->response($data,200);
            }   
            else
            {
                $user = (array) $user[0];
                $temp_password = rand_string(8);
                $md5 = md5($temp_password);

                $user['password'] = $md5;
                $this->user->edit_user($user['user_id'],$user);
                
                    //email work here
                $subject = 'Your password has been changed successfully';
                $message = 'Your temporary password is '.$temp_password;
                $email = array('to'=>$user['email'], 'from'=>$this->config->item('default_email'),'subject'=>$subject, 'message'=>$message);
                
                //sendEmail($email);
                $data["header"]["error"] = "0";
                $data["header"]["message"] = $message;
                $this->response($data,200);
            } 
               
            
        }
    }
}
