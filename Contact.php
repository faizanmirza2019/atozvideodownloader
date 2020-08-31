<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends CI_Controller {
	public function index() {
		$data = array();
		
		/*** Language ***/
		$this->load->library('encryption');
		if($this->input->cookie("vds_language",true) == NULL) {
			$language = $this->DefaultModel->getDefaultLanguage();
			$this->input->set_cookie("vds_language",$this->encryption->encrypt(base64_encode(json_encode($language))),63072000);
		}
		else {
			$languageArr = $this->input->cookie("vds_language",true);
			$languageArr = $this->encryption->decrypt($languageArr);
			$languageArr = json_decode(base64_decode($languageArr),true);
			if(is_array($languageArr) && count($languageArr) > 0) {
				$language = $this->DefaultModel->getLanguageById($languageArr['id']);
				if(!is_array($language) || count($language) == 0) {
					$language = $this->DefaultModel->getDefaultLanguage();
					$this->input->set_cookie("vds_language",$this->encryption->encrypt(base64_encode(json_encode($language))),63072000);
				}
			}
			else {
				$language = $this->DefaultModel->getDefaultLanguage();
				$this->input->set_cookie("vds_language",$this->encryption->encrypt(base64_encode(json_encode($language))),63072000);
			}
		}
		$data['language'] = $language;
		$languageValues = json_decode(file_get_contents("lang-files/".$language['id'].".json"),true);
		$data['languageValues'] = $languageValues;
		/*** Language End ***/
		
		$data['sHeader'] = true;
		
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$cover = array();
		$coverPath = base_url("assets/images/".$settings['coverImage']);
		$cover['path'] = $coverPath;
		$cover['porperties'] = getimagesize($coverPath);
		$data['cover'] = $cover;
		
                $source_info = get_source_info("facebook");
                if(!empty($source_info['fb_app_id']))
                {
                    $data['facebookAppId'] = $source_info['fb_app_id'];                    
                }
	
		$data['analytics'] = $this->DefaultModel->analyticsSettings();
		$data['adsSettings'] = $this->DefaultModel->adsSettings();
		$data['languages'] = $this->DefaultModel->languages();
		
		$pages = $this->DefaultModel->pages();
		$headerPages = array();
		$footerPages = array();
		foreach($pages as $page) {
			$position = $page['position'];
			if($position == 1) {
				array_push($headerPages,$page);
			}
			else if($position == 2) {
				array_push($footerPages,$page);
			}
			else {
				array_push($headerPages,$page);
				array_push($footerPages,$page);
			}
		}
		$data['headerPages'] = $headerPages;
		$data['footerPages'] = $footerPages;
		
		$captchaSettings = $this->DefaultModel->captchaSettings();
		$captchaCheck = false;
		if($captchaSettings['contactCaptcha'] == 1) {
			$captchaCheck = true;
			$data['captchaSettings'] = $captchaSettings;
		}
		
		$error = false;
		if(isset($_POST['submit'])) {
			$name = clearString($this->security->xss_clean($this->input->post("name")));
			$data['name'] = $name;
			$email = clearString($this->security->xss_clean($this->input->post("email")));
			$data['email'] = $email;
			$subject = clearString($this->security->xss_clean($this->input->post("subject")));
			$data['subject'] = $subject;
			$message = clearString($this->security->xss_clean($this->input->post("message")));
			$data['message'] = $message;
			
			if($captchaCheck == true && isset($_POST['g-recaptcha-response'])) {
				$gCaptchaSecret = $captchaSettings['secretKey'];
				$gCaptchaResponse = $this->input->post("g-recaptcha-response");
				$remoteIp = $this->input->ip_address();
				$url = "https://www.google.com/recaptcha/api/siteverify?".http_build_query(['secret' => $gCaptchaSecret,'remoteip' => $remoteIp,'response' => $gCaptchaResponse]);
				$response = getRemoteContents($url);
				$response = json_decode($response,true);
				if(!isset($response['success']) || $response['success'] != true) {
					$error = true;
					$data['captchaError'] = showLanguageVar($languageValues,"captcha_error");
				}
			}
			
			if(!$error) {
				if(!isAlphaSpaces($name)) {
					$error = true;
					$data['nameError'] = showLanguageVar($languageValues,"name_error");
				}
				
				if(!validEmail($email)) {
					$error = true;
					$data['emailError'] = showLanguageVar($languageValues,"email_error");
				}
				
				if(empty($subject)) {
					$error = true;
					$data['subjectError'] = showLanguageVar($languageValues,"subject_error");
				}
				
				if(empty($message)) {
					$error = true;
					$data['messageError'] = showLanguageVar($languageValues,"message_error");
				}
				
				if(!$error) {
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
					
					$message = nl2br($message);
					$message .= '<p><strong>Sent Via <a href="'.base_url().'">'.$host.'</a></strong></p>';
					
					$this->email->from($mailSettings['contactEmail'], $name);
					$this->email->reply_to($email, $name);
					$this->email->to($mailSettings['contactEmail']);
					$this->email->subject($subject." : ".$host);
					$this->email->message($message);
					$this->email->send();
				}
			}
		}
		
		$data['error'] = $error;
		$data['captchaCheck'] = $captchaCheck;
		$this->load->view("contact",$data);
	}
}
?>