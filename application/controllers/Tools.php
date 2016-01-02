<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tools extends CI_Controller {
    private $data = array();

    public function index(){
        $this->thongKe();
    }

    public function thongKe(){

    }

    public function calender(){
        $this->data = array();

        $this->data['content_view'] = 'tools/calender';
        $this->load->view('wrapper', $this->data);
    }

    public function playXs($zone){

        switch($zone){
            case 'mb':
                break;
            case 'mt':
                break;
            case 'mn':
                break;
        }

        $this->data['content_view'] = 'tools/playXs';
        $this->load->view('wrapper', $this->data);
    }
}
