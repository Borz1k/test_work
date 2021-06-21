<?
namespace DB;

Class MySQL{
  public  $select_key                 = false; /* Использовать ключ из поля */
  public  $select_value               = false;
  public  $select_group               = false; /* Использовать ключ из поля для групировки */
  public  $select_group_name          = false; /* Использовать ключ из поля для названия групировки */
  public  $select_group_field         = false;
  public  $select_group_unset_field   = false;
  public  $select_group_sub           = '_sub';
  public	$select_my_list			        = array();
  public  $select_active   		        = false;
  public  $is_stripslashes            = true;
  public 	$select_unserialize         = false; /* Масиив полей для unserialize*/
  
  public  $insert_id      = 0; /* Последний ID после INSERT*/
  public  $affected_rows  = 0; /* Кол-во затронутых рядов после UPDATE */
  public  $count          = 0; /* Кол-во рядов после SELECT */
  public  $query          = null; /* Последний выполняемый запрос */
  private $Link           = null; /* Ссылка на подключеную бд */
  private $Result         = null; /* Результат последнего выполняемого запроса */
  private $dataTree       = false; /* Массив данных для обработки дерева */
  
  public $log                     = array(); /* Лог запросов (Если в config.php debug=true) */
  public $error                   = null;  /* Ошибка запроса */
  public $error_arr               = array(); /* Все ошибки запросов */
  public $select_get_data 				= false; /* Получить значения полей */
  public $select_get_data_result 	= false; /* Результат получения значения полей */
  public $_log                    = true;
  
  public function __construct($config = array()){
  	if(empty($config['name']) OR empty($config['user']) OR empty($config['host'])){
      die('Параметры подключание к MySQL не определены.');
    }
    $this->Link = @mysqli_connect($config['host'], $config['user'], $config['password']) or die("Извините, сервер временно недоступен! Попробуйте зайти попозже. <br> ".mysqli_connect_error());
		@mysqli_query($this->Link, 'SET NAMES "utf8"');
    @mysqli_select_db($this->Link, $config['name']) or die("Ошибка подключения MySQL. <br> ".mysqli_error($this->Link));      
	}
  
  public function Select($table, $fields = "*", $cond = false, $end = false){
     $this->select_get_data_result = false;
     
		 $fields  = empty($fields) ? '*' : $fields;
     $cond    = empty($cond) ? false : $cond;
     $end     = empty($end) ? false : $end;
     $this->run("SELECT {$fields} FROM {$table}".($cond ? " WHERE ".$cond : '').($end ? ' '.$end : ''));
     $this->dataTree = false;
     return $this->FetchAllAssoc();
  }
  
  public function SelectSet($table, $fields = "*", $cond = false, $end = false,$count = 10, $page = 1){
     $fields  = empty($fields) ? '*' : $fields;
     $cond    = empty($cond) ? false : $cond;
     $end     = empty($end) ? false : $end;
     $query = "SELECT {$fields} FROM {$table}".($cond ? " WHERE ".$cond : '').($end ? ' '.$end : '');
      if(strstr($query, 'DISTINCT')){
        $rep = array(
            "/SQL_CALC_FOUND_ROWS/i"=>"",
            "/DISTINCT/i"=>"",
            "/SELECT/i"=>"SELECT DISTINCT SQL_CALC_FOUND_ROWS");
        $query  = preg_replace(array_keys($rep), array_values($rep), $query,1);
      }else{
        $rep    = array(
          "/SQL_CALC_FOUND_ROWS/i"=>"",
          "/SELECT/i"=>"SELECT SQL_CALC_FOUND_ROWS");
        $query  = preg_replace(array_keys($rep), array_values($rep), $query,1);
      }
      $query.=" LIMIT ".intval(($page-1)*$count).", ".intval($count); 
      $this->run($query);
      $result = $this->FetchAllAssoc();
      if($result){
        $this->run("SELECT FOUND_ROWS() as c");
        $db = $this->FetchAssoc();
        return array('result'=>$result,'pages'=>MyCMS::call('Func')->PagesList($page,$db['c'],$count),'count'=>count($result),'NumRows'=>$db['c']); 
      }else{
      	$this->resetVar();
        return false;
      }
  }
  
  // function SelectTree($table, $fields = "*", $cond = false, $end = false,$primary_key = 'id', $parent_key = 'parent',$recursive = true,$max_level = false,$set_url=false,$empty_group_name=false){
  //    $fields  = empty($fields) ? '*' : $fields;
  //    $cond    = empty($cond) ? false : $cond;
  //    $end     = empty($end) ? false : $end;
     
  //    $query = "SELECT {$fields} FROM {$table}".($cond ? " WHERE ".$cond : '').($end ? ' '.$end : '');
     
  //    if(empty($primary_key)){
  //      $this->error_arr[] = array('query'=>$query,'error'=>"Укажите параметр \"primary_key\""); 
  //    }elseif(empty($parent_key)){
  //      $this->error_arr[] = array('query'=>$query,'error'=>"Укажите параметр \"parent_key\""); 
  //    }else{
  //      $select_group = $this->select_group;
  //      $this->select_group = false;
  //      $this->select_key = $primary_key; 
  //      $this->run($query);            
  //      $this->dataTree = array(
  //       'parent_key'=>$parent_key,
  //       'primary_key'=>$primary_key,
  //       'select_group'=>$select_group,
  //       'max_level'=>$max_level,
  //       'set_url'=>$set_url,
  //       'group_name'=>$this->select_group_name,
  //       'empty_group_name'=>$empty_group_name,
  //       'select_active'=>$this->select_active,
  //       'one_empty_group_value'=>-1);
  //      if($get = $this->FetchAllAssoc()){ 
  //       //prr($get);
  //       //die(); 
  //       unset($this->dataTree);
  //       $get['recursive'] = $recursive;
  //       $this->resetVar();
  //       return MyCMS::call('Func')->Tree($get);
  //      }else{
  //      	$this->resetVar();
  //       return false;
  //      }
  //    }
  // }
  
  
  public function SelectRecord($table, $fields = "", $cond = false, $end = false){
     $this->count    = 0;
     $fields  = empty($fields) ? '*' : $fields;
     $cond    = empty($cond) ? false : $cond;
     $end     = empty($end) ? false : $end;
     $this->run("SELECT {$fields} FROM {$table}".($cond ? " WHERE ".$cond : '').($end ? ' '.$end : '')." LIMIT 1");
     return $this->FetchAssoc();
  }
  
  public function run($query){
    $this->insert_id      = 0;
    $this->affected_rows  = 0; 
    $this->count          = 0;
    $this->query          = $query; 
    
    $start_time      = $this->get_sec();  
    $this->Result    = mysqli_query($this->Link, $query);
    $exec_time       = $this->get_sec() - $start_time;

    if(mysqli_error($this->Link)){ 
      $this->error_arr[] = array('query'=>$query,'error'=>mysqli_error($this->Link)); 
      $this->error = mysqli_error($this->Link);
      $this->Result = false;
      return false;
    }else{
      if($this->_log){
        $this->log[] = array('query'=>$query,'time'=>sprintf("%01.3f", $exec_time));
      }
    } 
    $_type = explode(" ",trim($query));
    $_type = trim(strtolower($_type[0]));
    if($_type=="insert"){
      $this->insert_id = mysqli_insert_id($this->Link);
    }elseif($_type=="update"){
      $this->affected_rows = mysqli_affected_rows($this->Link);
    }  
    return $this->Result;

  }
  
	public function NumRows($result = NULL){
    if ( is_null($result) ) { $result = $this->Result; }
		return $result ? mysqli_num_rows($result) : 0;
	} 
  
  public function FetchAssoc($result = NULL){
    if (is_null($result)){ 
      $result = $this->Result; 
      if($result==false){
        $this->resetVar();
        return false;   
      }
    }
    $array = mysqli_fetch_assoc($result);
    if($array){
      mysqli_free_result($result);
      $this->count = 1;
      array_walk($array,array($this,'_stripslashes'));
      $this->resetVar();
      return $array;
    } 
    $this->resetVar();  
    return false;
  }
  
	public function FetchAllAssoc($result = NULL){
    if (is_null($result)){ 
      $result = $this->Result;
      if($result==false){
        $this->resetVar();
        return false;
      }  
    }

    $i = 0;
    $count = 0;
    $list = array();
		$groupField = $this->select_group_field ? explode(",",$this->select_group_field) : false;
		while ($row=mysqli_fetch_assoc($result)){ 
			
			if($this->select_key && !isset($row[$this->select_key])){
				$this->select_key = false;
			}
			
			$i = ($this->select_key && isset($row[$this->select_key])) ? $row[$this->select_key] : $i;
      array_walk($row,array($this,'_stripslashes'));
      if($this->select_group!=false){  
        if($this->dataTree!=false){          
          $list[$row[$this->select_group]][$i] =  $row;
        }else{

					if(is_array($this->select_my_list) && count($this->select_my_list)>0){
						$list[$row[$this->select_group]]['name'] = $this->select_my_list[$row[$this->select_group]] ? $this->select_my_list[$row[$this->select_group]] : $row[$this->select_group];
					}else{
          	$list[$row[$this->select_group]]['name'] = $this->select_group_name ? $row[$this->select_group_name] : $row[$this->select_group];
          }
          
					if($groupField){
            foreach($groupField as $v){
              $list[$row[$this->select_group]][trim($v)] = $row[trim($v)];
            }
          }
          if($this->select_value){
        	  $list[$row[$this->select_group]][$this->select_group_sub][$i] =  $row[$this->select_value];
        	}else{
          	$list[$row[$this->select_group]][$this->select_group_sub][$i] =  $row;
          }
          if($this->select_group_unset_field){
						foreach($groupField as $v){
							unset($list[$row[$this->select_group]][$this->select_group_sub][$i][$v]);
						}
					}
        }        
      }else{
        if($this->select_value){
          $list[$i] =  $row[$this->select_value];
        }else{
          $list[$i] =  $row;
        }
      }
      if(isset($this->dataTree) && $this->dataTree!=false){
        $this->dataTree['_link'][($row[$this->dataTree['parent_key']]==$row[$this->dataTree['primary_key']] ? 0 : $row[$this->dataTree['parent_key']])][$i] = $row[$this->dataTree['primary_key']];
        $this->dataTree['items'][$row[$this->dataTree['primary_key']]] = ($row[$this->dataTree['parent_key']]==$row[$this->dataTree['primary_key']] ? 0 : $row[$this->dataTree['parent_key']]);
      }              
			if($this->select_key==false) $i++;
      $count++;
      
      if($this->select_get_data){
				if(is_array($this->select_get_data)){
					foreach($this->select_get_data as $_datakey=>$_dataval){
						$this->select_get_data_result[$_datakey][$row[$_dataval]] = $row[$_dataval]; 
					}
				}
			}
		}

		mysqli_free_result($result);
    if(isset($this->dataTree) && $this->dataTree!=false){
      $this->dataTree['data'] = $list;
      unset($list);
      $list = array();
    }
    $this->resetVar();
    $this->count = $count;
    return ((isset($this->dataTree) && $this->dataTree!=false && $count>0) ? $this->dataTree : (count($list)>0 ? $list : false));
	}
  
	public function Quote($string, $quote_char = "'"){
		return $quote_char.mysqli_real_escape_string($this->Link, $string).$quote_char;
	}
  
  public function Update($table, $rows = "", $cond = false, $quote=true){
    if(is_array($rows)){
      $set = array();
      foreach($rows as $key=>$val){
      	if($val==="SQL_TIME_NOW()"){
        	$set[] = "{$key}=NOW()";
        }else{
				  $set[] = "{$key}=".($quote ? $this->Quote($val) : $val);
				}
      }
      $set = implode(", ",$set);
      
    }else{
      $set = $rows;
    }
    if(empty($set)){ return false; } 
    return $this->run("UPDATE {$table} SET ".$set.($cond ? ' WHERE '.$cond : '')."");
  }  
       
  public function Insert($table,$rows='',$quote=true){
    if(is_array($rows)){
      $set = array();
      foreach($rows as $key=>$val){
      	if($val==="SQL_TIME_NOW()"){
        	$set[] = "{$key}=NOW()";
        }else{
				  $set[] = "{$key}=".($quote ? $this->Quote($val) : $val);
				}
      }
      $set = implode(", ",$set);      
    }else{
      $set = $rows;
    } 
    if(empty($set)){ return false; }    
    $res = $this->run("INSERT INTO {$table} SET ".$set);
    $insert_id = mysqli_insert_id($this->Link);  
    return $insert_id>0 ? $insert_id : ($res);
  }
  
  public function  _stripslashes(&$value, $key){
  
    if($this->select_unserialize){
			if(in_array($key,$this->select_unserialize)){
       // prr($value);
        $value = $value ? unserialize($value) : $value;
      }else{
        if($this->is_stripslashes) $value = stripslashes($value);
      }
    }else{
      if($this->is_stripslashes) $value = stripslashes($value);
    }
  }
  
  public function unserialize(){
    if (!func_num_args()){
        return false;
    }
    $args = func_get_args();
    if(is_array($args[0])){
      $this->select_unserialize = $args[0]; 
    }else{
      $this->select_unserialize = $args ; 
    }
  }
  
	public function ListFields($tablename){
		$cols = array();
		$res = mysqli_query($this->Link, "SHOW FULL COLUMNS FROM $tablename");
		return $this->FetchAllAssoc($res);
	}
	
	public function setDataResult($param = false){
		$this->select_get_data 		 		= false;
		$this->select_get_data_result = false;
		if($param && is_array($param) && count($param)>0){
			$this->select_get_data = $param;
		}
	}
	
	public function getDataResult(){
		$select_get_data_result = $this->select_get_data_result;
		$this->select_get_data 		 		= false;
		$this->select_get_data_result = false;
		return $select_get_data_result;
	}
  
  private function resetVar(){
    $this->select_key          = false;
    $this->select_value        = false;
    $this->select_group        = false;
    $this->select_group_name   = false;
    $this->select_unserialize  = false;
    $this->select_group_field  = false;
    $this->select_group_unset_field = false;
    $this->select_group_sub    = '_sub';
    $this->select_active  		 = false;
    $this->select_my_list			 = false;
    $this->select_get_data 		 = false;
  }

  private function get_sec(){
      $mtime = microtime();
      $mtime = explode(" ",$mtime);
      $mtime = $mtime[1] + $mtime[0];
      return $mtime;
    }
  
  /* Отключение */
	public function Dissconnect(){
		@mysqli_close($this->Link) or die("Ошибка MySQL Dissconnect.".mysqli_error($this->Link));
	}
  
	public function __destruct(){
		$this->Dissconnect();
	}

}
?>