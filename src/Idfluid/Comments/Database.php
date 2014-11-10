<?php

namespace Idfluid\Comments;

class Database
{
	private $result;
	private $prefix;
	protected $id;

	public function __construct(){
		if(\Comments::config('db_prefix')){
    		$this->prefix = \Comments::config('db_prefix');
    	}
    	else {
    		$this->prefix = null;
    	}
	}
	#DB SELECT
    public function select($table, $rows = '*', $where = null, $order = null, $limit=null)
    {
    	\Config::set('database.fetch', \PDO::FETCH_ASSOC);
        $q = 'select '.$rows.' from '.$this->prefix.$table;
        if($where != null) $q .= ' where '.$where;
        if($order != null) $q .= ' order by '.$order;
		if($limit != null) $q .= ' limit '.$limit;

        $query = \DB::select($q);
        if($query){
        	$this->numResults = count($query);
        	$i = 0;
        	$this->result = array();
			foreach($query as $r) {
				$key = array_keys($r);
				for($x = 0; $x < count($key); $x++){
					if($this->numResults > 1)
						$this->result[$i][$key[$x]] = $r[$key[$x]];
					else if($this->numResults < 1)
						$this->result = null;
					else 
						$this->result[1][$key[$x]] = $r[$key[$x]];
				}
				$i++;
			}
			return true;
        }
        else {
        	return false;
        }
    }
	public function ClearResult(){
		$this->result = null;
	}
	public function getResult(){
		return $this->result;
	}
	#DB INSERT
	public function insert($table, $fields) {
	    $insert = \DB::table($table)->insertGetId($fields);
	    $this->id = $insert;
	    if($insert) return true;
	    	else return false;
	}
	function get_id() {
		return $this->id;
	}
	#DB UPDATE
	function update($table,$rows,$where=null,$limit = null) {
		$update = 'UPDATE '.$table.' SET ';
	  	$keys = array_keys($rows);
	   	for($i = 0; $i < count($rows); $i++) {
			if(is_string($rows[$keys[$i]]))
	    		$update .= $keys[$i].'="'.mysql_real_escape_string(str_replace("\n", " ",$rows[$keys[$i]])).'"';
	        else
				$update .= $keys[$i].'='.$rows[$keys[$i]];
	        if($i != count($rows)-1)
				$update .= ',';
		}
		if($where!=null)
	    	$update .= ' WHERE '.$where;
		if($limit!= null)
	    	$update .= ' LIMIT '.$limit;
	    if(@mysql_query($update)) return true;
	        else return false;
	}
	#DB DELETE
	function delete($table,$where = null,$limit = null) {
		$delete = 'DELETE FROM '.$table;
	    if($where!= null)
	    	$delete .= ' WHERE '.$where;
		if($limit!= null)
	    	$delete .= ' LIMIT '.$limit;
		if(@mysql_query($delete))
	    	return true;
	    else
	    	return false;
	}

}
?>