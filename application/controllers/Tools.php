<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tools extends CI_Controller {

    public function index(){
        $this->thongKe();
    }

    public function thongKe(){

    }

    public function calender(){
        $data = array();

        $data['content_view'] = 'tools/calender';
        $this->load->view('wrapper', $data);
    }

    public function quayThu(){

    }
}
