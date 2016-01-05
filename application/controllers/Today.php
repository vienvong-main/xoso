<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Today extends CI_Controller {
    private $data = array();

    public function index(){
        $this->data['content_view'] = 'today';
        $this->load->view('wrapper', $this->data);
    }
}
