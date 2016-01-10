<?php
/**
 * Created by PhpStorm.
 * User: hoanggia
 * Date: 1/13/15
 * Time: 8:59 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Mongo_query_builder extends CI_DB {

    /**
     * Comparison
     *
     * All comparison orders of where and having conditions
     */
    private  $comparison = array(
                                    '$gte' => '>=',
                                    '$lte' => '<=',
                                    '$ne' => '!=',
                                    '$gt' => '>',
                                    '$lt' => '<'
                                );

    /**
     * Wildcard SQL Like
     *
     * All special characters of wildcard SQL Like
     */
    private $sql_wildcard = array(
        0 =>'[',
        1 => ']',
        2 => '_');

    /**
     * Regular Express MongoDB
     *
     * All special characters of MongoDB Regex
     */
    private $mongo_regex = array(
        0 => '.([',
        1 => '])',
        2 => '.{1}');

    /**
     * Group Where Queue Caching
     *
     * Storage queue of groups content
     */
    private $mongo_group_status = array();

    // --------------------------------------------------------------------
    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param    string    the table
     * @param    string    the limit clause
     * @param    string    the offset clause
     * @return    object
     */
    public function get($table = '', $limit = NULL, $offset = NULL)
    {

        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        // set limit and offset conditions
        if ( ! empty($limit))
        {
            $this->limit($limit, $offset);
        }elseif(!empty($offset)) {
            $this->offset($offset);
        }

        if(!$this->table_exists($table)) {
            $this->display_error("Table <b>\"$table\"</b> don't exists!", '', TRUE);
        }

        $select = $this->_build_stages();

        $result = $result = $this->db->{$table}
            ->aggregate($select);

        // Save the query for debugging
        if ($this->save_queries === TRUE)
        {
            $this->queries[] = $this->get_compiled_select($table);
        }else {
            $this->_reset_select();
        }

        return $result;
    }

    // --------------------------------------------------------------------

    /**
     * Build all stages of aggregate query function
     *
     * Generates a query stage array based on which functions were used.
     * Should not be called directly.
     *
     * @return	array
     */
    private  function _build_stages()
    {
        $select = array();

        if($this->qb_distinct === true) {
            if($this->qb_select) {
                $fields = implode(',', $this->qb_select);
                $this->qb_groubby['$group']['distinct']['$addToSet'] = $this->qb_select;
            }
        }

        // Filter Where conditions
        if(!empty($this->qb_where)) {
            array_push($select, array('$match'=>$this->qb_where));
        }

        if(!empty($this->qb_groupby)) {
            array_push($select, $this->qb_groupby);

        }else {

            // build order by condition
            if(!empty($this->qb_orderby)) {
                array_push($select, array('$sort' => $this->qb_orderby));
            }

            // build select required
            if(!empty($this->qb_select)) {
                array_push($select, array('$project' => $this->qb_select));
            }

            // build offset condition
            if(!empty($this->qb_offset)) {
                array_push($select, array('$skip' => $this->qb_offset));
            }

            // build limit condition
            if(!empty($this->qb_limit)) {
                array_push($select, array('$limit' => $this->qb_limit));
            }
        }

        // Having group
        if(!empty($this->qb_having)) {
            array_push($select, array('$match'=>$this->qb_having));
        }

        return $select;
    }

    // --------------------------------------------------------------------

    /**
     * Get_Where
     *
     * Allows the where clause, limit and offset to be added directly
     *
     * @param    string $table
     * @param    string $where
     * @param    int $limit
     * @param    int $offset
     * @return    object
     */
    public function get_where($table = '', $where = NULL, $limit = NULL, $offset = NULL)
    {
        // set where conditions
        $this->where($where);

        // get result query
        $result = $this->get($table, $limit, $offset);

        return $result;
    }

    // --------------------------------------------------------------------

    /**
     * Get SELECT query string
     *
     * Compiles a SELECT query string and returns the sql.
     *
     * @param    string    the table name to select from (optional)
     * @param    bool    TRUE: resets QB values; FALSE: leave QB vaules alone
     * @return    string
     */
    public function get_compiled_select($table = '', $reset = true)
    {
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        // build aggregate stages
        $stages = json_encode($this->_build_stages(), TRUE);

        $stages = substr($stages, 1, strlen($stages)-2);

        // build aggregate query
        $get = "db.{$table}.aggregate({$stages})";

        // clear cache of current query
        if($reset) {
            $this->_reset_select();
        }

        return $get;
    }

    // --------------------------------------------------------------------

    /**
     * WHERE
     *
     * Generates the WHERE portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param    mixed
     * @param    mixed
     * @param    bool
     * @return    CI_DB_query_builder
     */
    public function where($key, $value = NULL, $escape = NULL)
    {
        return $this->_wh('qb_where', $key, $value);
    }

    // --------------------------------------------------------------------
    /**
     * Full text search
     *
     * @param   string   $value  string need search
     * @return  CI_DB_query_builder
     */
    public function fulltext_where($value)
    {
        if(is_string($value)) {
            $match['$text']['$search'] = $value;
            $this->_build_where_condition('qb_where',$match, 'AND');
            $this->fulltext_sort();
        }

        return $this;

    }

    // --------------------------------------------------------------------
    /**
     * Full text search
     *
     * @param   string   $value  string need search
     * @return  CI_DB_query_builder
     */
    public function fulltext_sort()
    {
        $orderby = array(
            'score' => array(
                '$meta' => "textScore"
            )
        );
        if(empty($this->qb_orderby)) {
            $this->qb_orderby = $orderby;
        }else {
            $this->qb_orderby = array_merge($this->qb_orderby, $orderby);
        }

        return $this;

    }

    // --------------------------------------------------------------------

    /**
     * OR WHERE
     *
     * Generates the WHERE portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param    mixed
     * @param    mixed
     * @param    bool
     * @return    CI_DB_query_builder
     */
    public function or_where($key, $value = NULL, $escape = NULL)
    {
        return $this->_wh('qb_where', $key, $value, ' OR ');
    }

    // --------------------------------------------------------------------

    /**
     * WHERE IN
     *
     * Generates a WHERE field IN('item', 'item') SQL query,
     * joined with 'AND' if appropriate.
     *
     * @param    string $key The field to search
     * @param    array $values The values searched on
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function where_in($key = NULL, $values = NULL, $escape = NULL)
    {
        return $this->_where_in($key, $values);
    }

    // --------------------------------------------------------------------

    /**
     * WHERE NOT IN
     *
     * Generates a WHERE field NOT IN('item', 'item') SQL query,
     * joined with 'AND' if appropriate.
     *
     * @param    string $key The field to search
     * @param    array $values The values searched on
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function where_not_in($key = NULL, $values = NULL, $escape = NULL)
    {
        return $this->_where_in($key, $values, TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * OR WHERE IN
     *
     * Generates a WHERE field IN('item', 'item') SQL query,
     * joined with 'OR' if appropriate.
     *
     * @param    string $key The field to search
     * @param    array $values The values searched on
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function or_where_in($key = NULL, $values = NULL, $escape = NULL)
    {
        return $this->_where_in($key, $values, FALSE, 'OR');
    }

    // --------------------------------------------------------------------

    /**
     * OR WHERE NOT IN
     *
     * Generates a WHERE field NOT IN('item', 'item') SQL query,
     * joined with 'OR' if appropriate.
     *
     * @param    string $key The field to search
     * @param    array $values The values searched on
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function or_where_not_in($key = NULL, $values = NULL, $escape = NULL)
    {
        return $this->_where_in($key, $values, TRUE, 'OR');
    }

    // --------------------------------------------------------------------

    /**
     * WHERE, HAVING
     *
     * @used-by	where()
     * @used-by	or_where()
     * @used-by	having()
     * @used-by	or_having()
     *
     * @param	string	$qb_key	'qb_where' or 'qb_having'
     * @param	mixed	$key
     * @param	mixed	$value
     * @param	string	$type
     * @param	bool	$escape
     * @return	CI_DB_query_builder
     */
    protected function _wh($qb_key, $key, $value = NULL, $type = 'AND ', $escape = NULL)
    {
//        $qb_cache_key = ($qb_key === 'qb_having') ? 'qb_cache_having' : 'qb_cache_where';

        if(empty($key)) {
            return $this;
        }

        if ( ! is_array($key))
        {
            $key = array($key => $value);
        }

        $match = array();

        foreach($key as $wh=>$val) {
            $compile = $this->_compile_where_comparison($wh, $val);

            if(!empty($match[$compile['key']]) && is_array($match[$compile['key']])) {
                $match[$compile['key']] = array_merge($match[$compile['key']], $compile['value']);
            }else {
                $match[$compile['key']] = $compile['value'];
            }
            
        }

        $this->_build_where_condition($qb_key,$match, $type);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Internal WHERE IN
     *
     * @used-by	where_in()
     * @used-by	or_where_in()
     * @used-by	where_not_in()
     * @used-by	or_where_not_in()
     *
     * @param	string	$key	The field to search
     * @param	array	$values	The values searched on
     * @param	bool	$not	If the statement would be IN or NOT IN
     * @param	string	$type
     * @param	bool	$escape
     * @return	CI_DB_query_builder
     */
    protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ', $escape = NULL)
    {
        if ($key === NULL OR $values === NULL)
        {
            return $this;
        }

        if ( ! is_array($values))
        {
            $values = array($values);
        }

        if(!is_string($key)) {
            $this->display_error("Field's name invalid. Field's is a string only", '', TRUE);
        }

        $match = array();

        if(!$not){
            $match[$key] = array('$in' => $values);
        }else {
            $match[$key] = array('$nin' => $values);
        }

        $this->_build_where_condition('qb_where',$match, $type);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Build "OR" of Where
     *
     * Build "OR" phrase of Where conditions
     *
     * @param   array   $where
     * @param   array   $match
     * @return  array   new statement of where condition
     */
    protected function _or_and_where($where, $match, $type = 'AND', $not ='') {

        if(trim($type) == 'OR'){
            $type = '$or';
            if(trim($not) == 'NOT') {
                $type = '$nor';
            }
        }else {
            $type = '$and';

            if(trim($not) == 'NOT') {
                $type = '$not';
            }
        }

        if(empty($where)) {
            if($type == '$not' && $this->qb_where_group_count == 0) {
                $order['$nor'] = array();
                $current_match['$and'] = array($match, array('true'=>true));
                array_push($order['$nor'], $current_match);
                $where = $order;
            }else {$where = $match;}

        }else {
            if($type == '$not') {
                $order['$nor'] = array();
                $current_match['$and'] = array($where, $match);
                array_push($order['$nor'], $current_match);
                $where = $order;
            }else {
                $order[$type] = array();
                array_push($order[$type], $where);
                array_push($order[$type], $match);
                $where = $order;
            }
        }

        return $where;
    }

    // --------------------------------------------------------------------

    /**
     * Build Where Condition
     *
     * Push phrase of Where conditions into qb_where or qb_where_group_started
     *
     * @param   array   $match
     * @param   string  $type
     * @return  current stage of object
     */
    private function _build_where_condition($qb_key, $match, $type='AND') {

        if($this->qb_where_group_count > 0 AND $qb_key == 'qb_where') {
            $current = $this->qb_where_group_started[$this->qb_where_group_count];
            $not = $this->mongo_group_status[$this->qb_where_group_count][0];

            $current = $this->_or_and_where($current, $match, $type, $not);

            $this->qb_where_group_started[$this->qb_where_group_count] = $current;
        }else {
            $this->{$qb_key} = $this->_or_and_where($this->{$qb_key}, $match, $type);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Compile WHERE, HAVING statements
     *
     * Escapes identifiers in WHERE and HAVING statements at execution time.
     *
     * Required so that aliases are tracked properly, regardless of wether
     * where(), or_where(), having(), or_having are called prior to from(),
     * join() and dbprefix is added only if needed.
     *
     * @param	string	$key    field name compare
     * @param   string  $value  value compare
     * @return	string	SQL statement
     */
    protected function _compile_where_comparison($key, $value) {

        // replace all whitespace
        $key = str_replace(' ', '', $key);

        foreach ($this->comparison as $comp_key => $comp_val) {
            if(strpos($key, $comp_val) !== FALSE) {
                $key = trim(str_replace($comp_val,' ', $key));

                if(strpos($key, ' ') !== FALSE) {
                    $key = explode(' ', $key);

                    return array(
                        'key' => $key[0],
                        'value' => array(
                            $comp_key => $key[1]
                        )
                    );
                }

                return array(
                    'key' => $key,
                    'value' => array(
                        $comp_key => $value
                    )
                );
            }
        }

        return array('key' => trim($key), 'value' => $value);
    }

    // --------------------------------------------------------------------

    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param    string
     * @param    mixed
     * @return    CI_DB_query_builder
     *
     */

    public function select($select = '*', $escape = NULL)
    {
        // var to cache select field request
        $select_query = array();

        if($select !== '*') {

            // convert select string to array
            $select = explode(',', $select);

            foreach( $select as $field) {

                // replace all white space at right and left string
                $field = trim($field);

                // filter all different type mongoDB not support
                if(strpos($field, ' ')) {
                    $this->display_error(
                        "Select parameter <b>\"$field\"</b> don't supported by MongoDB driver",
                        '',
                        TRUE
                    );
                }

                // push select field to cache
                $select_query[$field] = 1;
            }

        }else {
            return $this;
        }

        // don't get _id default field of MongoDB
        if(!isset($select_query['_id'])) {
            $select_query['_id'] = 0;
        }

        // set select query to global parameter
        $this->qb_select = $select_query;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param   bool    $val
     * @return  CI_DB_query_builder
     */
    public function distinct($val = TRUE)
    {
        $this->qb_distinct = is_bool($val) ? $val : TRUE;
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * GROUP BY
     *
     * @param    string $by
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function group_by($by, $escape = NULL)
    {
        // convert group_by string to array
        if(!empty($by) && !is_array($by)) {
            $by = explode(',', $by);
        }

        $group = array();

        if(empty($by)) {
            $group = null;
        }else {
            foreach ($by as $key=>$value) {
                $group[$value] = '$'.trim($value);
            }
        }

        if(empty($this->qb_groupby['$group']['_id'])){
            $this->qb_groupby['$group']['_id']= $group;
        }else {
            $this->qb_groupby['$group']['_id'] = array_merge($this->qb_groupby['$group']['_id'],$group);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * HAVING
     *
     * Separates multiple calls with 'AND'.
     *
     * @param    string $key
     * @param    string $value
     * @param    bool $escape
     * @return    object
     */
    public function having($key, $value = NULL, $escape = NULL)
    {
        return $this->_wh('qb_having', $key, $value);
    }

    // --------------------------------------------------------------------

    /**
     * OR HAVING
     *
     * Separates multiple calls with 'OR'.
     *
     * @param    string $key
     * @param    string $value
     * @param    bool $escape
     * @return    object
     */
    public function or_having($key, $value = NULL, $escape = NULL)
    {
        return $this->_wh('qb_having', $key, $value, ' OR ');
    }

    // --------------------------------------------------------------------

    /**
     * LIKE
     *
     * Generates a %LIKE% portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param    mixed $field
     * @param    string $match
     * @param    string $side
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function like($field, $match = '', $side = 'both', $escape = NULL, $mongoSyntax = FALSE)
    {
        return $this->_like($field, $match, 'AND', $side, '', $escape, $mongoSyntax);
    }

    // --------------------------------------------------------------------

    /**
     * NOT LIKE
     *
     * Generates a NOT LIKE portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param	mixed	$field
     * @param	string	$match
     * @param	string	$side
     * @param	bool	$escape
     * @return	CI_DB_query_builder
     */
    public function not_like($field, $match = '', $side = 'both', $escape = NULL, $mongoSyntax = FALSE)
    {
        return $this->_like($field, $match, 'AND ', $side, 'NOT', $escape, $mongoSyntax);
    }

    // --------------------------------------------------------------------

    /**
     * OR LIKE
     *
     * Generates a %LIKE% portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param	mixed	$field
     * @param	string	$match
     * @param	string	$side
     * @param	bool	$escape
     * @return	CI_DB_query_builder
     */
    public function or_like($field, $match = '', $side = 'both', $escape = NULL, $mongoSyntax = FALSE)
    {
        return $this->_like($field, $match, 'OR ', $side, '', $escape, $mongoSyntax);
    }

    // --------------------------------------------------------------------

    /**
     * OR NOT LIKE
     *
     * Generates a NOT LIKE portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param	mixed	$field
     * @param	string	$match
     * @param	string	$side
     * @param	bool	$escape
     * @return	CI_DB_query_builder
     */
    public function or_not_like($field, $match = '', $side = 'both', $escape = NULL, $mongoSyntax = FALSE)
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT', $escape, $mongoSyntax);
    }

    // --------------------------------------------------------------------

    /**
     * Internal LIKE
     *
     * @used-by	like()
     * @used-by	or_like()
     * @used-by	not_like()
     * @used-by	or_not_like()
     *
     * @param	mixed	$field
     * @param	string	$match
     * @param	string	$type
     * @param	string	$side
     * @param	string	$not
     * @param	bool	$escape
     * @return	CI_DB_query_builder
     */
    protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '', $escape = NULL, $mongoSyntax = FALSE)
    {
        if ( ! is_array($field))
        {
            $field = array($field => $match);
        }

        $flag = 'imsx';

        foreach ($field as $k => $v)
        {
            if(!$mongoSyntax){
                $v = $this->_wildcard_mongo($v);
            }

            if($not === 'NOT') {
                $v = "[!{$v}]";
            }

            switch($side) {
                case 'none':{
                    $v = '/^'.$v.'$/'.$flag;
                    break;
                }
                case 'before':{
                    $v = '/'.$v.'$/'.$flag;
                    break;
                }
                case 'after':{
                    $v = '/^'.$v.'/'.$flag;
                    break;
                }
                default:{
                    $v = '/'.$v.'/'.$flag;
                    break;
                }
            }

            $re = new MongoRegex($v);
            $field[$k] = array('$regex' => $re);
        }

        $this->_build_where_condition('qb_where',$field, $type);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Wildcard in Mongo
     *
     * convert SQL wildcard to Regular Expression of MongoDB
     *
     * @used-by	_like()
     *
     * @param	string  $match
     *
     * @return	match converted
     */
    protected function _wildcard_mongo($match)
    {

        $match = str_replace($this->sql_wildcard, $this->mongo_regex, $match);

        return $match;
    }

    // --------------------------------------------------------------------

    /**
     * SELECT [MAX|MIN|AVG|SUM]()
     *
     * @used-by	select_max()
     * @used-by	select_min()
     * @used-by	select_avg()
     * @used-by	select_sum()
     *
     * @param	string	$select	Field name
     * @param	string	$alias
     * @param	string	$type
     * @return	CI_DB_query_builder
     */
    protected function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX')
    {
        // filter select fields query input
        if ( ! is_string($select) OR $select === '')
        {
            $this->display_error('db_invalid_query');
        }

        // filter type of function input
        $type = strtolower($type);

        if ( ! in_array($type, array('max', 'min', 'avg', 'sum')))
        {
            show_error('Invalid function type: '.$type);
            $this->display_error("Invalid function type: <b>'{$type}'</b>. A aggregate funtion have type 'MAX, MIN, SUM, AVG'.",'' , TRUE);
        }

        if($alias === '') {
            $alias = $select.'_'.strtoupper($type);
        }

        $this->qb_groupby['$group'][$alias]['$'.$type] = '$'.$select;

        if(empty($this->qb_groupby['$group']['_id'])) {
            $this->group_by(null, FALSE);
        }

        if(!empty($this->qb_select) && ($type == 'max' || $type == 'min')) {
            foreach($this->qb_select as $select_key=>$select_val) {
                if($select_val){
                    $this->qb_groupby['$group'][$select_key]['$addToSet'] = '$'.$select_key;
                }
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Select Max
     *
     * Generates a SELECT MAX(field) portion of a query
     *
     * @param    string    the field
     * @param    string    an alias
     * @return    CI_DB_query_builder
     */

    public function select_max($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'MAX');
    }

    // --------------------------------------------------------------------

    /**
     * Select Min
     *
     * Generates a SELECT MIN(field) portion of a query
     *
     * @param    string    the field
     * @param    string    an alias
     * @return    CI_DB_query_builder
     *
     */
    public function select_min($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'MIN');
    }

    // --------------------------------------------------------------------

    /**
     * Select Average
     *
     * Generates a SELECT AVG(field) portion of a query
     *
     * @param    string    the field
     * @param    string    an alias
     * @return    CI_DB_query_builder
     */

    public function select_avg($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'AVG');
    }

    // --------------------------------------------------------------------
    /**
     * Select Count
     *
     * @param    string $by
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function select_count($by, $escape = NULL)
    {
        // convert group_by string to array
        if(!empty($by) && !is_array($by)) {
            $by = explode(',', $by);
        }

        $group = array();

        if(empty($by)) {
            $group = null;
        }else {
            foreach ($by as $key=>$value) {
                $group[$value] = '$'.trim($value);
            }
        }

        if(empty($this->qb_groupby['$group']['_id'])){
            $this->qb_groupby['$group']['_id']= $group;
        }else {
            $this->qb_groupby['$group']['_id'] = array_merge($this->qb_groupby['$group']['_id'],$group);
        }

        $this->qb_groupby['$group']['count'] = array('$sum' => 1);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Select Sum
     *
     * Generates a SELECT SUM(field) portion of a query
     *
     * @param    string    the field
     * @param    string    an alias
     * @return    CI_DB_query_builder
     */
    public function select_sum($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'SUM');
    }

    // --------------------------------------------------------------------

    /**
     * "Count All Results" query
     *
     * Generates a platform-specific query string that counts all records
     * returned by an Query Builder query.
     *
     * @param    string
     * @return    int
     */
    public function count_all_results($table = '', $reset = true)
    {
        // query database
        $result = $this->get($table);

        return count($result['result']);
    }

    // --------------------------------------------------------------------

    /**
     * ORDER BY
     *
     * @param    string $orderby
     * @param    string $direction ASC, DESC or RANDOM
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */
    public function order_by($orderby, $direction = '', $escape = NULL)
    {
        if(strpos(trim($orderby), ' ')) {
            $orderby = explode(',', $orderby);

            foreach ($orderby as $order_param) {
                $param = explode(' ', $order_param);
                $parameters = array();

                foreach($param as $value) {
                    if(!empty($value)) {
                        $parameters[] = $value;
                    }
                }

                if(count($parameters) === 2) {
                    $this->_build_orderby_params($parameters[0], $parameters[1]);
                }
            }

        }else {
            $this->_build_orderby_params($orderby, $direction);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * ORDER BY
     *
     * @param    string $orderby
     * @param    string $direction ASC, DESC or RANDOM
     * @param    bool $escape
     * @return    CI_DB_query_builder
     */

    private function _build_orderby_params($orderby, $direction ) {
        switch(strtoupper(trim($direction))) {
            case '': {
                $direction = 0;
                break;
            }
            case 'DESC': {
                $direction = -1;
                break;
            }
            case 'ASC': {
                $direction = 1;
                break;
            }
            default: {
                break;
            }
        }

        $orderby = array($orderby => $direction);

        if(empty($this->qb_orderby)) {
            $this->qb_orderby = $orderby;
        }else {
            $this->qb_orderby = array_merge($this->qb_orderby, $orderby);
        }
    }

    // --------------------------------------------------------------------

    /**
     * JOIN
     *
     * Generates the JOIN portion of the query
     *
     * @param    string
     * @param    string    the join condition
     * @param    string    the type of join
     * @param    string    whether not to try to escape identifiers
     * @return    CI_DB_query_builder
     */
    public function join($table, $cond, $type = '', $escape = NULL)
    {
        $this->display_error(
            'MongoDB does not support join table in query.<br>
                If you want join tables you can using linking
                <a href="http://docs.mongodb.org/manual/reference/database-references/">DBRef</a>.',
            'Not support join table',
            TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * Starts a query group.
     *
     * @param    string $not (Internal use only)
     * @param    string $type (Internal use only)
     * @return    CI_DB_query_builder
     */
    public function group_start($not = '', $type = 'AND ')
    {

        $this->qb_where_group_count += 1;
        $this->mongo_group_status[$this->qb_where_group_count][0] = $not;
        $this->mongo_group_status[$this->qb_where_group_count][1] = $type;
        $this->qb_where_group_started[$this->qb_where_group_count] = array();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Starts a query group, but NOTs the group
     *
     * @return    CI_DB_query_builder
     */
    public function not_group_start()
    {
        $this->group_start('NOT');

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Starts a query group, but ORs the group
     *
     * @return    CI_DB_query_builder
     */
    public function or_group_start()
    {
        $this->group_start('', 'OR');

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Starts a query group, but OR NOTs the group
     *
     * @return    CI_DB_query_builder
     */
    public function or_not_group_start()
    {
        $this->group_start('NOT', 'OR');

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Ends a query group
     *
     * @return    CI_DB_query_builder
     */
    public function group_end()
    {
        if($this->qb_where_group_count > 0) {
            // get data was cached
            $where_group = $this->qb_where_group_started[$this->qb_where_group_count];
            $type = $this->mongo_group_status[$this->qb_where_group_count][1];

            // restore counting of grouping
            $this->qb_where_group_count --;

            $this->_build_where_condition('qb_where',$where_group, $type);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param    mixed    the table(s) to delete from. String or array
     * @param    mixed    the where clause
     * @param    mixed    the limit clause
     * @param    bool
     * @return    mixed
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = true)
    {
        // set table will be query
        if ($table !== '')
        {
            $list_table = array();

            if(!empty($this->qb_from)) {
                $list_table = $this->qb_from;
            }

            if(!is_array($table)) {
                $this->from($table);
                $table = array(str_replace('`','',$this->qb_from[0]));
            }

            foreach($table as $table_name) {
                array_push($list_table, $table_name);
            }

            $this->qb_from = $list_table;

        }elseif(empty($this->qb_from)) {
            $this->display_error('Table\'s name just never setup.','',TRUE);
        }

        // set where conditions
        if($where != '') {
            $this->where($where);
        }

        // check where condition
        if (count($this->qb_where) === 0)
        {
            return ($this->db_debug) ? $this->display_error('db_del_must_use_where') : FALSE;
        }

        // set limit and offset conditions
        if ( ! empty($limit))
        {
            $this->limit($limit);
        }

        $cursor = $this->db->{$this->qb_from[0]}->find();
        foreach ($cursor as $doc) {
            var_dump($doc);
        }

        $remove = array();

        // delete data into table
        foreach($this->qb_from as $remover) {
            var_dump($remover, $this->qb_where);

            $remove[] = $this->db->{$remover}->remove($this->qb_where);
        }

        if ($reset_data)
        {
            $this->_reset_write();
        }

        // Save the query for debugging
        if ($this->save_queries === TRUE)
        {
            $this->queries[] = $this->get_compiled_select();
        }else {
            $this->_reset_select();
        }

        return $remove;
    }

    // --------------------------------------------------------------------

    /**
     * Empty Table
     *
     * Compiles a delete string and runs "DELETE FROM table"
     *
     * @param    string    the table to empty
     * @return    object
     */
    public function empty_table($table = '')
    {
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        return $this->db->{$table}->remove(array());
    }

    // --------------------------------------------------------------------

    /**
     * Truncate
     *
     * Compiles a truncate string and runs the query
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param    string    the table to truncate
     * @return    object
     */
    public function truncate($table = '')
    {
        return $this->empty_table($table);
    }

    // --------------------------------------------------------------------

    /**
     * Get DELETE query string
     *
     * Compiles a delete query string and returns the sql
     *
     * @param    string    the table to delete from
     * @param    bool    TRUE: reset QB values; FALSE: leave QB values alone
     * @return    string
     */
    public function get_compiled_delete($table = '', $reset = true)
    {
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        $where = json_encode($this->qb_where, true);

        $delete = "db.{$table}.remove({$where})";

        return $delete;
    }

    // --------------------------------------------------------------------

    /**
     * The "set" function.
     *
     * Allows key/value pairs to be set for inserting or updating
     *
     * @param    mixed
     * @param    string
     * @param    bool
     * @return    CI_DB_query_builder
     */
    public function set($key, $value = '', $escape = NULL)
    {
        if(!is_array($key)){
            $key = array($key => $value);
        }

        foreach($key as $k=>$v) {
            $this->qb_set[$k] = $v;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param    string    the table to insert data into
     * @param    array    an associative array of insert values
     * @param    bool $escape Whether to escape values and identifiers
     * @return    object
     */
    public function insert($table = '', $set = NULL, $escape = NULL)
    {
        // input update data if that was setup
        if ($set !== NULL)
        {
            $this->set($set);
        }

        // check insert data available
        if (count($this->qb_set) === 0)
        {
            return ($this->db_debug) ? $this->display_error('db_must_use_set') : FALSE;
        }

        // setup table name
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            $table = str_replace('`','',$this->qb_from[0]);
        }

        // Inset data into table
        $insert = $this->db->{$table}->insert($this->qb_set);

        // clear data insert
        $this->_reset_write();
        return $insert;
    }

    // --------------------------------------------------------------------

    /**
     * Insert_Batch
     *
     * Compiles batch insert strings and runs the queries
     *
     * @param    string $table Table to insert into
     * @param    array $set An associative array of insert values
     * @param    bool $escape Whether to escape values and identifiers
     * @return    int    Number of rows inserted or FALSE on failure
     */
    public function insert_batch($table = '', $set = NULL, $escape = NULL)
    {
        // input update data if that was setup
        if ($set !== NULL)
        {
            $this->set($set);
        }

        // check insert data available
        if (count($this->qb_set) === 0)
        {
            return ($this->db_debug) ? $this->display_error('db_must_use_set') : FALSE;
        }

        // setup table name
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            $table = $this->qb_from[0];
        }

        // insert all rows
        $insert = $this->db->{$table}->batchInsert($this->qb_set);

        $this->_reset_write();
        return $insert;
    }

    // --------------------------------------------------------------------

    /**
     * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
     *
     * @param    mixed
     * @param    string
     * @param    bool
     * @return    CI_DB_query_builder
     */
    public function set_insert_batch($key, $value = '', $escape = NULL)
    {
        $key = $this->_object_to_array_batch($key);

        if ( ! is_array($key))
        {
            $key = array($key => $value);
        }

        $keys = array_keys($this->_object_to_array(current($key)));
        sort($keys);

        foreach ($key as $row)
        {
            $row = $this->_object_to_array($row);
            if (count(array_diff($keys, array_keys($row))) > 0 OR count(array_diff(array_keys($row), $keys)) > 0)
            {
                // batch function above returns an error on an empty array
                $this->qb_set[] = array();
                return;
            }

            ksort($row); // puts $row in the same order as our keys

            $this->qb_set[] = $row;
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Get INSERT query string
     *
     * Compiles an insert query and returns the sql
     *
     * @param    string    the table to insert into
     * @param    bool    TRUE: reset QB values; FALSE: leave QB values alone
     * @return    string
     */
    public function get_compiled_insert($table = '', $reset = true)
    {
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        $set = json_encode($this->qb_set);

        // get table name
        $insert = "db.$table.insert($set)";

        // clear all data insert
        if($reset) {
            $this->_reset_write();
        }

        return $insert;
    }

    // --------------------------------------------------------------------

    /**
     * Generate an insert string
     *
     * @param    string    the table upon which the query will be performed
     * @param    array    an associative array data of key/values
     * @return    string
     */
    public function insert_string($table, $data)
    {
        // clear old data
        $this->_reset_write();

        // input insert data into temp cache
        if(is_array($data)) {
            $this->set($data);
        }

        // return insert string
        return $this->get_compiled_insert($table);
    }

    // --------------------------------------------------------------------

    /**
     * UPDATE
     *
     * Compiles an update string and runs the query.
     *
     * @param    string $table
     * @param    array $set An associative array of update values
     * @param    mixed $where
     * @param    int $limit
     * @return    object
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
    {
        // input update data if that was setup
        if ($set !== NULL)
        {
            $this->set($set);
        }

        // check insert data available
        if (count($this->qb_set) === 0)
        {
            return ($this->db_debug) ? $this->display_error('db_must_use_set') : FALSE;
        }

        // setup table name
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            $table = $this->qb_from[0];
        }

        // set where conditions
        $this->where($where);

        // set limit and offset conditions
        if ( ! empty($limit))
        {
            $this->limit($limit);
        }

        // update data into database
        $update = $this->db->{$table}->update($this->qb_where, array('$set' => $this->qb_set));

        // clear data insert
        $this->_reset_write();

        $update['data'] = $this->qb_set;

        return $update;
    }

    // --------------------------------------------------------------------

    /**
     * Get UPDATE query string
     *
     * Compiles an update query and returns the sql
     *
     * @param    string    the table to update
     * @param    bool    TRUE: reset QB values; FALSE: leave QB values alone
     * @return    string
     */
    public function get_compiled_update($table = '', $reset = true)
    {
        // set table will be query
        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        // build where point update
        $update_where = json_encode($this->qb_where, true);

        // build setup data
        $update_data = json_encode($this->qb_set, true);

        // build update options
        $update_options = json_encode(array('multiple' => true));

        // build update query
        $update_query = "db.$table.update($update_where, $update_data, $update_options)";

        // clear data update
        if($reset) {
            $this->_reset_write();
        }

        return $update_query;
    }

    // --------------------------------------------------------------------

    /**
     * Generate an update string
     *
     * @param    string    the table upon which the query will be performed
     * @param    array    an associative array data of key/values
     * @param    mixed    the "where" statement
     * @return    string
     */
    public function update_string($table, $data, $where)
    {
        // clear old data
        $this->_reset_write();

        // input insert data into temp cache
        if(is_array($data)) {
            $this->set($data);
        }

        // set where conditions
        if(is_array($where)) {
            $this->where($where);
        }

        // build update string query
        $update_string = $this->get_compiled_update($table);

        return $update_string;
    }

    // --------------------------------------------------------------------

    /**
     * The "set_update_batch" function.  Allows key/value pairs to be set for batch updating
     *
     * @param    array
     * @param    string
     * @param    bool
     * @return    CI_DB_query_builder
     */
    public function set_update_batch($key, $index = '', $escape = NULL)
    {
        $key = $this->_object_to_array_batch($key);

        if ( ! is_array($key))
        {
            return $this->display_error('db_batch_missing_data');
        }

        foreach ($key as $k => $v)
        {
            $index_set = FALSE;
            foreach ($v as $k2 => $v2)
            {
                if ($k2 === $index)
                {
                    $index_set = TRUE;
                }
            }

            if ($index_set === FALSE)
            {
                return $this->display_error('db_batch_missing_index');
            }

            $this->qb_set[] = $v;

            $this->qb_keys = $index;
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Update_Batch
     *
     * Compiles an update string and runs the query
     *
     * @param    string    the table to retrieve the results from
     * @param    array    an associative array of update values
     * @param    string    the where key
     * @return    int    number of rows affected or FALSE on failure
     */
    public function update_batch($table = '', $set = NULL, $index = NULL)
    {
        // Combine any cached components with the current statements
        $this->_merge_cache();

        if ($index === NULL)
        {
            if($this->qb_keys === NULL) {
                return ($this->db_debug) ? $this->display_error('db_must_use_index') : FALSE;
            }else {
                $index = $this->qb_keys;
            }
        }

        if ($set !== NULL)
        {
            $this->set_update_batch($set, $index);
        }

        if (count($this->qb_set) === 0)
        {
            return ($this->db_debug) ? $this->display_error('db_must_use_set') : FALSE;
        }

        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        // Batch this baby
        $affected_rows = 0;
        for ($i = 0, $total = count($this->qb_set); $i < $total; $i += 100)
        {
            $this->_update_batch($table, array_slice($this->qb_set, $i, 100), $index);
            $affected_rows += $this->affected_rows();
            $this->qb_where = array();
        }

        $this->_reset_write();
        return $affected_rows;
    }

    // --------------------------------------------------------------------

    /**
     * Update_Batch statement
     *
     * Generates a platform-specific batch update string from the supplied data
     *
     * @param	string	$table	Table name
     * @param	array	$values	Update data
     * @param	string	$index	WHERE key
     * @return	string
     */
    protected function _update_batch($table, $values, $index)
    {
        foreach ($values as $key => $val)
        {
            $where[$index] = $val[$index];

            $set = array();

            foreach (array_keys($val) as $field)
            {
                if ($field !== $index)
                {
                    $set[$field] = $val[$field];
                }
            }

            $update[] = $this->db->{$table}->update($where, $set, array('multiple' => true));
        }

        return $update;
    }

    // --------------------------------------------------------------------

    /**
     * Replace
     *
     * Compiles an replace into string and runs the query
     *
     * @param    string    the table to replace data into
     * @param    array    an associative array of insert values
     * @return    object
     */
    public function replace($table = '', $set = NULL)
    {
        // input update data if that was setup
        if ($set !== NULL)
        {
            $this->set($set);
        }

        // check insert data available
        if (count($this->qb_set) === 0)
        {
            return ($this->db_debug) ? $this->display_error('db_must_use_set') : FALSE;
        }else {
            // clone data
            $set = $this->qb_set;
            $this->qb_set = array();
        }

        if ($table === '')
        {
            if ( ! isset($this->qb_from[0]))
            {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }

            // get table name
            $table = str_replace('`','',$this->qb_from[0]);
        }

        // replace row and insert new record
        $this->db->{$table}->save($set);
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query. Returns boolean TRUE
     * upon successful execution of a "write" type query. Returns boolean
     * FALSE upon failure, and if the $db_debug variable is set to TRUE
     * will raise an error.
     *
     * @param    string $sql
     * @param    array $binds = FALSE        An array of binding data
     * @param    bool $return_object = NULL
     * @return    mixed
     */
    public function query($sql, $binds = false, $return_object = NULL)
    {
        if ($sql === '')
        {
            log_message('error', 'Invalid query: '.$sql);
            return ($this->db_debug) ? $this->display_error('db_invalid_query') : FALSE;
        }
        elseif ( ! is_bool($return_object))
        {
            $return_object = ! $this->is_write_type($sql);
        }

        // Save the query for debugging
        if ($this->save_queries === TRUE)
        {
            $this->queries[] = $sql;
        }

        $query = $this->simple_query($sql);

        return $query;
    }

    // --------------------------------------------------------------------

    /**
     * Simple Query
     * This is a simplified version of the query() function. Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @param    string    the sql query
     * @return    mixed
     */
    public function simple_query($sql)
    {
        if ( ! $this->connection)
        {
            $this->initialize();
        }

        return $this->db->execute($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Disable Transactions
     * This permits transactions to be disabled at run-time.
     *
     * @return	void
     */
    public function trans_off()
    {
        $this->display_error('CI MongoDB Driver do not support for transaction in current version! Please wait next version!');
    }

    // --------------------------------------------------------------------

    /**
     * Enable/disable Transaction Strict Mode
     * When strict mode is enabled, if you are running multiple groups of
     * transactions, if one group fails all groups will be rolled back.
     * If strict mode is disabled, each group is treated autonomously, meaning
     * a failure of one group will not affect any others
     *
     * @param	bool	$mode = TRUE
     * @return	void
     */
    public function trans_strict($mode = TRUE)
    {
        $this->display_error('CI MongoDB Driver do not support for transaction in current version! Please wait next version!');
    }

    // --------------------------------------------------------------------

    /**
     * Start Transaction
     *
     * @param	bool	$test_mode = FALSE
     * @return	void
     */
    public function trans_start($test_mode = FALSE)
    {
        $this->display_error('CI MongoDB Driver do not support for transaction in current version! Please wait next version!');
    }

    // --------------------------------------------------------------------

    /**
     * Complete Transaction
     *
     * @return	bool
     */
    public function trans_complete()
    {
        $this->display_error('CI MongoDB Driver do not support for transaction in current version! Please wait next version!');
    }

    // --------------------------------------------------------------------

    /**
     * Lets you retrieve the transaction flag to determine if it has failed
     *
     * @return	bool
     */
    public function trans_status()
    {
        $this->display_error('CI MongoDB Driver do not support for transaction in current version! Please wait next version!');
    }

    // --------------------------------------------------------------------

}
/**
 * End of Class Mongo_query_builder
 */