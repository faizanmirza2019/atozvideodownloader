<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotFound extends CI_Controller {
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
		
		$data['settings'] = $this->DefaultModel->generalSettings();
		$data['analytics'] = $this->DefaultModel->analyticsSettings();
		
		$this->output->set_status_header('404');
		$this->load->view("404",$data);
	}
}
?>