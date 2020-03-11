<?PHP

namespace plugin;
use main\main;

class mysql{
    protected $host = '127.0.0.1';
    protected $dbname = 'mysql';
    protected $name = 'root';
    protected $pass = 'root';
    protected $port = '3306';
    protected $link = NULL;

    protected $_where = '';
	protected $_order = '';
	protected $_limit = '';
	protected $_field = '*';

    public function __construct($mysql_config = array())
    {
        if(!class_exists("PDO")){
            throw new \Exception('不支持PDO,请先开启');
        }
        if(empty($mysql_config))
            $mysql_config = main::getConfig('mysql');
        if(!empty($mysql_config['host']))
            $this -> host = $mysql_config['host'];
        if(!empty($mysql_config['dbname']))
            $this -> dbname = $mysql_config['dbname'];
        if(!empty($mysql_config['name']))
            $this -> name = $mysql_config['name'];
        if(!empty($mysql_config['pass']))
            $this -> pass = $mysql_config['pass'];
        if(!empty($mysql_config['port']))
            $this -> port = $mysql_config['port'];

        try {
            $this -> dsn = 'mysql:host=' . $this -> host . ';dbname=' . $this -> dbname . ';port=' . $this -> port;
            $this -> link = new \PDO($this -> dsn, $this -> name, $this -> pass);
            $this -> link -> exec("set names utf8");
        } catch (PDOException $e) {
            throw new \Exception('数据库连接错误');
        }
    }

    public function query($sql) {
		$result = $this -> link -> query(trim($sql));
        if($result && $row = $result -> fetchAll(\PDO::FETCH_ASSOC))
            return $row;
        return false;
    }

    public function select($table='') {
		$sql = "select ".trim($this->_field)." from `".$table."` ".trim($this->_where)." ".trim($this->_order)." ".trim($this->_limit);
        $this->_clear();
        $result = $this -> link -> query(trim($sql));
        if($result && $row = $result -> fetchAll(\PDO::FETCH_ASSOC))
            return $row;
        return false;
    }

    public function get($table='') {
		$sql = "select ".trim($this->_field)." from `".$table."` ".trim($this->_where)." ".trim($this->_order)." ".trim($this->_limit);
        $this->_clear();
        $result = $this -> link -> query(trim($sql));
        if($result && $row = $result -> fetch(\PDO::FETCH_ASSOC))
            return $row;
        return false;
    }

    public function update($table, $data) {
        if (!trim($this->_where)) return false;
        if(count($data) != count($data, 1)) return false;
        $data = $this -> _dataFormat($data);
        if(empty($data)) return false;
        foreach($data as $k=>$v){
            $valArr[] = $k . '=' . $v;
        }

        $valStr = implode(',', $valArr);
        $sql = "update `" . $table . "` set ". trim($valStr) . ' ' . $this->_where;
        $this->_clear();
		return $this -> link -> exec(trim($sql));  
    }

    public function delete($table) {
		if (!trim($this->_where)) return false;
        $sql = 'delete from `' . $table . '` ' . $this->_where;
        $this -> _clear();
        return $this -> link -> exec(trim($sql));   
	}

	public function insert($table, $data) {
        if(is_array($data) && !empty($data))
        {
            $data = $this -> _dataFormat($data);
            $keys = implode(',', array_keys($data));
            $values = array_values($data);
            if(empty($values)) return false;
            $values_data = array();
            
            if(is_array($values[0]))
            {
                foreach ($values as $v1) {
                    foreach ($v1 as $k2 => $v2) {
                        $values_data[$k2][] = $this -> _addChar($v2);
                    }
                }
                foreach ($values_data as $key => $val) {
                    $values_data[$key] = '(' . implode(',', $val) . ')';
                }
                $values_data = implode(',', $values_data);
            }else{
                $values_data = '(' . implode(',', $values) . ')';
            }
            if(!empty($values_data))
            {
                $sql = "insert into ".$table."(" . $keys . ") values " . $values_data;
                return $this -> link -> exec(trim($sql));
            }
        }
		return false;
    }

    protected function _addChar($value){
        if(is_numeric($value))
            return $value;
        if(is_string($value))
            return '"' . $value . '"';
        if(main::getSysConfig('debug'))
        {
            throw new \Exception('数据库参数传入类型错误');
        }else{
            return '';
        }
    }

    protected function _dataFormat($data) {
        if (!is_array($data)) return false;
		$ret=array();
		foreach ($data as $key=>$val) {
			$key = '`' . trim($key) . '`';
			if (is_int($val)) { 
				$val = intval($val);
			} elseif (is_float($val)) { 
				$val = floatval($val);
			} elseif (is_string($val)) {
				$val = '"'.addslashes($val).'"';
			}
			$ret[$key] = $val;
		}
        return $ret;
    }

    protected function _clear() {
		$this->_where = '';
		$this->_order = '';
		$this->_limit = '';
		$this->_field = '*';
    }

    public function where($option, $logic = 'and')
    {
        $logic = (trim($logic) != 'and' ? 'or' : 'and');
		if (is_string($option)) {
			$this->_where = ' where ' . $option . ' ';
		}elseif(is_array($option)){
            $option_arr = array();
            foreach($option as $key => $val)
            {
                $option_arr[] = '`' . $key .'` = ' . $this -> _addChar($val);
            }
            $tem = join($logic, $option_arr);
            if($tem != '')
                $this->_where = ' where ' . $tem . ' ';
        }
        return $this;
    }

    public function order($option) {
		if (is_string($option)) {
			$this->_order = ' order by ' . $option . ' ';
		}
		elseif (is_array($option)) {
            $option_arr = array();
			foreach($option as $k => $v){
				$option_arr[] = $k . ' ' . $v;
            }
            $tem = join(',', $option_arr);
            if($tem != '')
                $this->_order = ' order by ' . $tem . ' ';;
		}
		return $this;
    }
    
    public function limit($page, $pageSize = 1) {
        $pageval = intval( ($page - 1) * $pageSize);
        if($pageval < 0)
            $pageval = 0;
		$this -> _limit = " limit ".$pageval.",".$pageSize . ' ';;
		return $this;
    }

    public function field($field){
        if (is_string($field)) {
			$this->_field = ' ' . $field . ' ';
		}
		elseif(is_array($field)) {
            $tem = join('`,`', $field);
            if($tem == '')
                $this->_field = ' `' . $tem . '` ';
        }
		return $this;
    }
    
    public function beginTransaction() {
		$this -> link -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this -> link -> beginTransaction();
        return $this;
    }

    public function commit() {
        $this -> link -> commit();
        return $this;
    }

    public function rollBack() {
        $this -> link -> rollBack();
        return $this;
    }

    public function lastInsertId() {
        return $this -> link -> lastInsertId();
    }
}