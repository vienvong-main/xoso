<?php 

class MKetqua extends CI_Model {

	private $table = 'ketqua';

	public function __construct() {
		parent::__construct();
		$this->load->database();
	}

	public function save($data, $batch = false) {
		if(!$batch) {
			$insert = $this->db->insert($this->table, $data);
		}else {
			$insert = $this->db->insert_batch($this->table, $data);
		}
	}
}