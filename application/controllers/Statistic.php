<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistic extends CI_Controller {
	/**
	 * Constructor 
	 *
	 */
	public function __construct() {
		parent::__construct();
	}
	// --------------------------------------------------------
	/**
	 * Statistic 
	 */
	public function index() {
		$init['enddate'] = date('Y-m-d');
		$inienddate = new DateTime($init['enddate']);
		$inienddate->modify('- 365 days');
		$init['startdate'] = $inienddate->format('Y-m-d');

		$cities = $this->MCity->allCity('_id, city');
		$init['cities'] = $cities['result'];

		if(($startdate = filter_input(INPUT_POST, 'startdate')) && ($enddate = filter_input(INPUT_POST, 'enddate'))) {
			$init['startdate'] = $startdate;
			$init['enddate'] = $enddate;

			$startdateConvert = new DateTime($startdate);
			$startdate = $startdateConvert->format('Y/m/d');
			
			$enddateConvert = new DateTime($enddate);
			$enddate = $enddateConvert->format('Y/m/d');

			$filter = array('date >= ' => $startdate, 'date <=' => $enddate);
			$filter_in = array();

			if($city = filter_input(INPUT_POST, 'city')) {
				if($city < 10) {
					$filter['cityCode'] = $city;
				}else {
					$filter['cityCode'] = (int) $city;
				}
			}

			if($listnumber = filter_input(INPUT_POST, 'listnumber')) {
				$listnumber = str_replace(' ', ',', $listnumber);
				$explode = explode(',', $listnumber);
				$listnumber = array();
				foreach ($explode as $value) {
					if(is_numeric($value)) {
						$listnumber[] = $value;
					}
				}

				$filter_in['loto'] = $listnumber; 
			}

			$init['result'] = $this->MResult->statistic($filter, $filter_in, 'date, date2');
		}

		$data['content_view'] = 'VStatistic';
		$data['content'] = $init;
        $this->load->view('wrapper', $data);
	}
}