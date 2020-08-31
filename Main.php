<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
    
        
	public function index() 
        {
            
		$data = array();
		
                $data["sources"] = get_all_sources();
                
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
		
		$settings = $this->DefaultModel->generalSettings();
		$data['settings'] = $settings;
		$cover = array();
		$coverPath = base_url("assets/images/".$settings['coverImage']);
		$cover['path'] = $coverPath;
		$cover['porperties'] = getimagesize($coverPath);
		$data['cover'] = $cover;
		
		$data['analytics'] = $this->DefaultModel->analyticsSettings();
		$data['adsSettings'] = $this->DefaultModel->adsSettings();
		$data['socialSharing'] = $this->DefaultModel->socialSharing();
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
		
		/*$sources = $this->DefaultModel->sources();
		$data['sources'] = $sources;*/
		
		$error = false;
		if(isset($_GET['url']) && !empty($_GET['url'])) 
                {
                    
			$isMobile = false;
			$url = clearString($this->security->xss_clean($_GET['url']));
			$data['url'] = $url;
			if(filter_var($url, FILTER_VALIDATE_URL) == false) 
                        {
				$url = "http://" . $url;
			}
                        
                        $domain = strtolower(getDomainName($url));
				if($domain == "youtu") {
					$domain = "youtube";
					$urlPartS = parse_url($url);
					$yId = explode("/",$urlPartS['path']);
					$yId = end($yId);
					$url = "https://www.youtube.com/watch?v=".$yId;
				}
				
                               /* $source = $this->DefaultModel->getSource($domain);
				
                                if($source) 
                                {*/
					$data['source'] = $domain;
					$cacheVar = "mediaData-".md5($url);
					if(!$mediaData = $this->cache->get($cacheVar)) 
                                        {
                                            $mediaData = array();
                                            $mediaData['found'] = 0;

                                            $domain = get_final_domain_name($domain);
                                            if(file_exists(APPPATH . "libraries/" . ucfirst($domain).".php")) 
                                            {                           
                                                $sm_domain = strtolower($domain);
                                                $this->load->library($sm_domain);
                                                $source_info = get_source_info($domain);
                                                if(!empty($source_info['status']) && $source_info['status'] == 1)
                                                {
                                                    $mediaData = $this->{$sm_domain}->getMediaInfo($url);                                                      
                                                    
                                                    if(!empty($mediaData['recall']) && $mediaData['recall'] == 1)
                                                    {
                                                        if(method_exists($this->{$sm_domain}, "FixDecryption"))
                                                        {

                                                            $this->{$sm_domain}->FixDecryption($mediaData['signature']);
                                                            $mediaData = $this->{$sm_domain}->getMediaInfo($url);
                                                        }
                                                    }
                                                    
                                                    if($mediaData['found'] == 1 && !empty($source_info['linkCacheTime'])) 
                                                    {
                                                        $this->cache->save($cacheVar,$mediaData, $source_info['linkCacheTime']);
                                                    }
                                                }                                                                                                                                                          
                                            }
						
						
					}
					
					if($mediaData['found'] == 1) 
                                        {
                                            $cover = array();
                                            $coverPath = $mediaData['image'];
                                            $cover['path'] = $coverPath;
                                            $cover['porperties'] = getimagesize($coverPath);
                                            $mediaData['cover'] = $cover;

                                            $this->countDownloadStats();

                                            $this->load->library('user_agent');
                                            if($this->agent->is_mobile()) {
                                                    $isMobile = true;
                                            }

                                            if($domain == "dailymotion") {
                                                    if($settings['allowDirectLink'] == 1) {
                                                            $mediaData['scriptFiles'] .= '<script type="text/javascript" src="'.base_url("assets/js/dailymotion-script.js").'"></script>';
                                                    }
                                            }

                                            $data['mediaData'] = $mediaData;
					}
					else {
						$error = true;
						$data['errorType'] = "warning";
						$data['errorMsg'] = showLanguageVar($languageValues,"video_not_found_error");
					}
				/*}
				else {
					$error = true;
					$data['errorType'] = "warning";
					$data['errorMsg'] = showLanguageVar($languageValues,"source_not_found_error");
				}*/
                        
			/*else {
				$error = true;
				$data['errorType'] = "danger";
				$data['errorMsg'] = showLanguageVar($languageValues,"invalid_url_error",true);
			}*/
                        
			$data['isMobile'] = $isMobile;
		}
                
                
                $source_info = get_source_info("facebook");
                if(!empty($source_info['fb_app_id']))
                {
                    $data['facebookAppId'] = $source_info['fb_app_id'];                    
                }
                //$data['facebookAppId'] =  (defined("FACEBOOK_APP_ID")) ? FACEBOOK_APP_ID : "";
                                
		$data['error'] = $error;
		$this->load->view('main',$data);
	}
	
	public function get_video_data_ajax() 
        {
            if($this->input->is_ajax_request()) 
            {
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
                    $languageValues = json_decode(file_get_contents("lang-files/".$language['id'].".json"),true);
                    $data['languageValues'] = $languageValues;
                    /*** Language End ***/

                    $settings = $this->DefaultModel->generalSettings();
                    $data['settings'] = $settings;
                    $title = $settings['title'];
                    $data['adsSettings'] = $this->DefaultModel->adsSettings();

                    //$sources = $this->DefaultModel->sources();
                    $error = false;
                    $found = 0;

                    if(isset($_POST['url']) && !empty($_POST['url'])) {

                            $isMobile = false;
                            $url = clearString($this->security->xss_clean($this->input->post("url")));
                            $data['url'] = $url;
                            if(filter_var($url, FILTER_VALIDATE_URL) == FALSE) 
                            {
                                $url = "https://" . $url;                                    
                            }
                            
                            $domain = strtolower(getDomainName($url));

                                    if($domain == "youtu") {
                                            $domain = "youtube";
                                            $urlPartS = parse_url($url);
                                            $yId = explode("/",$urlPartS['path']);
                                            $yId = end($yId);
                                            $url = "https://www.youtube.com/watch?v=".$yId;
                                    }
                                    
                                   /* $source = $this->DefaultModel->getSource($domain);
                                    if($source) 
                                    {*/

                                            $data['source'] = $domain;
                                            $cacheVar = "mediaData-".md5($url);

                                            if(!$mediaData = $this->cache->get($cacheVar)) 
                                            {
                                            
                                                $mediaData = array();
                                                $mediaData['found'] = 0;                                                

                                                $domain = get_final_domain_name($domain);
                                                
                                                if(file_exists(APPPATH . "libraries/" . ucfirst($domain).".php")) 
                                                {                     
                                                    $sm_domain = strtolower($domain);
                                                    $this->load->library($sm_domain);                              
                                                    $source_info = get_source_info($domain);
                                                    if(!empty($source_info['status']) && $source_info['status'] == 1)
                                                    {
                                                        $mediaData = $this->{$sm_domain}->getMediaInfo($url);   
                                                       
                                                        if(!empty($mediaData['recall']) && $mediaData['recall'] == 1)
                                                        {
                                                            if(method_exists($this->{$sm_domain}, "FixDecryption"))
                                                            {
                                                                 
                                                                $this->{$sm_domain}->FixDecryption($mediaData['signature']);
                                                                $mediaData = $this->{$sm_domain}->getMediaInfo($url);
                                                            }
                                                        }
                                                        
                                                        if($mediaData['found'] == 1) {
                                                            $this->cache->save($cacheVar,$mediaData, $source_info["linkCacheTime"]);
                                                        }
                                                    }
                                                }

                                                if($mediaData['found'] == 1 && !empty($source_info['linkCacheTime'])) 
                                                {
                                                    $this->cache->save($cacheVar,$mediaData, $source_info['linkCacheTime']);
                                                }
                                            }

                                            if($mediaData['found'] == 1) 
                                            {

                                                    $found = 1;
                                                    $title = "Download ".$mediaData['title']." From ". ucfirst($data['source'])." - ".$settings['title'];

                                                    $this->countDownloadStats();

                                                    $this->load->library('user_agent');
                                                    if($this->agent->is_mobile()) {
                                                            $isMobile = true;
                                                    }

                                                    if($domain == "dailymotion") {
                                                            if($settings['allowDirectLink'] == 1) {
                                                                    $mediaData['scriptFiles'] .= '<script type="text/javascript" src="'.base_url("assets/js/dailymotion-script.js").'"></script>';
                                                            }
                                                    }

                                                    $data['mediaData'] = $mediaData;
                                            }
                                            else {
                                                    $error = true;
                                                    $data['errorType'] = "warning";
                                                    $data['errorMsg'] = showLanguageVar($languageValues,"video_not_found_error");
                                            }
                                   /* }
                                    else {
                                            $error = true;
                                            $data['errorType'] = "warning";
                                            $data['errorMsg'] = showLanguageVar($languageValues,"source_not_found_error");
                                    }*/
                            
                            /*else {
                                    $error = true;
                                    $data['errorType'] = "danger";
                                    $data['errorMsg'] = showLanguageVar($languageValues,"invalid_url_error",true);
                            }*/
                            $data['isMobile'] = $isMobile;
                    }
                    $data['error'] = $error;
                    $pageContent = $this->load->view("ajax-page-content",$data,true);
                    $scriptContent = $this->load->view("ajax-page-script",$data,true);
                    echo json_encode ([
                            "pageContent" => $pageContent,
                            "scriptContent" => $scriptContent,
                            "title" => $title,
                            "found" => $found,
                            "url" => $url
                    ]);
            }
            else 
            {
                    $this->output->set_status_header('403');
                    exit();
            }
	}
	
	public function select_language($id = null) {
		if(is_numeric($id)) {
			$language = $this->DefaultModel->getLanguageById($id);
			if(is_array($language) && count($language) > 0) {
				$this->load->library('encryption');
				$this->input->set_cookie("vds_language",$this->encryption->encrypt(base64_encode(json_encode($language))),63072000);
				$this->load->library('user_agent');
				$referrer = $this->agent->referrer();
				if(getDomain($referrer) == getDomain(base_url())) {
					redirect($referrer,"location");
				}
				else {
					redirect(base_url(),"location");
				}
			}
			else {
				redirect(base_url(),"location");
			}
		}
		else {
			redirect(base_url(),"location");
		}
	}
			
	/*** Count Downloads Stats ***/
	protected function countDownloadStats() {
		$date = date("Y-m-d");
		$cacheVar = "vds_stats_entry_".$date;
		$statsEntry = $this->cache->get($cacheVar);
		if(!is_null($statsEntry) && is_array($statsEntry) && count($statsEntry) > 0) {
			$statsEntry['downloads'] = $statsEntry['downloads'] + 1;
			$this->DefaultModel->updateStatisticsEntry($statsEntry,$date);
			$this->cache->save($cacheVar,$statsEntry,86400);
		}
	}
	
	/*** Get Sizes ***/
	public function get_stream_sizes() {
		if(isset($_POST['getSizes']) && $_POST['getSizes'] == 'getSizes') {
			$url = clearString($this->security->xss_clean($this->input->post("url")));
			$cacheVar = "mediaSizes-".md5($url);
			if(!$sizes = $this->cache->get($cacheVar)) {
				$sizes = array();
				$formatsArr = json_decode($_POST['formatsArr'],true);
				foreach($formatsArr as $row) {
					$headers = get_headers($row['linkUrl'],1);
					if(is_array($headers) && count($headers) > 0) {
						$size = $headers['Content-Length'];
						if(is_array($size)) {
							foreach($size as $value) {
								if($value != 0) {
									$size = $value;
									break;
								}
							}
						}
						$sizes[$row['formatId']] = formatSizeUnits($size);
					}
					else {
						$sizes[$row['formatId']] = "unknown";
					}
				}
				$this->cache->save($cacheVar,$sizes,(86400*60));
			}
			/*if(count(array_unique($sizes)) === 1 && end($sizes) === '0 bytes') 
                        {
				$this->load->helper('yt_func_helper');
				FixDecryption();
				$this->cache->clean();
			}*/
			echo json_encode($sizes);
		}
	}
	
	/*** Download Function ***/
	public function download() {
		$source = clearString($this->security->xss_clean($this->input->get("source")));
		$id = clearString($this->security->xss_clean($this->input->get("id")));
		$format = clearString($this->security->xss_clean($this->input->get("format")));
		$url = $this->input->get("url");
		$title = clearString($this->security->xss_clean($this->input->get("title")));
		$title = html_entity_decode($title,ENT_QUOTES,"UTF-8");
		
		$cacheVar = "downloadInfo_".$source."_".$id."_".$format;
		if(!$info = $this->cache->get($cacheVar)) {
			$headers = get_headers($url,1);
			
			$mime = $headers['Content-Type'];
			if(is_array($mime)) {
				foreach($mime as $value) {
					if($value != "text/html") {
						$mime = $value;
						break;
					}
				}
			}
			
			$size = $headers['Content-Length'];
			if(is_array($size)) {
				foreach($size as $value) {
					if($value != 0) {
						$size = $value;
						break;
					}
				}
			}
			
			$info = array();
			$info['size'] = $size;
			$info['mime'] = $mime;
			
			$this->cache->save($cacheVar,$info,(86400*60));
		}
		
		header('Content-Type: "'.$info['mime'].'"');
		header('Content-Disposition: attachment; filename="'.$title.'"');
		header("Content-Transfer-Encoding: binary");
		header('Expires: 0');
		header('Content-Length: '.$info['size']);
		header('Pragma: no-cache');

		if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}

		readfile($url);
		exit;
	}
}
?>