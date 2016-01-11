<?php 

class MCity extends CI_Model {
	/**
	 * Table name
	 */
	private $table = 'city';
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
	 * Get cities round in today
	 *
	 * @param date $day day need get
	 * @param string $select list fields need get
	 * 
	 * @return array
	 */
	public function cityInDay($day, $select = '*') {
		$dayInWeek = new DateTime($day);

		$dayInWeek = $dayInWeek->format('N') + 1;

		$get = $this->db->select($select)->like(array('day' => '.*'.$dayInWeek.'.*'))->or_where(array('day' => '0'));

		return $get->get($this->table);
	}
	// -------------------------------------------------------------------------
	/**
	 * Get list cities
	 * 
	 * @param string $select
	 * 
	 * @return array
	 */
	public function allCity($select = '*') {
		return $this->db->select($select)->get($this->table);
	}
}