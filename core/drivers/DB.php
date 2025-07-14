<?php
namespace Drivers;

use Core\Providers\EloquentServiceProvider;
use PDO;
use PDOException;
use Exception;
use stdClass;

class DB {
	private $n = 0;
	private static $dbm = null;

	private static function start() {
		$cdb = Config::getDBConfig();
		try {
			switch ($cdb->type) {
				case 'odbc':
					self::$dbm = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq={$cdb->route};Uid={$cdb->uid}");
					break;
				case 'pgsql':
					self::$dbm = new PDO("pgsql:dbname={$cdb->database};host={$cdb->host}", $cdb->username, $cdb->password);
					break;
				case 'sqlite':
					self::$dbm = new PDO("sqlite:{$cdb->path}");
					break;
				case 'mysql':
					self::$dbm = new PDO("mysql:host={$cdb->host};dbname={$cdb->database}", $cdb->username, $cdb->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					break;

				default:
					die("Tipo de base de datos no permitido");
					break;
			}
			self::$dbm->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$dbm->setAttribute(PDO::ATTR_PERSISTENT, true);
			self::$dbm->query('SET NAMES utf8');
			self::$dbm->query('SELECT convert(cast(convert(content using latin1) as binary) using utf8) AS content');
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	public static function close() {
		if(self::$dbm != null) {
			unset(self::$dbm);
		}
	}

	public static function execQuery($query = null) {
		if(self::$dbm) self::start();
		if(!$query) return;
		try {
			self::$dbm->beginTransaction();
			self::$dbm->exec($query);
			self::$dbm->commit();
		} catch(PDOException $e) {
	    	return false;
	    }
	    self::close();
	    return true;
	}

	public static function execQueryId($query = null) {
		if(!self::$dbm) self::start();
		if(!$query) return;
		try {
			self::$dbm->beginTransaction();
			self::$dbm->exec($query);
			$return = self::$dbm->lastInsertId();
			self::$dbm->commit();
		} catch(PDOException $e) {
	    	echo "<pre>";
	    	print_r($e);
	    	echo "</pre>";
	    	return false;
	    }
	    self::close();
	    return $return;
	}

	public static function tableInsert($table = null, $data = null, $k = 'id') {
		if(!self::$dbm) self::start();
		if(!$table || !$data) return;
		foreach ($data as $element) {
			if(!isset($element[$k]) || $element[$k] == ""){//Nuevo
				$keys = implode(',', array_keys($element));
				$values = "'".implode("', '", array_values($element))."'";
				$query = "INSERT INTO {$table} ({$keys}) VALUES({$values})";
			} else {//Actualizar
				$query = "UPDATE {$table} SET ";
				$i = 0;
				$id = $element[$k];
				unset($element[$k]);
				foreach ($element as $key => $value) {
					if($i > 0) $query .= ", ";
					$query .= "{$key}='{$value}'";
					$i++;
				}
				$query .= " WHERE {$k}='".$id."'";
			}

			try {
				self::$dbm->beginTransaction();
				self::$dbm->exec($query);
				$return = self::$dbm->lastInsertId();
				self::$dbm->commit();
			} catch(PDOException $e) {
		    	/*echo "<pre>";
		    	print_r($e);
		    	echo "</pre>";*/
		    	//return false;
		    	$return = $e->errorInfo;
		    }
		}

	    self::close();
	    return $return;
	}

	public static function execQueryObject($query = null, $lo = false) {
		if(!self::$dbm) self::start();
		if(!$query) return;

		try {
			$rows = self::$dbm->prepare($query);
			$rows->execute();
			$rows = $rows->fetchAll(PDO::FETCH_ASSOC);
			//$rows->setFetchMode(PDO::FETCH_ASSOC);
			//$result = $rows->fetchAll();
			//print_r($result);
			$return = array();
			if($rows){
				foreach ($rows as $row) {
	        		$r = new stdClass();
					foreach ($row as $key => $value) {
						$r->$key = $value;
					}
					$return[] = $r;
	        	}
	        	if(count($return) == 1 && !$lo) $return = $return[0];
	        	return $return;
			}

			return null;

		} catch (Exception $e) {
			return mysql_error();
		}
		self::close();

	}

	/**
	 * Get Eloquent database connection
	 * This provides access to the Eloquent ORM connection
	 */
	public static function getEloquentConnection($name = null) {
		return EloquentServiceProvider::getConnection($name);
	}

	/**
	 * Get Eloquent Capsule instance
	 * This provides access to the full Eloquent functionality
	 */
	public static function getEloquentCapsule() {
		return EloquentServiceProvider::getCapsule();
	}

	/**
	 * Execute raw query using Eloquent connection
	 * This is a bridge method for using Eloquent's query builder
	 */
	public static function eloquentQuery($query, $bindings = []) {
		return EloquentServiceProvider::getConnection()->select($query, $bindings);
	}

	/**
	 * Get Eloquent query builder instance
	 * This provides access to Eloquent's fluent query builder
	 */
	public static function table($table) {
		return EloquentServiceProvider::getConnection()->table($table);
	}

}
