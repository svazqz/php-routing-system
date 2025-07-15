<?php

class Input {
  public const __POST__ = 0;
  public const __GET__ = 1;
  
  public static function getVar($name = null, $default = null, $type = null) {
    if($type != null){
      return ($type == self::__POST__) ? 
      ( (isset($_POST[$name])) ? $_POST[$name] : $default ) : 
      ( ($type == self::__GET__) ? ( isset($_GET[$name]) ? $_GET[$name] : $default ) : null );
    } else{
      return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
    }
  }
  
  /**
   * Get all input data from POST, GET, and PUT requests
   */
  public static function all() {
    $input = [];
    
    // Get GET data
    if (!empty($_GET)) {
      $input = array_merge($input, $_GET);
    }
    
    // Handle JSON data for POST, PUT, PATCH, DELETE requests
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      
      // Check if content type is JSON
      if (strpos($contentType, 'application/json') !== false) {
        $jsonData = file_get_contents('php://input');
        if (!empty($jsonData)) {
          $decodedData = json_decode($jsonData, true);
          if (json_last_error() === JSON_ERROR_NONE) {
            $input = array_merge($input, $decodedData);
          }
        }
      } else {
        // Get regular POST data
        if (!empty($_POST)) {
          $input = array_merge($input, $_POST);
        }
        
        // For PUT/PATCH/DELETE, try to parse as URL-encoded data
        if (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH', 'DELETE'])) {
          $putData = file_get_contents('php://input');
          if (!empty($putData)) {
            parse_str($putData, $parsedData);
            $input = array_merge($input, $parsedData);
          }
        }
      }
    }
    
    return $input;
  }
  
  /**
   * Get a specific input value
   */
  public static function get($name, $default = null) {
    return self::getVar($name, $default);
  }
  
  /**
   * Check if input has a specific key
   */
  public static function has($name) {
    return isset($_REQUEST[$name]);
  }
  
  /**
   * Get only specific keys from input
   */
  public static function only($keys) {
    $input = self::all();
    $result = [];
    
    foreach ($keys as $key) {
      if (isset($input[$key])) {
        $result[$key] = $input[$key];
      }
    }
    
    return $result;
  }
  
  /**
   * Get all input except specific keys
   */
  public static function except($keys) {
    $input = self::all();
    
    foreach ($keys as $key) {
      unset($input[$key]);
    }
    
    return $input;
  }
}