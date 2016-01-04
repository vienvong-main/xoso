<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Score extends CI_Controller {
    private $data = array();

    public function index($country = ""){
        switch($country){
            case "anh":
                $this->data['link_score']  = "http://android.livescore.com/#/soccer/league/england/";
                $this->data['title_score']  = "Kết quả bóng đá Anh";
                break;
            case "y":
                $this->data['link_score'] = "http://android.livescore.com/#/soccer/league/italy/";
                $this->data['title_score']  = "Kết quả bóng đá Ý";
                break;
            case "tbn":
                $this->data['link_score'] = "http://android.livescore.com/#/soccer/league/spain/";
                $this->data['title_score']  = "Kết quả bóng đá TBN";
                break;
            case "ca":
                $this->data['link_score'] = "http://android.livescore.com/#/soccer/league/eurocups/";
                $this->data['title_score']  = "Kết quả bóng đá Châu Âu";
                break;
            case "duc":
                $this->data['link_score'] = "http://android.livescore.com/#/soccer/league/germany/";
                $this->data['title_score']  = "Kết quả bóng đá Đức";
                break;
            case "phap":
                $this->data['link_score'] = "http://android.livescore.com/#/soccer/league/france/";
                $this->data['title_score']  = "Kết quả bóng đá Pháp";
                break;
            default:
                $this->data['link_score']  = "http://android.livescore.com/";
                $this->data['title_score']  = "Kết quả bóng đá";
                break;
        }
        $this->data['content_view'] = 'score';
        $this->load->view('wrapper', $this->data);
    }
}
