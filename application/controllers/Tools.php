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
                $this->data['region'] = 'mb';
                break;
            case 'mt':
                $this->data['region'] = 'mt';
                break;
            case 'mn':
                $this->data['region'] = 'mn';
                break;
            default:
                $this->data['region'] = 'mb';
                break;
        }

        $this->data['content_view'] = 'tools/playXs';
        $this->load->view('wrapper', $this->data);
    }
}
