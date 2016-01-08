<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Result extends CI_Controller {
    private $data = array();

    public function index(){
        $this->data['content_view'] = 'result';
        $this->load->view('wrapper', $this ->data);
    }

    public function search(){
        var_dump($this->input->post('total'));
    }
}
