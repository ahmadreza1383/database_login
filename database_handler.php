<?php

define('DB_SERVER', 	'localhost');
define('DB_DATABASE', 	'bashari');
define('DB_USERNAME', 	'root');
define('DB_PASSWORD', 	'');



  class DatabaseHandler
{
	const debug 			= false;
	const DB_PERSISTENCY 	= true;

	private static $_mHandler;
	private function __construct(){
		self::Close();
	}
	private static function GetHandler(){
		if(!isset(self::$_mHandler)){
			try{
				self::$_mHandler =
					new PDO('mysql:host='. DB_SERVER .';dbname='. DB_DATABASE, DB_USERNAME, DB_PASSWORD,
						array(
							PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
							PDO::ATTR_PERSISTENT => self::DB_PERSISTENCY,
							PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
						)
					);
			} catch (PDOException $e) {
				self::Close();
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}
		return self::$_mHandler;
	}
	public static function Close(){
		self::$_mHandler = null;
	}
	public static function Execute($sqlQuery, $params = null){
		if(self::debug) echo $sqlQuery .'<br /><br />';
		try{
			$database_handler = self::GetHandler();
			$statement_handler = $database_handler->prepare($sqlQuery);
			$statement_handler->execute($params);
			$statement_handler->closeCursor();
			self::Close();
			return $statement_handler->rowCount();
		} catch(PDOException $e) {
			self::Close();
			trigger_error($e->getMessage(), E_USER_ERROR);
			return false;
		}
	}
	public static function GetAll($sqlQuery, $params = null, $fetchStyle = PDO::FETCH_ASSOC){
		if(self::debug) echo $sqlQuery .'<br /><br />';
		$result = null;
		try{
			$database_handler = self::GetHandler();
			$statement_handler = $database_handler->prepare($sqlQuery);
			$statement_handler->execute($params);
			$result = $statement_handler->fetchAll($fetchStyle);
			$statement_handler->closeCursor();
			self::Close(); 
		} catch(PDOException $e){
			self::Close();
			trigger_error($e->getMessage(), E_USER_ERROR);
			return false;
		}
		return $result;
	}
	public static function GetRow($sqlQuery, $params = null, $fetchStyle = PDO::FETCH_ASSOC){
		if(self::debug) echo $sqlQuery .'<br /><br />';
		$result = null;
		try{
			$database_handler = self::GetHandler();
			$statement_handler = $database_handler->prepare($sqlQuery);
			$statement_handler->execute($params);
			$result = $statement_handler->fetch($fetchStyle);
			$statement_handler->closeCursor();
			self::Close(); 
		} catch(PDOException $e) {
			self::Close();
			trigger_error($e->getMessage(), E_USER_ERROR);
			return false;
		}
		return $result;
	}
	public static function GetOne($sqlQuery, $params = null){
		if(self::debug) echo $sqlQuery .'<br /><br />';
		$result = null;
		try{
			$database_handler = self::GetHandler();
			$statement_handler = $database_handler->prepare($sqlQuery);
			$statement_handler->execute($params);
			$result = $statement_handler->fetch(PDO::FETCH_NUM);
			$statement_handler->closeCursor();
			$result = $result[0];
			self::Close(); 
		} catch(PDOException $e) {
			self::Close();
			trigger_error($e->getMessage(), E_USER_ERROR);
			return false;
		}
		return $result;
	}

}


  class Table
{
	
	public $where;
	public $params;
	
	/*
		تعریف جدول ارتباط با دیتابیس
		
		پارامتر هایی که میگیرد
		@parameters
			$table_name = (string); 
		
		@return 
			$this
		
	*/
	public function __construct($table_name){
		
		$this->table_name = $table_name;
		
		return $this;
	}
	
	/* 
		ذخیره داده ها در جدول 
		
		پارامتر هایی که میگیرد
		@parameters
			$params = array(); 
		
		تعداد رکورد هایی که مورد تاثیر قرار گرفته اند را برمیگرداند
		@return 
			row count
	*/
	public function insert($params){
		$sql = "INSERT INTO `{$this->table_name}` SET ". $this->createSqlFields($params);
		
		$params = $this->createParams($params);
		
		DatabaseHandler::Execute($sql, $params);
	}
	
