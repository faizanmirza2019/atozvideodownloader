<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller {
	public function index($permalink) {
		$permalink = clearString($this->security->xss_clean($permalink));
		$pageSingle = $this->DefaultModel->getPageByPermalink($permalink);
		if(is_array($pageSingle) && count($pageSingle) > 0) {
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
			//$data['facebookAppId'] = $this->DefaultModel->getApiKey('facebookAppId');
		
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
			
			$data['page'] = $pageSingle;
			
			$this->load->view("page",$data);
		}
		else {
			redirect(base_url(),"location");
			exit();
		}
	}
}
?>