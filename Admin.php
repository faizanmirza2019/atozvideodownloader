<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function __construct() {
		parent::__construct();
		if(!$this->session->has_userdata('vds_access') || $this->session->userdata('vds_access') != true || !$this->session->has_userdata('vds_admin_access') || $this->session->userdata('vds_admin_access') != true) {
			redirect(base_url(AUTH_CONTROLLER."/login"),"location");
			exit();
		}
		$this->load->model("AdminModel");
	}
	
	public function index() {
		redirect(base_url(ADMIN_CONTROLLER."/dashboard"),"location");
		exit();
	}
	
	public function dashboard() {
		$data = array();
		$data['settings'] = $this->DefaultModel->generalSettings();
		
		$cacheVar = "vds_admin_stats";
		if(!$stats = $this->cache->get($cacheVar)) {
			$stats = array();
			
			$record = array();
			$record['type'] = "All Time";
			$record['stats'] = $this->AdminModel->getStatsCount();
			array_push($stats,$record);
			
			$record = array();
			$record['type'] = "Last 7 Days";
			$startDate = date('Y-m-d',strtotime("-6 days"));
			$endDate = date("Y-m-d");
			$record['stats'] = $this->AdminModel->getStatsCount($startDate,$endDate);
			array_push($stats,$record);
			
			$record = array();
			$record['type'] = "Last 30 Days";
			$startDate = date('Y-m-d',strtotime("-29 days"));
			$endDate = date("Y-m-d");
			$record['stats'] = $this->AdminModel->getStatsCount($startDate,$endDate);
			array_push($stats,$record);
			
			$record = array();
			$record['type'] = "Today";
			$startDate = date('Y-m-d');
			$endDate = date("Y-m-d");
			$record['stats'] = $this->AdminModel->getStatsCount($startDate,$endDate);
			array_push($stats,$record);
			
			$this->cache->save($cacheVar,$stats,1000);
		}
		
		$data['stats'] = $stats;
		$this->load->view("admin/dashboard",$data);
	}
	
	public function general_settings() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$title = clearString($this->security->xss_clean($this->input->post("title")));
			$data['title'] = $title;
			$values['title'] = $title;
			if(empty($title)) {
				$error = true;
				$data['titleError'] = "Should not be empty";
			}
			
			$description = clearString($this->security->xss_clean($this->input->post("description")));
			$data['description'] = $description;
			$values['description'] = $description;
			if(empty($description)) {
				$error = true;
				$data['descriptionError'] = "Should not be empty";
			}
			
			$keywords = clearString($this->security->xss_clean($this->input->post("keywords")));
			$data['keywords'] = $keywords;
			$values['keywords'] = $keywords;
			if(empty($keywords)) {
				$error = true;
				$data['keywordsError'] = "Should not be empty";
			}
			
			$oldCoverImage = clearString($this->security->xss_clean($this->input->post("oldCoverImage")));
			$coverImage = $oldCoverImage;
			if(!$error) {
				if(!empty($_FILES['coverImage']['name'])) {
					$base = explode(".", strtolower(basename($_FILES["coverImage"]["name"])));
					$ext = end($base);
					$extArr = array("jpeg","jpg","png");
					if(in_array($ext,$extArr)) {
						$oldFile = "assets/images/".$oldCoverImage;
						if(file_exists($oldFile)) {
							unlink($oldFile);
						}
						$coverImage = "cover.".$ext;
						move_uploaded_file($_FILES["coverImage"]["tmp_name"], "assets/images/".$coverImage);
					} else {
						$data['coverImageError'] = "Only image types allowed";
						$error = true;
					}
				}
			}
			$data['coverImage'] = $coverImage;
			$values['coverImage'] = $coverImage;
			
			$oldBackgroundImage = clearString($this->security->xss_clean($this->input->post("oldBackgroundImage")));
			$backgroundImage = $oldBackgroundImage;
			if(!$error) {
				if(!empty($_FILES['backgroundImage']['name'])) {
					$base = explode(".", strtolower(basename($_FILES["backgroundImage"]["name"])));
					$ext = end($base);
					$extArr = array("jpeg","jpg","png");
					if(in_array($ext,$extArr)) {
						$oldFile = "assets/images/".$oldBackgroundImage;
						if(file_exists($oldFile)) {
							unlink($oldFile);
						}
						$backgroundImage = "bg-cover.".$ext;
						move_uploaded_file($_FILES["backgroundImage"]["tmp_name"], "assets/images/".$backgroundImage);
					} else {
						$data['backgroundImageError'] = "Only image types allowed";
						$error = true;
					}
				}
			}
			$data['backgroundImage'] = $backgroundImage;
			$values['backgroundImage'] = $backgroundImage;
			
			$oldLogo = clearString($this->security->xss_clean($this->input->post("oldLogo")));
			$logo = $oldLogo;
			if(!$error) {
				if(!empty($_FILES['logo']['name'])) {
					$base = explode(".", strtolower(basename($_FILES["logo"]["name"])));
					$ext = end($base);
					$extArr = array("jpeg","jpg","png");
					if(in_array($ext,$extArr)) {
						$oldFile = "assets/images/".$oldLogo;
						if(file_exists($oldFile)) {
							unlink($oldFile);
						}
						$logo = "logo.".$ext;
						move_uploaded_file($_FILES["logo"]["tmp_name"], "assets/images/".$logo);
					} else {
						$data['logoError'] = "Only image types allowed";
						$error = true;
					}
				}
			}
			$data['logo'] = $logo;
			$values['logo'] = $logo;
			
			$oldLogoLight = clearString($this->security->xss_clean($this->input->post("oldLogoLight")));
			$logoLight = $oldLogoLight;
			if(!$error) {
				if(!empty($_FILES['logoLight']['name'])) {
					$base = explode(".", strtolower(basename($_FILES["logoLight"]["name"])));
					$ext = end($base);
					$extArr = array("jpeg","jpg","png");
					if(in_array($ext,$extArr)) {
						$oldFile = "assets/images/".$oldLogoLight;
						if(file_exists($oldFile)) {
							unlink($oldFile);
						}
						$logoLight = "logoLight.".$ext;
						move_uploaded_file($_FILES["logoLight"]["tmp_name"], "assets/images/".$logoLight);
					} else {
						$data['logoLightError'] = "Only image types allowed";
						$error = true;
					}
				}
			}
			$data['logoLight'] = $logoLight;
			$values['logoLight'] = $logoLight;
			
			$oldFavicon = clearString($this->security->xss_clean($this->input->post("oldFavicon")));
			$favicon = $oldFavicon;
			if(!$error) {
				if(!empty($_FILES['favicon']['name'])) {
					$base = explode(".", strtolower(basename($_FILES["favicon"]["name"])));
					$ext = end($base);
					$extArr = array("jpeg","jpg","png","ico");
					if(in_array($ext,$extArr)) {
						$oldFile = "assets/images/".$oldFavicon;
						if(file_exists($oldFile)) {
							unlink($oldFile);
						}
						$favicon = "favicon.".$ext;
						move_uploaded_file($_FILES["favicon"]["tmp_name"], "assets/images/".$favicon);
					} else {
						$data['faviconError'] = "Only image types allowed";
						$error = true;
					}
				}
			}
			$data['favicon'] = $favicon;
			$values['favicon'] = $favicon;
			
			$allowDirectLink = clearString($this->security->xss_clean($this->input->post("allowDirectLink")));
			$allowDirectLink = ($allowDirectLink == "on" ? 1 : 0);
			$data['allowDirectLink'] = $allowDirectLink;
			$values['allowDirectLink'] = $allowDirectLink;
			
			$www = clearString($this->security->xss_clean($this->input->post("www")));
			$www = ($www == "on" ? 1 : 0);
			$data['www'] = $www;
			$values['www'] = $www;
			
			$https = clearString($this->security->xss_clean($this->input->post("https")));
			$https = ($https == "on" ? 1 : 0);
			$data['https'] = $https;
			$values['https'] = $https;
			
			if($error == false) {
				$this->AdminModel->updateGeneralSettings($values);
				$oldBaseUrl = base_url();
				$directoryName = preg_replace('{/$}', '', dirname($_SERVER['SCRIPT_NAME']))."/";
				$newBaseUrl = ($https == 1 ? "https://" : "http://").($www == 1 ? "www." : "").getDomain($_SERVER['SERVER_NAME']).$directoryName;
				if($oldBaseUrl != $newBaseUrl) {
					$configPath = APPPATH.'config/config.php';
					$configFile = file_get_contents($configPath);
					$configFile = str_replace($oldBaseUrl,$newBaseUrl,$configFile);
					file_put_contents($configPath,$configFile);
					redirect(base_url("logout"),"location");
					exit();
				}
				else {
					$settings = $this->DefaultModel->generalSettings();
					$data['settings'] = $settings;
				}
			}
		}
		else {
			$data['title'] = $settings['title'];
			$data['description'] = $settings['description'];
			$data['keywords'] = $settings['keywords'];
			$data['coverImage'] = $settings['coverImage'];
			$data['backgroundImage'] = $settings['backgroundImage'];
			$data['logo'] = $settings['logo'];
			$data['logoLight'] = $settings['logoLight'];
			$data['favicon'] = $settings['favicon'];
			$data['allowDirectLink'] = $settings['allowDirectLink'];
			$data['www'] = $settings['www'];
			$data['https'] = $settings['https'];
		}
		$data['error'] = $error;
		$this->load->view("admin/general_settings",$data);
	}
	
	public function sources() 
        {
            $data = array();
            $settings = $this->DefaultModel->generalSettings();
            
            $sources = get_all_sources();
            
            $data['settings'] = $settings;
            $error = false;
            /*$sources = $this->AdminModel->sources();*/
            $data['sources'] = $sources;
            $this->load->view("admin/sources",$data);
	}
	
	public function change_source_status() {
		if(isset($_POST['changeSourceStatus']) && $_POST['changeSourceStatus'] == 'changeSourceStatus') {
			$response = array();
			$action = clearString($this->security->xss_clean($this->input->post("action")));
			$name = clearString($this->security->xss_clean($this->input->post("name")));
			$id = clearString($this->security->xss_clean($this->input->post("id")));
			$status = ($action == "on" ? "1" : "0");
			$messageAction = ($action == "on" ? "enabled" : "disabled");
			//$this->AdminModel->updateSource(["status" => $status],$id,$name);
                        
                        update_source_info($id, ["status" => $status]);
                        
			$response['status'] = "success";
			$response['message'] = "Source ".$messageAction." successfully";
			echo json_encode($response);
		}
	}
	
	public function edit_source($id = NULL) 
        {
            
		if($id) 
                {
                    $data = array();
                    $lib_path = APPPATH . "libraries/" . ucfirst($id).".php";
                    
                    if(file_exists($lib_path))
                    {
                        $id_s = strtolower($id);
                        $this->load->library($id_s);
                        
                        //if(function_exists($this->{$id}->get_fields()))
                        if(method_exists($this->{strtolower($id_s)}, "get_fields"))
                        {
                            $data['fields'] = $this->{$id_s}->get_fields();
                        }
                        
                       
                        
                        $id = clearString($this->security->xss_clean($id));
                        
                        $data['id'] = $id;
                        $settings = $this->DefaultModel->generalSettings();
                        $data['settings'] = $settings;
                        $error = false;
                        if(isset($_POST['submit'])) 
                        {
                                $post_data = $this->input->post(NULL, TRUE); 

                                $name = clearString($this->security->xss_clean($this->input->post("name")));
                                $data['name'] = $name;

                                $website = clearString($this->security->xss_clean($this->input->post("website")));
                                $data['website'] = $website;

                                $linkCacheTime = clearString($this->security->xss_clean($this->input->post("linkCacheTime")));
                                $data['linkCacheTime'] = $linkCacheTime;
                                $values['linkCacheTime'] = $linkCacheTime;
                                if(!is_numeric($linkCacheTime)) {
                                        $error = true;
                                        $data['linkCacheTimeError'] = "Should be a numeric value";
                                }
                                else if($linkCacheTime < 0) {
                                        $error = true;
                                        $data['linkCacheTimeError'] = "Value should be greater than 0";
                                }

                                $oldIcon = clearString($this->security->xss_clean($this->input->post("oldIcon")));
                                $icon = $oldIcon;
                                if(!$error) {
                                        if(!empty($_FILES['icon']['name'])) {
                                                $base = explode(".", strtolower(basename($_FILES["icon"]["name"])));
                                                $ext = end($base);
                                                $extArr = array("jpeg","jpg","png","ico");
                                                if(in_array($ext,$extArr)) {
                                                        $oldFile = "assets/images/sources/".$oldIcon;
                                                        if(file_exists($oldFile)) {
                                                                unlink($oldFile);
                                                        }

                                                        $name_parts = explode(".", $name);

                                                        //$icon = $name.".".$ext;
                                                        //echo strtolower($name);exit;
                                                        
                                                        $icon = strtolower(get_final_domain_name($name_parts[0]) . ".".$ext);
                                                        
                                                        move_uploaded_file($_FILES["icon"]["tmp_name"], "assets/images/sources/".$icon);
                                                } else {
                                                        $data['iconError'] = "Only image types allowed";
                                                        $error = true;
                                                }
                                        }
                                }
                                $data['icon'] = $icon;
                                $values['icon'] = $icon;

                                $status = clearString($this->security->xss_clean($this->input->post("status")));
                                $status = ($status == "on" ? "1" : "0");
                                $data['status'] = $status;
                                $values['status'] = $status;
                                
                                if(!empty($post_data['fields']) && sizeof($post_data['fields']))
                                {
                                    foreach($post_data['fields'] as $index => $value)
                                    {
                                        $values[$index] = $value;
                                    }
                                }
                                
                               
                                /*if(!empty($post_data["fb_app_id"]))
                                {
                                    $values['fb_app_id'] = $post_data['fb_app_id'];
                                }
                                if(!empty($post_data["fb_app_secret"]))
                                {
                                    $values['fb_app_secret'] = $post_data['fb_app_secret'];
                                }

                                if(!empty($post_data["sc_api_key"]))
                                {
                                    $values['sc_api_key'] = $post_data['sc_api_key'];
                                }*/

                                if(!$error) {

                                       // $this->AdminModel->updateSource($values,$id,$name);
                                    update_source_info($id, $values);
                                }
                        }


                            //$source = $this->AdminModel->getSource($id);
                            $source = get_source_info($id);

                            if(is_array($source) && count($source) > 0) 
                            {
                               /* $data['name'] = $source['name'];
                                $data['website'] = $source['website'];
                                $data['icon'] = $source['icon'];
                                $data['linkCacheTime'] = $source['linkCacheTime'];
                                $data['status'] = $source['status'];
                            */
                                foreach($source as $index => $value)
                                {
                                    $data[$index] = $value;
                                }


                            }
                            else {
                                    redirect(base_url(ADMIN_CONTROLLER."/sources"),"location");
                                    exit();
                            }

                        $data['error'] = $error;
                        $this->load->view("admin/edit_source",$data);
                    }
                    else
                    {
                        redirect(base_url(ADMIN_CONTROLLER."/sources"),"location");
			exit();
                    }
                    
		}
		else {
			redirect(base_url(ADMIN_CONTROLLER."/sources"),"location");
			exit();
		}
	}
	
	public function update_sources_order() {
		if(isset($_POST['updateSourcesOrder']) && $_POST['updateSourcesOrder'] == 'updateSourcesOrder') {
			$order = $_POST['order'];
			for ($i = 0,$j = 1; $i < count($order); $i++,$j++)  {
				$id = $order[$i];
                                
                                $update_data = array("order" => $j);
                                update_source_info($id, $update_data);
				//$this->AdminModel->updateSourcesOrder($j,$id);
			}
		}
	}
	
	/*public function api_keys() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$facebookAppId = clearString($this->security->xss_clean($this->input->post("facebookAppId")));
			$data['facebookAppId'] = $facebookAppId;
			$values['facebookAppId'] = $facebookAppId;
			
			$facebookAppSecret = clearString($this->security->xss_clean($this->input->post("facebookAppSecret")));
			$data['facebookAppSecret'] = $facebookAppSecret;
			$values['facebookAppSecret'] = $facebookAppSecret;
			
			$soundcloud = clearString($this->security->xss_clean($this->input->post("soundcloud")));
			$data['soundcloud'] = $soundcloud;
			$values['soundcloud'] = $soundcloud;
			
			if(!$error) {
				$this->AdminModel->updateApiKeys($values);
			}
		}
		else {
			$apiKeys = $this->DefaultModel->apiKeys();
			$data['facebookAppId'] = $apiKeys['facebookAppId'];
			$data['facebookAppSecret'] = $apiKeys['facebookAppSecret'];
			$data['soundcloud'] = $apiKeys['soundcloud'];
		}
		$data['error'] = $error;
		$this->load->view("admin/api_keys",$data);
	}*/
	
	public function captcha_settings() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$siteKey = clearString($this->security->xss_clean($this->input->post("siteKey")));
			$data['siteKey'] = $siteKey;
			$values['siteKey'] = $siteKey;
			if(empty($siteKey)) {
				$error = true;
				$data['siteKeyError'] = "Site key should not be empty";
			}
			
			$secretKey = clearString($this->security->xss_clean($this->input->post("secretKey")));
			$data['secretKey'] = $secretKey;
			$values['secretKey'] = $secretKey;
			if(empty($secretKey)) {
				$error = true;
				$data['secretKeyError'] = "Secret key should not be empty";
			}
			
			$loginCaptcha = clearString($this->security->xss_clean($this->input->post("loginCaptcha")));
			$loginCaptcha = ($loginCaptcha == "on" ? 1 : 0);
			$data['loginCaptcha'] = $loginCaptcha;
			$values['loginCaptcha'] = $loginCaptcha;
			
			$forgotPasswordCaptcha = clearString($this->security->xss_clean($this->input->post("forgotPasswordCaptcha")));
			$forgotPasswordCaptcha = ($forgotPasswordCaptcha == "on" ? 1 : 0);
			$data['forgotPasswordCaptcha'] = $forgotPasswordCaptcha;
			$values['forgotPasswordCaptcha'] = $forgotPasswordCaptcha;
			
			$resetPasswordCaptcha = clearString($this->security->xss_clean($this->input->post("resetPasswordCaptcha")));
			$resetPasswordCaptcha = ($resetPasswordCaptcha == "on" ? 1 : 0);
			$data['resetPasswordCaptcha'] = $resetPasswordCaptcha;
			$values['resetPasswordCaptcha'] = $resetPasswordCaptcha;
			
			$contactCaptcha = clearString($this->security->xss_clean($this->input->post("contactCaptcha")));
			$contactCaptcha = ($contactCaptcha == "on" ? 1 : 0);
			$data['contactCaptcha'] = $contactCaptcha;
			$values['contactCaptcha'] = $contactCaptcha;
			
			$captchaShowFailedAttempts = clearString($this->security->xss_clean($this->input->post("captchaShowFailedAttempts")));
			$data['captchaShowFailedAttempts'] = $captchaShowFailedAttempts;
			$values['captchaShowFailedAttempts'] = $captchaShowFailedAttempts;
			if(!is_numeric($captchaShowFailedAttempts)) {
				$error = true;
				$data['captchaShowFailedAttemptsError'] = "Should be a numeric value";
			}
			
			if(!$error) {
				$this->AdminModel->updateCaptchaSettings($values);
			}
		}
		else {
			$captchaSettings = $this->DefaultModel->captchaSettings();
			$data['siteKey'] = $captchaSettings['siteKey'];
			$data['secretKey'] = $captchaSettings['secretKey'];
			$data['loginCaptcha'] = $captchaSettings['loginCaptcha'];
			$data['forgotPasswordCaptcha'] = $captchaSettings['forgotPasswordCaptcha'];
			$data['resetPasswordCaptcha'] = $captchaSettings['resetPasswordCaptcha'];
			$data['contactCaptcha'] = $captchaSettings['contactCaptcha'];
			$data['captchaShowFailedAttempts'] = $captchaSettings['captchaShowFailedAttempts'];
		}
		$data['error'] = $error;
		$this->load->view("admin/captcha_settings",$data);
	}
	
	public function change_password() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$oldPassword = $this->input->post("oldPassword");
			$data['oldPassword'] = $oldPassword;
			
			$newPassword = $this->input->post("newPassword");
			$data['newPassword'] = $newPassword;
			
			$confirmNewPassword = $this->input->post("confirmNewPassword");
			$data['confirmNewPassword'] = $confirmNewPassword;
			
			$userInfo = $this->session->userdata("vds_user");
			
			if(md5($oldPassword) != $userInfo['password']) {
				$error = true;
				$data['oldPasswordError'] = "Invalid old password";
			}
			else if(empty($newPassword)) {
				$error = true;
				$data['newPasswordError'] = "Should not be empty";
			}
			else if(empty($confirmNewPassword)) {
				$error = true;
				$data['confirmNewPasswordError'] = "Should not be empty";
			}
			else if($newPassword != $confirmNewPassword) {
				$error = true;
				$data['newPasswordError'] = "Passwords does not match";
			}
			else {
				$newPass = md5($newPassword);
				$this->AdminModel->updatePassword($userInfo['id'],$newPass);
				$userInfo['password'] = $newPass;
				$this->session->set_userdata("vds_user",$userInfo);
			}
		}
		$data['error'] = $error;
		$this->load->view("admin/change_password",$data);
	}
	
	public function analytics_settings() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$status = clearString($this->security->xss_clean($this->input->post("status")));
			$status = ($status == "on" ? 1 : 0);
			$data['status'] = $status;
			$values['status'] = $status;
			
			$code = htmlspecialchars(trim($this->input->post("code")),ENT_QUOTES, "UTF-8");
			$data['code'] = $code;			
			if($status == 1) {
				$values['code'] = $code;
				if(empty($code)) {
					$error = true;
					$data['codeError'] = "Should not be empty";
				}
			}
			
			if(!$error) {
				$this->AdminModel->updateAnalyticsSettings($values);
			}
			
		}
		else {
			$analyticsSettings = $this->DefaultModel->analyticsSettings();
			$data['status'] = $analyticsSettings['status'];
			$data['code'] = $analyticsSettings['code'];
		}
		$data['error'] = $error;
		$this->load->view("admin/analytics_settings",$data);
	}
	
	public function ads_settings() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$ad728x90Status = clearString($this->security->xss_clean($this->input->post("ad728x90Status")));
			$ad728x90Status = ($ad728x90Status == "on" ? 1 : 0);
			$data['ad728x90Status'] = $ad728x90Status;
			$values['ad728x90Status'] = $ad728x90Status;
			
			$ad728x90ResponsiveStatus = clearString($this->security->xss_clean($this->input->post("ad728x90ResponsiveStatus")));
			$ad728x90ResponsiveStatus = ($ad728x90ResponsiveStatus == "on" ? 1 : 0);
			$data['ad728x90ResponsiveStatus'] = $ad728x90ResponsiveStatus;
			$values['ad728x90ResponsiveStatus'] = $ad728x90ResponsiveStatus;
			
			$ad728x90 = htmlspecialchars(trim($this->input->post("ad728x90")),ENT_QUOTES, "UTF-8");
			$data['ad728x90'] = $ad728x90;	
			$values['ad728x90'] = $ad728x90;			
			if($ad728x90Status == 1) {
				if(empty($ad728x90)) {
					$error = true;
					$data['ad728x90Error'] = "Should not be empty";
				}
			}
			
			$ad250x250Status = clearString($this->security->xss_clean($this->input->post("ad250x250Status")));
			$ad250x250Status = ($ad250x250Status == "on" ? 1 : 0);
			$data['ad250x250Status'] = $ad250x250Status;
			$values['ad250x250Status'] = $ad250x250Status;
			
			$ad250x250ResponsiveStatus = clearString($this->security->xss_clean($this->input->post("ad250x250ResponsiveStatus")));
			$ad250x250ResponsiveStatus = ($ad250x250ResponsiveStatus == "on" ? 1 : 0);
			$data['ad250x250ResponsiveStatus'] = $ad250x250ResponsiveStatus;
			$values['ad250x250ResponsiveStatus'] = $ad250x250ResponsiveStatus;
			
			$ad250x250 = htmlspecialchars(trim($this->input->post("ad250x250")),ENT_QUOTES, "UTF-8");
			$data['ad250x250'] = $ad250x250;	
			$values['ad250x250'] = $ad250x250;			
			if($ad250x250Status == 1) {
				if(empty($ad250x250)) {
					$error = true;
					$data['ad250x250Error'] = "Should not be empty";
				}
			}
			
			
			$popAdStatus = clearString($this->security->xss_clean($this->input->post("popAdStatus")));
			$popAdStatus = ($popAdStatus == "on" ? 1 : 0);
			$data['popAdStatus'] = $popAdStatus;
			$values['popAdStatus'] = $popAdStatus;
			
			$popAd = htmlspecialchars(trim($this->input->post("popAd")),ENT_QUOTES, "UTF-8");
			$data['popAd'] = $popAd;	
			$values['popAd'] = $popAd;	

			$popAdFrequency = clearString($this->security->xss_clean($this->input->post("popAdFrequency")));
			$data['popAdFrequency'] = $popAdFrequency;
			$values['popAdFrequency'] = $popAdFrequency;
			
			if($popAdStatus == 1) {
				if(empty($popAd)) {
					$error = true;
					$data['popAdError'] = "Should not be empty";
				}
				if(!is_numeric($popAdFrequency)) {
					$error = true;
					$data['popAdFrequencyError'] = "Should be a numeric value";
				}
			}
			
			$displayOnHomePage = clearString($this->security->xss_clean($this->input->post("displayOnHomePage")));
			$displayOnHomePage = ($displayOnHomePage == "on" ? 1 : 0);
			$data['displayOnHomePage'] = $displayOnHomePage;
			$values['displayOnHomePage'] = $displayOnHomePage;
			
			$displayOnDynamicPages = clearString($this->security->xss_clean($this->input->post("displayOnDynamicPages")));
			$displayOnDynamicPages = ($displayOnDynamicPages == "on" ? 1 : 0);
			$data['displayOnDynamicPages'] = $displayOnDynamicPages;
			$values['displayOnDynamicPages'] = $displayOnDynamicPages;
			
			$displayOnContactPage = clearString($this->security->xss_clean($this->input->post("displayOnContactPage")));
			$displayOnContactPage = ($displayOnContactPage == "on" ? 1 : 0);
			$data['displayOnContactPage'] = $displayOnContactPage;
			$values['displayOnContactPage'] = $displayOnContactPage;
			
			if(!$error) {
				$this->AdminModel->updateAdsSettings($values);
			}
		}
		else {
			$adsSettings = $this->DefaultModel->adsSettings();
			$data['ad728x90Status'] = $adsSettings['ad728x90Status'];
			$data['ad728x90ResponsiveStatus'] = $adsSettings['ad728x90ResponsiveStatus'];
			$data['ad728x90'] = $adsSettings['ad728x90'];
			$data['ad250x250Status'] = $adsSettings['ad250x250Status'];
			$data['ad250x250ResponsiveStatus'] = $adsSettings['ad250x250ResponsiveStatus'];
			$data['ad250x250'] = $adsSettings['ad250x250'];
			$data['popAdStatus'] = $adsSettings['popAdStatus'];
			$data['popAd'] = $adsSettings['popAd'];
			$data['popAdFrequency'] = $adsSettings['popAdFrequency'];
			$data['displayOnHomePage'] = $adsSettings['displayOnHomePage'];
			$data['displayOnDynamicPages'] = $adsSettings['displayOnDynamicPages'];
			$data['displayOnContactPage'] = $adsSettings['displayOnContactPage'];
		}
		$data['error'] = $error;
		$this->load->view("admin/ads_settings",$data);
	}
	
	public function social_sharing() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$status = clearString($this->security->xss_clean($this->input->post("status")));
			$status = ($status == "on" ? 1 : 0);
			$data['status'] = $status;
			$values['status'] = $status;
				
			$facebookSharing = clearString($this->security->xss_clean($this->input->post("facebookSharing")));
			$facebookSharing = ($facebookSharing == "on" ? 1 : 0);
			$data['facebookSharing'] = $facebookSharing;
			$values['facebookSharing'] = $facebookSharing;
			
			$facebookPageName = clearString($this->security->xss_clean($this->input->post("facebookPageName")));
			$data['facebookPageName'] = $facebookPageName;
				
			$googleplusSharing = clearString($this->security->xss_clean($this->input->post("googleplusSharing")));
			$googleplusSharing = ($googleplusSharing == "on" ? 1 : 0);
			$data['googleplusSharing'] = $googleplusSharing;
			$values['googleplusSharing'] = $googleplusSharing;
			
			$googleplusPageId = clearString($this->security->xss_clean($this->input->post("googleplusPageId")));
			$data['googleplusPageId'] = $googleplusPageId;
			
			$twitterSharing = clearString($this->security->xss_clean($this->input->post("twitterSharing")));
			$twitterSharing = ($twitterSharing == "on" ? 1 : 0);
			$data['twitterSharing'] = $twitterSharing;
			$values['twitterSharing'] = $twitterSharing;
			
			$twitterTweetText = clearString($this->security->xss_clean($this->input->post("twitterTweetText")));
			$data['twitterTweetText'] = $twitterTweetText;
			
			$linkedinSharing = clearString($this->security->xss_clean($this->input->post("linkedinSharing")));
			$linkedinSharing = ($linkedinSharing == "on" ? 1 : 0);
			$data['linkedinSharing'] = $linkedinSharing;
			$values['linkedinSharing'] = $linkedinSharing;
				
			if($status == 1) {
				if($facebookSharing == 1) {
					$values['facebookPageName'] = $facebookPageName;
					if(empty($facebookPageName)) {
						$error = true;
						$data['facebookPageNameError'] = "Should not be empty";
					}
				}
				
				if($googleplusSharing == 1) {
					$values['googleplusPageId'] = $googleplusPageId;
					if(empty($googleplusPageId)) {
						$error = true;
						$data['googleplusPageIdError'] = "Should not be empty";
					}
				}
				
				
				if($twitterSharing == 1) {
					$values['twitterTweetText'] = $twitterTweetText;
					if(empty($twitterTweetText)) {
						$error = true;
						$data['twitterTweetTextError'] = "Should not be empty";
					}
				}
			}
			
			if(!$error) {
				$this->AdminModel->updateSocialSharing($values);
			} 
			
		}
		else {
			$socialSharing = $this->DefaultModel->socialSharing();
			$data['status'] = $socialSharing['status'];
			$data['facebookSharing'] = $socialSharing['facebookSharing'];
			$data['facebookPageName'] = $socialSharing['facebookPageName'];
			$data['googleplusSharing'] = $socialSharing['googleplusSharing'];
			$data['googleplusPageId'] = $socialSharing['googleplusPageId'];
			$data['twitterSharing'] = $socialSharing['twitterSharing'];
			$data['twitterTweetText'] = $socialSharing['twitterTweetText'];
			$data['linkedinSharing'] = $socialSharing['linkedinSharing'];
		}
		$data['error'] = $error;
		$this->load->view("admin/social_sharing",$data);
	}
	
	
	public function mail_settings() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$values = array();
			
			$smtpStatus = clearString($this->security->xss_clean($this->input->post("smtpStatus")));
			$smtpStatus = ($smtpStatus == "on" ? 1 : 0);
			$data['smtpStatus'] = $smtpStatus;
			$values['smtpStatus'] = $smtpStatus;
				
			$host = clearString($this->security->xss_clean($this->input->post("host")));
			$data['host'] = $host;
				
			$port = clearString($this->security->xss_clean($this->input->post("port")));
			$data['port'] = $port;
			
			$username = clearString($this->security->xss_clean($this->input->post("username")));
			$data['username'] = $username;
			
			$password = clearString($this->security->xss_clean($this->input->post("password")));
			$data['password'] = $password;
			
			if($smtpStatus == 1) {
				$values['host'] = $host;
				if(empty($host)) {
					$error = true;
					$data['hostError'] = "Should not be empty";
				}
				
				$values['port'] = $port;
				if(!is_numeric($port)) {
					$error = true;
					$data['portError'] = "Should be a numeric value";
				}
				
				$values['username'] = $username;
				if(empty($username)) {
					$error = true;
					$data['usernameError'] = "Should not be empty";
				}
				
				$values['password'] = $password;
				if(empty($password)) {
					$error = true;
					$data['passwordError'] = "Should not be empty";
				}
			}
			
			$contactEmail = clearString($this->security->xss_clean($this->input->post("contactEmail")));
			$data['contactEmail'] = $contactEmail;
			$values['contactEmail'] = $contactEmail;
			if(!validEmail($contactEmail)) {
				$error = true;
				$data['contactEmailError'] = "Invalid email address";
			}
			
			if(!$error) {
				$this->AdminModel->updateMailSettings($values);
			}
		}
		else {
			$mailSettings = $this->DefaultModel->mailSettings();
			$data['smtpStatus'] = $mailSettings['smtpStatus'];
			$data['host'] = $mailSettings['host'];
			$data['port'] = $mailSettings['port'];
			$data['username'] = $mailSettings['username'];
			$data['password'] = $mailSettings['password'];
			$data['contactEmail'] = $mailSettings['contactEmail'];
		}
		$data['error'] = $error;
		$this->load->view("admin/mail_settings",$data);
	}
	
	public function add_page() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$title = clearString($this->security->xss_clean($this->input->post("title")));
			$data['title'] = $title;
			$values['title'] = $title;
			if(empty($title)) {
				$error = true;
				$data['titleError'] = "Title should not be empty";
			}
			
			$permalink = clearString($this->security->xss_clean($this->input->post("permalink")));
			$permalink = (!empty($permalink) ? generatePermalink($permalink) : generatePermalink($title));
			$data['permalink'] = $permalink;
			$values['permalink'] = $permalink;
			if($this->AdminModel->countPagePermalink($permalink) > 0) {
				$error = true;
				$data['permalinkError'] = "Permalink already exists";
			}
			
			$description = clearString($this->security->xss_clean($this->input->post("description")));
			$data['description'] = $description;
			$values['description'] = $description;
			if(empty($description)) {
				$error = true;
				$data['descriptionError'] = "Description should not be empty";
			}
			
			$keywords = clearString($this->security->xss_clean($this->input->post("keywords")));
			$data['keywords'] = $keywords;
			$values['keywords'] = $keywords;
			if(empty($keywords)) {
				$error = true;
				$data['keywordsError'] = "Keywords field should not be empty";
			}
			
			$content = htmlspecialchars(trim($this->security->xss_clean($this->input->post("content"))),ENT_QUOTES, "UTF-8");
			$data['content'] = $content;
			$values['content'] = $content;
			if(empty($content)) {
				$error = true;
				$data['contentError'] = "Content field should not be empty";
			}
			
			$position = clearString($this->security->xss_clean($this->input->post("position")));
			$data['position'] = $position;
			$values['position'] = $position;
			
			$status = clearString($this->security->xss_clean($this->input->post("status")));
			$status = ($status == "on" ? 1 : 0);
			$data['status'] = $status;
			$values['status'] = $status;
			
			if($error == false) {
				$values['displayOrder'] = $this->AdminModel->getPageDisplayOrder();
				$this->AdminModel->addPage($values);
			}
		}
		$data['error'] = $error;
		$this->load->view("admin/add_page",$data);
	}
	
	public function pages() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$pages = $this->AdminModel->pages();
		$data['pages'] = $pages;
		$this->load->view("admin/pages",$data);
	}
	
	public function update_pages_order() {
		if(isset($_POST['updatePagesOrder']) && $_POST['updatePagesOrder'] == 'updatePagesOrder') {
			$order = $_POST['order'];
			for ($i = 0,$j = 1; $i < count($order); $i++,$j++)  {
				$id = $order[$i];
				$this->AdminModel->updatePagesOrder($j,$id);
			}
		}
	}
	
	public function delete_page() {
		if(isset($_POST['deletePage']) && $_POST['deletePage'] == 'deletePage') {
			$id = clearString($this->security->xss_clean($this->input->post("id")));
			$permalink = clearString($this->security->xss_clean($this->input->post("permalink")));
			$this->AdminModel->deletePage($id,$permalink);
		}
	}
	
	public function edit_page($id = NULL) {
		if(is_numeric($id)) {
			$id = clearString($this->security->xss_clean($id));
			$data = array();
			$data['id'] = $id;
			$settings = $this->DefaultModel->generalSettings();
			$data['settings'] = $settings;
			$error = false;
			if(isset($_POST['submit'])) {
				$title = clearString($this->security->xss_clean($this->input->post("title")));
				$data['title'] = $title;
				$values['title'] = $title;
				if(empty($title)) {
					$error = true;
					$data['titleError'] = "Title should not be empty";
				}
				
				$oldPermalink = clearString($this->security->xss_clean($this->input->post("oldPermalink")));
				$data['oldPermalink'] = $oldPermalink;
				$permalink = clearString($this->security->xss_clean($this->input->post("permalink")));
				$permalink = (!empty($permalink) ? generatePermalink($permalink) : generatePermalink($title));
				$data['permalink'] = $permalink;
				$values['permalink'] = $permalink;
				if($permalink != $oldPermalink) {
					if($this->AdminModel->countPagePermalink($permalink) > 0) {
						$error = true;
						$data['permalinkError'] = "Permalink already exists";
					}
				}
				
				$description = clearString($this->security->xss_clean($this->input->post("description")));
				$data['description'] = $description;
				$values['description'] = $description;
				if(empty($description)) {
					$error = true;
					$data['descriptionError'] = "Description should not be empty";
				}
				
				$keywords = clearString($this->security->xss_clean($this->input->post("keywords")));
				$data['keywords'] = $keywords;
				$values['keywords'] = $keywords;
				if(empty($keywords)) {
					$error = true;
					$data['keywordsError'] = "Keywords field should not be empty";
				}
				
				$content = htmlspecialchars(trim($this->security->xss_clean($this->input->post("content"))),ENT_QUOTES, "UTF-8");
				$data['content'] = $content;
				$values['content'] = $content;
				if(empty($content)) {
					$error = true;
					$data['contentError'] = "Content field should not be empty";
				}
				
				$position = clearString($this->security->xss_clean($this->input->post("position")));
				$data['position'] = $position;
				$values['position'] = $position;
				
				$status = clearString($this->security->xss_clean($this->input->post("status")));
				$status = ($status == "on" ? 1 : 0);
				$data['status'] = $status;
				$values['status'] = $status;
				
				if($error == false) {
					$data['oldPermalink'] = $permalink;
					$this->AdminModel->updatePage($values,$id,$oldPermalink);
				}
			}
			else {
				$page = $this->AdminModel->getPage($id);
				if(is_array($page) && count($page) > 0) {
					$data['title'] = $page['title'];
					$data['permalink'] = $page['permalink'];
					$data['description'] = $page['description'];
					$data['keywords'] = $page['keywords'];
					$data['content'] = $page['content'];
					$data['position'] = $page['position'];
					$data['status'] = $page['status'];
				}
				else {
					redirect(base_url(ADMIN_CONTROLLER."/pages"),"location");
					exit();
				}
			}
			$data['error'] = $error;
			$this->load->view("admin/edit_page",$data);
		}
		else {
			redirect(base_url(ADMIN_CONTROLLER."/pages"),"location");
			exit();
		}
	}
	
	public function languages() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$languages = $this->AdminModel->languages();
		$data['languages'] = $languages;
		
		$cacheVar = "vds_total_language_variables";
		if(!$totalLangVars = $this->cache->get($cacheVar)) {
			$this->load->helper("language");
			$totalLangVars = count(getLanguageRec());
			$this->cache->save($cacheVar,$totalLangVars,(86400*30));
		}
		$data['totalLangVars'] = $totalLangVars;
		
		$cacheVar = "vds_languages_variables_count";
		if(!$langVarsCount = $this->cache->get($cacheVar)) {
			$langVarsCount = array();
			foreach($languages as $language) {
				$id = $language['id'];
				$languageValues = file_get_contents("lang-files/".$id.".json");
				$languageValues = json_decode($languageValues,true);
				$langVarsCount[$id] = (is_array($languageValues) && count($languageValues) > 0 ? count(array_filter($languageValues)) : 0);
			}
			$this->cache->save($cacheVar,$langVarsCount,(86400*30));
		}
		
		$data['langVarsCount'] = $langVarsCount;
		$this->load->view("admin/languages",$data);
	}
	
	public function add_language() {
		$data = array();
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$error = false;
		if(isset($_POST['submit'])) {
			$name = clearString($this->security->xss_clean($this->input->post("name")));
			$data['name'] = $name;
			$values['name'] = $name;
			if(empty($name)) {
				$error = true;
				$data['nameError'] = "Name should not be empty";
			}
			
			$code = clearString($this->security->xss_clean($this->input->post("code")));
			$data['code'] = $code;
			$values['code'] = $code;
			if(empty($code)) {
				$error = true;
				$data['codeError'] = "Code should not be empty";
			}
			else if($this->AdminModel->countLanguageCode($code) > 0) {
				$error = true;
				$data['codeError'] = "Code already exist";
			}
			
			$flag = clearString($this->security->xss_clean($this->input->post("flag")));
			$data['flag'] = $flag;
			$values['flag'] = $flag;
			if(empty($flag)) {
				$error = true;
				$data['flagError'] = "Please select a value";
			}
			
			if($error == false) {
				$values['displayOrder'] = $this->AdminModel->getLanguageDisplayOrder();
				$id = $this->AdminModel->addLanguage($values);
				$file = fopen("lang-files/".$id.".json", "w");
				fclose($file);
			}
		}
		$data['error'] = $error;
		$countries = json_decode(file_get_contents("assets/countries/names.json"),true);
		$data['countries'] = $countries;
		$this->load->view("admin/add_language",$data);
	}
	
	public function edit_language($id = null) {
		if(is_numeric($id)) {
			$id = clearString($this->security->xss_clean($id));
			$data = array();
			$settings = $this->DefaultModel->generalSettings();
			$data['settings'] = $settings;
			$data['id'] = $id;
			$error = false;
			if(isset($_POST['submit'])) {
				$name = clearString($this->security->xss_clean($this->input->post("name")));
				$data['name'] = $name;
				$values['name'] = $name;
				if(empty($name)) {
					$error = true;
					$data['nameError'] = "Name should not be empty";
				}
				
				$oldCode = clearString($this->security->xss_clean($this->input->post("oldCode")));
				$data['oldCode'] = $oldCode;
				$code = clearString($this->security->xss_clean($this->input->post("code")));
				$data['code'] = $code;
				$values['code'] = $code;
				if($code != $oldCode) {
					if(empty($code)) {
						$error = true;
						$data['codeError'] = "Code should not be empty";
					}
					else if($this->AdminModel->countLanguageCode($code) > 0) {
						$error = true;
						$data['codeError'] = "Language code already exists";
					}
				}
				
				$flag = clearString($this->security->xss_clean($this->input->post("flag")));
				$data['flag'] = $flag;
				$values['flag'] = $flag;
				
				$status = clearString($this->security->xss_clean($this->input->post("status")));
				$status = ($status == "on" ? 1 : 0);
				$data['status'] = $status;
				$values['status'] = $status;
				
				if($error == false) {
					$data['oldCode'] = $code;
					$this->AdminModel->updateLanguage($values,$id);
				}
			}
			else {
				$language = $this->AdminModel->getLanguage($id);
				if(is_array($language) && count($language) > 0) {
					$data['name'] = $language['name'];
					$data['code'] = $language['code'];
					$data['flag'] = $language['flag'];
					$data['status'] = $language['status'];
				}
				else {
					redirect(base_url(ADMIN_CONTROLLER."/languages"),"location");
					exit();
				}
			}
			$data['error'] = $error;
			$countries = json_decode(file_get_contents("assets/countries/names.json"),true);
			$data['countries'] = $countries;
			$this->load->view("admin/edit_language",$data);
		}
		else {
			redirect(base_url(ADMIN_CONTROLLER."/languages"),"location");
			exit();
		}
	}
	
	public function update_languages_order() {
		if(isset($_POST['updateLanguagesOrder']) && $_POST['updateLanguagesOrder'] == 'updateLanguagesOrder') {
			$order = $_POST['order'];
			for ($i = 0,$j = 1; $i < count($order); $i++,$j++)  {
				$id = $order[$i];
				$this->AdminModel->updateLanguagesOrder($j,$id);
			}
		}
	}
	
	public function edit_language_values($id = null) {
		if(is_numeric($id)) {
			$id = clearString($this->security->xss_clean($id));
			$language = $this->AdminModel->getLanguage($id);
			if(is_array($language) && count($language) > 0) {
				if(file_exists("lang-files/".$id.".json")) {
					$data = array();
					$data['settings'] = $this->DefaultModel->generalSettings();
					$data['id'] = $id;
					$data['language'] = $language;
					$this->load->helper("language");
					$data['languageRec'] = getLanguageRec();
					$error = false;
					$data['error'] = $error;
					if(isset($_POST['submit'])) {
						array_pop($_POST);
						$languageValues = $_POST;
						file_put_contents("lang-files/".$id.".json",json_encode($languageValues));
						$_POST['submit'] = "submit";
						$this->AdminModel->deleteCacheVar("vds_languages_variables_count");
					}
					else {
						$languageValues = file_get_contents("lang-files/".$id.".json");
						$languageValues = json_decode($languageValues,true);
					}
					$data['languageValues'] = $languageValues;
					$this->load->view("admin/edit_language_values",$data);
				}
				else {
					redirect(base_url(ADMIN_CONTROLLER."/languages"),"location");
					exit();
				}
			}
			else {
				redirect(base_url(ADMIN_CONTROLLER."/languages"),"location");
				exit();
			}
		}
	}
	
	public function delete_language() {
		if(isset($_POST['deleteLanguage']) && $_POST['deleteLanguage'] == 'deleteLanguage') {
			$status = clearString($this->security->xss_clean($this->input->post("status")));
			if($status == 0 || ($status == 1 && $this->AdminModel->publishedlanguagesCount() > 1)) {
				$id = clearString($this->security->xss_clean($this->input->post("id")));
				$this->AdminModel->deleteLanguge($id);
				$file = "lang-files/".$id.".json";
				if(file_exists($file)) {
					unlink($file);
				}
				echo json_encode(["status" => "success"]);
			}
			else {
				echo json_encode(["status" => "error", "message" => "Could not delete that langauge"]);
			}
		}
	}
	
	public function clear_cache() {
		$this->cache->clean();
		redirect(base_url(ADMIN_CONTROLLER),"location");
		exit();
	}
}
?>