	/* 
		گرفتن همه رکرود های یک جدول
		
		این تابع هیچ پارامتری نمیکیرد
			
		تمام رکورد های جدول را برمیگرداند
		@return 
			$row
	*/
	public function selectAll($limit = 20, $start = 0){ 
		$sql = "SELECT * FROM `{$this->table_name}` ORDER BY `id` DESC LIMIT {$start}, {$limit}";
		return DatabaseHandler::GetAll($sql);
	}
	
	/* 
		گرفتن یک رکورد جدول با داشتن آی‌دی آن 
		
		این تابع یک آی‌دی میپذیرد که شماره یک رکورد در دیتابیس است
		@parameters
			$id = (int); 
		
		این تابع یک رکورد از جدول را برمیگرداند
		@return 
			$this
	
	*/
	public function select($id){
		$sql = "SELECT * FROM `{$this->table_name}` WHERE `id` = :id LIMIt 0, 1";
		$params = array(
			':id' => $id
		);
		return DatabaseHandler::GetRow($sql, $params);
	}
	
	/*
		
	*/
	public function where($params){
		
		$this->params = $params;
		
		$this->sql = "
			SELECT * FROM `{$this->table_name}` 
			WHERE 
				". $this->createSqlFields($params);
		
		return $this;
	}
	
	/*
		
	*/
	public function order($sort = 'DESC', $field = 'id'){
		
		$this->sql .= " ORDER BY `{$field}` {$sort} ";
		
		return $this;
		
	}
	
	/*
		
	*/
	public function limit($limit = 5, $start = 0){
		
		$this->sql = " LIMIT {$start}, {$limit} ";
		
		return $this;
		
	}
	
	/*
		
	*/
	public function get(){
		
		$this->params = $this->createParams($this->params);
		
		return (array) DatabaseHandler::GetAll($this->sql, $this->params);
	}
	
	/*
		
	*/
	public function first($params){
		$sql = "SELECT * FROM `{$this->table_name}` WHERE ". $this->createSqlFields($params) ." ORDER BY `id` ASC LIMIT 0, 1";
		
		$params = $this->createParams($params);
		
		return DatabaseHandler::GetRow($sql, $params);
	}
	
	/*
		
	*/
	public function last($params){
		$sql = "SELECT * FROM `{$this->table_name}` WHERE ". $this->createSqlFields($params) ." ORDER BY `id` DESC LIMIT 0, 1";
		
		$params = $this->createParams($params);
		
		return DatabaseHandler::GetRow($sql, $params);
	}
	
	/* 
		به روز رسانی یک رکورد
		
		
		@parameters
			$params = (array) پارامترز که آرایه ای از یک رکورد است
			$id = (int)  آی‌دی، که شماره یک رکورد در دیتابیس است
		
		تعداد رکورد هایی که مورد تاثیر قرار گرفته اند را برمیگرداند
		@return 
			row count
	
	*/
	public function update($params, $id){
		$sql = "
			UPDATE `{$this->table_name}`
			SET
				". $this->createSqlFields($params) ."
			WHERE
				`id` = :id
		";
		
		$params = $this->createParams($params);
		
		$params[':id'] = $id;
		
		DatabaseHandler::Execute($sql, $params);
	}
	
	/*
		حذف یک رکورد
		
		
		@parameters
			$id = (int)  آی‌دی، که شماره یک رکورد در دیتابیس است
		
		تعداد رکورد هایی که مورد تاثیر قرار گرفته اند را برمیگرداند
		@return 
			row count
	*/
	public function delete($id){
		$sql = "DELETE FROM `{$this->table_name}` WHERE `id` = :id";
		$params = array(
			':id' => $id
		);
		return DatabaseHandler::Execute($sql, $params);
	}
	
	/*
		
	*/
	private function createParams($params){
		
		$new_array = [];
		
		foreach($params as $value):
			$new_array[":{$value}"] = $_POST[$value];
		endforeach;
		
		return $new_array;
	}
	
	/*
		
	*/
	private function createSqlFields($params, $seperator = ', '){
		$new_array = [];
		
		foreach($params as $value):
			$new_array[] = "`{$value}` = :{$value}";
		endforeach;
		
		return implode($seperator, $new_array);
	}
	
}

function table($table_name){
	return (new Table($table_name));
}
