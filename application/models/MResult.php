<?php 

class MResult extends CI_Model {

	/**
	 * Table name
	 */
	private $table = 'result';
	// -------------------------------------------------------------------------
	/**
	 * constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	// -------------------------------------------------------------------------
	/**
	 * Insert data into table
	 *
	 * @param array $data data needed input
	 * @param bool $batch is insert batch
	 *
	 * @return array
	 */
	public function insert($data, $batch = false) {
		if(!$batch) {
			$insert = $this->db->insert($this->table, $data);
		}else {
			$insert = $this->db->insert_batch($this->table, $data);
		}

		return $insert;
	}
	// -------------------------------------------------------------------------
	/**
	 * Get result from table
	 *
	 * @param array $filter filter conditions
	 * @param array $filter_in filter conditions
	 * @param mixed $select fields need get
	 *
	 * @return array
	 */
	public function get($filter, $filter_in = array(), $select = '*') {
		$get = $this->db->select($select)->where($filter);

		if(!empty($filter_in)) {
			foreach ($filter_in as $key => $value) {
				$get->where_in($key, $value);
			}
		}

		return $get->get($this->table);
	}
	// -------------------------------------------------------------------------
	/**
	 * Count result
	 *
	 * @param array $filter filter conditions
	 * @param array $filter_in filter conditions
	 * @param mixed $select fields need get
	 *
	 * @return array
	 */
	public function statistic($filter, $filter_in = array(), $select = '*') {
		// $get = $this->db->select_count('date')->where($filter)->group_by('loto');
		$get = $this->db->select($select)->select_count('loto')->group_by('loto')->select_max('date')->where($filter);
		if(!empty($filter_in)) {
			foreach ($filter_in as $key => $value) {
				$get->where_in($key, $value);
			}
		}

		$statistic = $get->get($this->table);

		$statistic['result'] = $this->_remake_key('_id, loto', $statistic['result']);

		asort($statistic['result']);

		foreach ($statistic['result'] as $key => $value) {
			$time = time();

			$endtime = new DateTime($statistic['result'][$key]['date_MAX']);

			$endtime = strtotime($endtime->format('Y/m/d 00:00:00'));
			$statistic['result'][$key]['long'] = floor(($time - $endtime)/(3600*24));
		}

		return $statistic['result'];
	}
	// -------------------------------------------------------------------------
	/**
	 * Update data in table
	 *
	 * @param array $data data need update
	 * @param array $fitler filter conditions
	 *
	 * @return array;
	 */
	public function update($data, $filter) {
		return $this->db->update($this->table, $data, $filter);
	}

	// -------------------------------------------------------------------------
	/**
	 * Remake result key
	 *
	 * @param mixed $levelKey
	 * @param array $result
	 * 
	 * @return array
	 */
	private function _remake_key($levelKey, $result) {
		$convert = array();

		// build level key array
		if(!is_array($levelKey)) {
			$levelKey = explode(',', str_replace(' ', null, $levelKey));
		}

		// remake array
		foreach ($result as $value) {
			$next['key'] = null;
			$next['value'] = $value;

			foreach ($levelKey as $key) {
				$next['key'] = $key;
				$next['value'] = $next['value'][$key];
			}

			$convert[$next['value']] = $value;
		}

		return $convert;
	}
}