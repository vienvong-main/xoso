<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Today extends CI_Controller {

    public function index(){
        $data = array();

        $data['content_view'] = 'today';
        $this->load->view('wrapper', $data);
    }
}
