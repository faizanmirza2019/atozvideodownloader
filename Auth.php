<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
	public function __construct() {
		parent::__construct();
		if($this->session->has_userdata('vds_access') && $this->session->userdata('vds_access') == true && $this->session->has_userdata('vds_admin_access') && $this->session->userdata('vds_admin_access') == true) {
			redirect(base_url(ADMIN_CONTROLLER),"location");
			exit();
		}
		else {
			$this->load->library('encryption');
			if($this->input->cookie("vds_ud",true) != NULL) {
				$dataArr = $this->input->cookie("vds_ud",true);
				$dataArr = $this->encryption->decrypt($dataArr);
				$dataArr = json_decode(base64_decode($dataArr),true);
				if(is_array($dataArr) && count($dataArr) > 0) {
					$this->session->set_userdata($dataArr);
					redirect(base_url(ADMIN_CONTROLLER),"location");
					exit();
				}
			}
		}
	}
	
	public function index() {
		redirect(base_url(AUTH_CONTROLLER."/login"),"location");
		exit();
	}
	
	public function login() {
		$data = array();
		
		$ip = getIpAddress();
		$cacheVar = 'vds_invalid_login_counter_'.$ip;
		$counter = $this->cache->get($cacheVar);
		$captchaSettings = $this->DefaultModel->captchaSettings();
		
		$captchaCheck = false;
		if(!is_null($counter) && $counter >= $captchaSettings['captchaShowFailedAttempts'] && $captchaSettings['loginCaptcha'] == 1) {
			$captchaCheck = true;
			$data['captchaSettings'] = $captchaSettings;
		}

		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		
		$error = false;
		if(isset($_POST['submit'])) {
			$uoe = clearString($this->security->xss_clean($this->input->post("uoe")));
			$data['uoe'] = $uoe;
			$password = $this->input->post("password");
			$data['password'] = $password;
			
			if($captchaCheck == true && isset($_POST['g-recaptcha-response'])) {
				$gCaptchaSecret = $captchaSettings['secretKey'];
				$gCaptchaResponse = $this->input->post("g-recaptcha-response");
				$remoteIp = $this->input->ip_address();
				$url = "https://www.google.com/recaptcha/api/siteverify?".http_build_query(['secret' => $gCaptchaSecret,'remoteip' => $remoteIp,'response' => $gCaptchaResponse]);
				$response = getRemoteContents($url);
				$response = json_decode($response,true);
				if(!isset($response['success']) || $response['success'] != true) {
					$error = true;
					$data['errorMsg'] = "Invalid Captcha !!";
				}
			}
			
			if(!$error) {
				$this->load->model("AuthModel");
				$user = $this->AuthModel->checkUserRecord($uoe,md5($password));
				if(is_array($user) && count($user) > 0) {
					$dataArr = array(
						"vds_user" => $user,
						"vds_access" => true
					);
					if($user['role'] == "admin") {
						$dataArr['vds_admin_access'] = true;
					}
					$this->session->set_userdata($dataArr);
					$this->cache->delete($cacheVar);
					$this->input->set_cookie("vds_ud",$this->encryption->encrypt(base64_encode(json_encode($dataArr))),63072000);
					redirect(base_url(ADMIN_CONTROLLER),"location");
					exit();
				}
				else {
					$error = true;
					$data['errorMsg'] = "Invalid Login Credentials !!";
					if(!$counter) {
						$counter = 1;
						$this->cache->save($cacheVar, $counter, 86400);
					}
					else if($counter < $captchaSettings['captchaShowFailedAttempts']) {
						$counter++;
						$info = $this->cache->get_metadata($cacheVar);
						$setTime = $info['expire'] - (time());
						$this->cache->save($cacheVar,$counter,$setTime);
						if($counter >= $captchaSettings['captchaShowFailedAttempts'] && $captchaSettings['loginCaptcha'] == 1) {
							$captchaCheck = true;
							$data['captchaSettings'] = $captchaSettings;
						}
					}
				}
			}
		}
		$data['error'] = $error;
		$data['captchaCheck'] = $captchaCheck;
		$this->load->view("login",$data);
	}
	
	public function forgot_Password() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		$captchaSettings = $this->DefaultModel->captchaSettings();
		$captchaCheck = false;
		if($captchaSettings['forgotPasswordCaptcha'] == 1) {
			$captchaCheck = true;
			$data['captchaSettings'] = $captchaSettings;
		}
		if(isset($_POST['submit'])) {
			$uoe = clearString($this->security->xss_clean($this->input->post("uoe")));
			$data['uoe'] = $uoe;
			
			if($captchaCheck == true && isset($_POST['g-recaptcha-response'])) {
				$gCaptchaSecret = $captchaSettings['secretKey'];
				$gCaptchaResponse = $this->input->post("g-recaptcha-response");
				$remoteIp = $this->input->ip_address();
				$url = "https://www.google.com/recaptcha/api/siteverify?".http_build_query(['secret' => $gCaptchaSecret,'remoteip' => $remoteIp,'response' => $gCaptchaResponse]);
				$response = getRemoteContents($url);
				$response = json_decode($response,true);
				if(!isset($response['success']) || $response['success'] != true) {
					$error = true;
					$data['message'] = "Invalid Captcha !!";
				}
			}
			
			if(!$error) {
				if(empty($uoe)) {
					$error = true;
					$data['message'] = "Field should not be empty !!";
				}
				else {
					$data['message'] = "Check your email to reset your password !!";
					$this->load->model("AuthModel");
					$user = $this->AuthModel->checkUserRecord_I($uoe);
					if(is_array($user) && count($user) > 0) {
						$id = $user['id'];
						$resetCode = md5(uniqid());
						$cacheVar = "vds_pass_reset_".$resetCode;
						$dataArr = array();
						$dataArr['id'] = $id;
						$dataArr['resetCode'] = $resetCode;
						$this->cache->save($cacheVar,$dataArr,86400);
						$resetUrl = base_url(AUTH_CONTROLLER."/reset-password/".$resetCode);
						
						$this->load->library('email');
						$mailSettings = $this->DefaultModel->mailSettings();
						$config = array();
						if($mailSettings['smtpStatus'] == 1) {
							$config['protocol'] = 'smtp';
							$config['smtp_host'] = $mailSettings['host'];
							$config['smtp_port'] = $mailSettings['port'];
							$config['smtp_user'] = $mailSettings['username'];
							$config['smtp_pass'] = $mailSettings['password'];
						}
						$config['mailtype'] = 'html';
						$config['charset'] = 'iso-8859-1';
						$config['wordwrap'] = TRUE;
						$config['newline'] = "\r\n";
						$this->email->initialize($config);
						
						$host = getDomain(base_url());
						
						$message = "<p>Hello <strong>".$user['username'].",</strong></p>";
						$message .= '<p>You have requested a password reset on <a target="_blank" href="'.base_url().'"><strong>'.$host.'</strong></a>. Here are your login details:</p>';
						$message .= "<p>Your login: <strong>".$user['username']."</strong> or <strong>".$user['email']."</strong></p>";
						$message .= '<p>To change your password, please follow this link: <a target="_blank" href="'.$resetUrl.'"><strong>'.$resetUrl.'</strong></a></p>';
						$message .= '<p>If you have not requested a password reset on <a target="_blank" href="'.base_url().'"><strong>'.$host.'</strong></a>, please ignore this message! This message was sent automatically, so do not reply to it.</p>';
						$message .= '<p>Thanks,<strong>'.$settings['title'].'</strong></p>';
						
						$this->email->from("no-reply@".$host, $host);
						$this->email->reply_to("no-reply@".$host, $host);
						$this->email->to($user['email']);
						$this->email->subject("Password Reset Request : ".$host);
						$this->email->message($message);
						$this->email->send();
					}
				}
			}
		}
		$data['error'] = $error;
		$data['captchaCheck'] = $captchaCheck;
		$this->load->view("forgot-password",$data);
	}
	
	public function reset_password($resetCode = null) {
		$resetCode = clearString($this->security->xss_clean($resetCode));
		$cacheVar = "vds_pass_reset_".$resetCode;
		if($dataArr = $this->cache->get($cacheVar)) {
			if($dataArr['resetCode'] == $resetCode) {
				$data = array();
				$data['resetCode'] = $resetCode;
				$settings = $this->DefaultModel->generalSettings();
				$data['settings'] = $settings;
				
				$captchaSettings = $this->DefaultModel->captchaSettings();
				$captchaCheck = false;
				if($captchaSettings['resetPasswordCaptcha'] == 1) {
					$captchaCheck = true;
					$data['captchaSettings'] = $captchaSettings;
				}
				
				$error = false;
				if(isset($_POST['submit'])) {
					$newPassword = $this->input->post("newPassword");
					$data['newPassword'] = $newPassword;
					$confirmNewPassword = $this->input->post("confirmNewPassword");
					$data['confirmNewPassword'] = $confirmNewPassword;
					
					if($captchaCheck == true && isset($_POST['g-recaptcha-response'])) {
						$gCaptchaSecret = $captchaSettings['secretKey'];
						$gCaptchaResponse = $this->input->post("g-recaptcha-response");
						$remoteIp = $this->input->ip_address();
						$url = "https://www.google.com/recaptcha/api/siteverify?".http_build_query(['secret' => $gCaptchaSecret,'remoteip' => $remoteIp,'response' => $gCaptchaResponse]);
						$response = getRemoteContents($url);
						$response = json_decode($response,true);
						if(!isset($response['success']) || $response['success'] != true) {
							$error = true;
							$data['message'] = "Invalid Captcha !!";
						}
					}
					
					if(!$error) {
						if(empty($newPassword) || empty($confirmNewPassword)) {
							$error = true;
							$data['message'] = "Fields should not be empty !!";
						}
						else if($newPassword != $confirmNewPassword) {
							$error = true;
							$data['message'] = "Passwords does not match !!";
						}
						else {
							$id = $dataArr['id'];
							$this->load->model("AuthModel");
							$this->AuthModel->updatePassword(md5($newPassword),$id);
							$this->cache->delete($cacheVar);
							$data['message'] = 'Password reset successfully <a href="'.base_url(AUTH_CONTROLLER."/login").'">Login Here</a>';
						}
					}
				}
				$data['error'] = $error;
				$data['captchaCheck'] = $captchaCheck;
				$this->load->view("reset-password",$data);
			}
			else {
				redirect(base_url(AUTH_CONTROLLER."/forgot-password"),"location");
				exit();
			}
		}
		else {
			redirect(base_url(AUTH_CONTROLLER."/forgot-password"),"location");
			exit();
		}
	}
}
?>