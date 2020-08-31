<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends CI_Controller {
	public function index() {
		$this->session->sess_destroy();
		delete_cookie('vds_ud');
		redirect(base_url(),"location");
		exit();
	}
}
?>