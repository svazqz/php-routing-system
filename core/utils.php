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
  $controller = \Config::get()->getVar("defaults.controller", "");
  if(count($components) > 0) {
    $controller = $components[0];
    if($controller == "api") {
      $namespace .= "API\\";
      $controller = $components[1];
      $components = array_slice($components, 2);
    } else {
      $components = array_slice($components, 1);
    }
  }
  $runnableData = new \stdClass();
  $runnableData->controller = $controller;
  $runnableData->components = $components;
  $runnableData->namespace = $namespace;
  $runnableData->originalURI = $originalURI;
  return $runnableData;
}