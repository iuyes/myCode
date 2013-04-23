<?php
/*
 *	pdo.class.php  
 */
class dbPdo
{
	public static $dbhost = 'localhost';
	public static $dbport = '3306';
	public static $dbname;
	public static $dbuser = 'root';
	public static $dbpass;
	public static $charset = 'utf8';
	public static $stmt = null;
	public static $DB = null;
	//是否长连接
	public static $connect = true;
	
	public $querycount = 0;
	//public $debugsql ;
	public static $debug = FALSE;
	
	public function __construct()
	{
		self::$dbhost = DBHOST;
		self::$dbport = DBPORT;
		self::$dbname = DBNAME;
		self::$dbuser = DBUSER;
		self::$dbpass = DBPASS;
		self::$connect = CONNECT;
		self::$charset = CHARSET;
		self::connect();
		self::$DB->query('SET NAMES ' . self::$charset);
	}
	
	/*
	 * 作用:链接数据库
	 */
	public function connect()
	{
		try
		{
			self::$DB = new PDO('mysql:host=' . self::$dbhost . ';port=' . self::$dbport . ';dbname=' . self::$dbname, self::$dbuser, self::$dbpass, array(
				PDO::ATTR_PERSISTENT => self::$connect
			));
		} catch (PDOException $e)
		{
			self::sqlError($e->getMessage());
		}
	}
	
	/*
	 * 作用:获取当前库的所有表名
	 * 返回:当前库的所有表名
	 * 类型:数组
	 */	
	public function getTablesName()
	{
		self::$stmt = self::$DB->query('SHOW TABLES FROM ' . self::$dbname);
		$result = self::$stmt->fetchAll(PDO::FETCH_NUM);
		self::$stmt = null;
		return $result;
	}
	
	/*
	 * 作用:获取数据表里的字段
	 * 返回:表字段结构
	 * 类型:数组
	 */	
	public function getFields($table)
	{
		self::$stmt = self::$DB->query("DESCRIBE $table");
		$result = self::$stmt->fetchAll(PDO::FETCH_ASSOC);
		self::$stmt = null;
		return $result;
	}
	
	/*
	 * 作用:获取所有数据
	 * 返回:表内记录
	 * 类型:二维数组
	 * 参数:$db->fetAll('$table',$condition = '',$field = '*',$orderby = '',$sort = '',$limit = '',$where='')
	 */	
	public function fetAll($table, $condition = null, $field = '*', $orderby = false, $sort = false, $limit = false, $where = false)
	{
		
		$sql = "SELECT {$field} FROM `{$table}`";
		false !== ($con = self::getCondition($condition, $where)) ? $sql .= $con : '';
		$sql .= ($orderby) ? " ORDER BY $orderby" : '';
		$sql .= ($sort) ? " $sort" : ' ';
		$sql .= ($limit) ? " LIMIT $limit" : '';
		return self::_fetch($sql, $type = '1');
	}

	/*
	 * 作用:获取所有数据
	 * 返回:表内记录
	 * 类型:二维数组
	 * 参数:select * from table
	 */	
	public function getAll($sql, $type = PDO::FETCH_ASSOC)
	{
		return self::_fetch($sql, $type = '1');
	}
	
	/*
	 * 作用:获取单行数据
	 * 返回:表内记录
	 * 类型:数组
	 * 参数:$db->fetOne($table,$condition = null,$field = '*',$where ='')
	 */	
	public function fetOne($table, $condition = null, $field = '*', $where = false)
	{
		$sql = "SELECT {$field} FROM `{$table}`";
		false !== ($con = self::getCondition($condition, $where)) ? $sql .= $con : '';
		return self::_fetch($sql, $type = '0');
	}
	
	/*
	 * 作用:获取单行数据
	 * 返回:表内记录
	 * 类型:数组
	 * 参数:select * from table where id='1'
	 */	
	public function getOne($sql)
	{
		return self::_fetch($sql, $type = '0');
	}
	
