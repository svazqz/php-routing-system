<?php

class Config {
	private static $instance = null;
	private $config = null;
	private function __construct() {
		$this->config = parse_ini_file("config.ini", true);
	}

	public function __clone() {
		throw new \Error('Clone no se permite.');
	}

	public static function get() {
		if (self::$instance == null) {
			$c = __CLASS__;
			$instance = new $c();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function getVar($config_var = "", $default = null) {
		$sections = explode(".", $config_var);
		if(count($sections) > 1) {
			return (isset($this->config[$sections[0]][$sections[1]])) ? $this->config[$sections[0]][$sections[1]] : $default;
		}
		return (isset($this->config[$sections[0]])) ? $this->config[$sections[0]] : $default;
	}
}
