<?php

function parseURIAndComponents() {
  $URI = $_SERVER['REQUEST_URI'];
  $parsedURI = parse_url('http://phproutingsystem.com'.$URI);
  
  // Preserve query string and populate $_GET if it exists
  if (isset($parsedURI['query'])) {
    parse_str($parsedURI['query'], $_GET);
  }
  
  $URI = str_replace("/index.php", "", $parsedURI["path"]);
  $URI = str_replace("index.php", "", $URI);
  $originalURI = $URI;
  $URI = str_replace("/", " ", $URI);
  $URI = trim($URI);
  
  $components = (strlen($URI) > 0) ? explode(" ", $URI) : array();
  $namespace = "Controllers\\";
  $defaultController = \Config::get()->getVar("defaults.controller", "");
  $controller = $defaultController;
  
  if(count($components) > 0) {
    $firstSegment = $components[0];
    
    // Handle API routes
    if($firstSegment == "api") {
      $namespace .= "API\\";
      $controller = $components[1];
      $components = array_slice($components, 2);
    } else {
      // Check if first segment is a method of the default controller
      $defaultControllerClass = $namespace . $defaultController;
      if (class_exists($defaultControllerClass) && method_exists($defaultControllerClass, $firstSegment)) {
        // First segment is a method of default controller, keep default controller
        $controller = $defaultController;
        // Don't remove the first component, it will be treated as a method
      } else {
        // First segment is a controller name
        $controller = $firstSegment;
        $components = array_slice($components, 1);
      }
    }
  }
  
  $runnableData = new \stdClass();
  $runnableData->controller = $controller;
  $runnableData->components = $components;
  $runnableData->namespace = $namespace;
  $runnableData->originalURI = $originalURI;
  return $runnableData;
}