	/*
	 * 获取记录总数
	 * 返回:记录数
	 * 类型:数字
	 * 参数:$db->fetRow('$table',$condition = '',$where ='');
	 */	
	public function fetRow($table, $condition = null, $field = '*', $where = false)
	{
		$sql = "SELECT COUNT({$field}) AS num FROM `$table`";
		false !== ($con = self::getCondition($condition, $where)) ? $sql .= $con : '';
		return self::_fetch($sql, $type = '2');
	}
	
	/*
	 * 获取记录总数
	 * 返回:记录数
	 * 类型:数字
	 * 参数:select count(*) from table
	 */	
	public function getRow($sql = NULL)
	{
		return self::_fetch($sql, $type = '2');
	}
	
	/*
	 * 作用:插入数据
	 * 返回:表内记录
	 * 类型:数组
	 * 参数:$db->insert('$table',array('title'=>'Zxsv'))
	 */	
	public function insert($table, $args)
	{
		$sql = "INSERT INTO `$table` SET ";
		$code = self::getCode($table, $args);
		$sql .= $code;
		if (self::execute($sql))
		{
			return self::getLastId();
		}
		return false;
	}

	/*
	 * 修改数据
	 * 返回:记录数
	 * 类型:数字
	 * 参数:$db->update($table,array('title'=>'Zxsv'),array('id'=>'1'),$where ='');
	 */	
	public function update($table, $args, $condition = null, $where = false)
	{
		$code = self::getCode($table, $args);
		$sql = "UPDATE `$table` SET ";
		$sql .= $code;
		false !== ($con = self::getCondition($condition, $where)) ? $sql .= $con : '';
		return self::execute($sql);
	}
	
	/*
	 * 作用:删除数据
	 * 返回:表内记录
	 * 类型:数组
	 * 参数:$db->delete($table,$condition = null,$where ='')
	 */	
	public function delete($table, $condition = null, $where = false)
	{
		$sql = "DELETE FROM `$table`";
		false !== ($con = self::getCondition($condition, $where)) ? $sql .= $con : exit('条件错误!');
		return self::execute($sql);
	}
	
	/*
	 * 作用:获得最后INSERT的主键ID
	 * 返回:最后INSERT的主键ID
	 * 类型:数字
	 */	
	public function getLastId()
	{
		return self::$DB->lastInsertId();
	}
	
	/*
	 * 作用:执行INSERT\UPDATE\DELETE
	 * 返回:执行语句影响行数
	 * 类型:数字
	 */	
	public function execute($sql)
	{
		self::getPDOError($sql);
		$this->querycount++;
		return self::$DB->exec($sql);
	}
	
	/*
	 * 获取要操作的数据
	 * 返回:合并后的SQL语句
	 * 类型:字符串
	 */	
	private function getCode($table, $args)
	{
		$code = '';
		if (is_array($args))
		{
			foreach ($args as $k => $v)
			{
				if ($v == '')
				{
					continue;
				}
				$code .= "`$k`='$v',";
			}
		}
		$code = substr($code, 0, -1);
		return $code;
	}
	
	/*
	 * 获取查询条件，可为数组或字符串，为数组时where为or,and,in,like,=!，可自由增加
	 * 返回:合并后的SQL语句
	 * 类型:字符串
	 */	
	private function getCondition($condition = false, $where = false)
	{
		if ($condition != '')
		{
			$con = ' WHERE';
			if (is_array($condition))
			{
				$i = 0;
				foreach ($condition as $k => $v)
				{
					if ($i != 0)
					{
						switch ($where)
						{
							case 'or' :
								$con .= " OR $k = '$v'";
								break;
							default :
								$con .= " AND $k = '$v'";
								break;
						}
					}
					else
					{
						switch ($where)
						{
							case 'in' :
								$con .= " $k IN ($v)";
								break;
							case 'like' :
								$con .= " $k LIKE '%" . $v . "%'";
								break;
							case '!=' :
								$con .= " $k != '$v'";
								break;
							default :
								$con .= " $k = '$v'";
								break;
						}
					}
					$i++;
				}
			}
			elseif (is_string($condition))
			{
				$con .= " $condition ";
			}
			else
			{
				return false;
			}
			return $con;
		}
		return false;
	}
	
