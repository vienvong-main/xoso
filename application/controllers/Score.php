<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Score extends CI_Controller {
    private $data = array();

    public function index(){
        $this->data['content_view'] = 'score';
        $this->load->view('wrapper', $this->data);
    }
}