	/*
	 * 执行具体SQL操作
	 * 返回:运行结果
	 * 类型:数组或数字
	 */		
	private function _fetch($sql, $type)
	{
		$result = array();
		self::$stmt = self::$DB->query($sql);
		self::getPDOError($sql);
		self::$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$this->querycount++;
		switch ($type)
		{
			case '0' :
				$result = self::$stmt->fetch();
				break;
			case '1' :
				$result = self::$stmt->fetchAll();
				break;
			case '2' :
				if ($sql)
				{
					$result = self::$stmt->fetchColumn();
				}
				elseif (self::$stmt)
				{
					$result = self::$stmt->rowCount();
				}
				else
				{
					$result = 0;
				}
				break;
		}
		self::$stmt = null;
		return $result;
	}
	
	/*
	 * 捕获PDO错误信息
	 * 返回:出错信息
	 * 类型:字符串
	 */	
	private function getPDOError($sql)
	{
		self::$debug ? self::errorfile($sql) : '';
		if (self::$DB->errorCode() != '00000')
		{
			$info = (self::$stmt) ? self::$stmt->errorInfo() : self::$DB->errorInfo();
			echo (self::sqlError('mySQL Query Error', $info[2], $sql));
			exit();
		}
	}
	
	/*
	 * 设置是否为调试模式
	 */		
	public function setDebugMode($mode = TRUE)
	{
		return ($mode == TRUE) ? self::$debug = TRUE : self::$debug = FALSE;
	}
	
	/*
	 *事务开始
	 */	
	public function autocommit()
	{
		self::$DB->beginTransaction();
	}
	
	/*
	 *事务提交
	 */	
	public function commit()
	{
		self::$DB->commit();
	}
	
	/*
	 *事务回滚
	 */	
	public function rollback()
	{
		self::$DB->rollback();
	}
	
	private function errorfile($sql)
	{
		echo $sql . '<br />';
		$errorfile = 'dberrorlog.php';
		$sql = str_replace(array(
			"\n", 
			"\r", 
			"\t", 
			"  ", 
			"  ", 
			"  "
		), array(
			" ", 
			" ", 
			" ", 
			" ", 
			" ", 
			" "
		), $sql);
		if (!file_exists($errorfile))
		{
			$fp = file_put_contents($errorfile, "<?PHP exit('Access Denied'); ?>\n" . $sql);
		}
		else
		{
			$fp = file_put_contents($errorfile, "\n" . $sql, FILE_APPEND);
		}
	
	}
	
	/*
	 * 作用:运行错误信息
	 * 返回:运行错误信息和SQL语句
	 * 类型:字符
	 */	
	private function sqlError($message = '', $info = '', $sql = '')
	{
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">';
		$html .= '<head><title>mySQL Message</title><style type="text/css">body {margin:0px;color:#555555;font-size:12px;background-color:#efefef;font-family:Verdana} ol {margin:0px;padding:0px;} .w {width:800px;margin:100px auto;padding:0px;border:1px solid #cccccc;background-color:#ffffff;} .h {padding:8px;background-color:#ffffcc;} li {height:auto;padding:5px;line-height:22px;border-top:1px solid #efefef;list-style:none;overflow:hidden;}</style></head>';
		$html .= '<body><div class="w"><ol>';
		if ($message)
		{
			$html .= '<div class="h">' . $message . '</div>';
		}
		$html .= '<li>Date: ' . date('Y-n-j H:i:s', time()) . '</li>';
		if ($sql)
		{
			$html .= '<li>SQLID: ' . $info . '</li>';
		}
		if ($sql)
		{
			$html .= '<li>Error: ' . $sql . '</li>';
		}
		$html .= '</ol></div></body></html>';
		return $html;
	}
	
	/*
	*关闭数据连接
	*/	
	public function close()
	{
		self::$DB = null;
	}
	
	/*
	*析构函数
	*/	
	public function __destruct()
	{
	
	}

}
?>
