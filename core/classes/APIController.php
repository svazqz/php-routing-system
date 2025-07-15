<?php

namespace Core;

abstract class APIController {

  private const __HTTP_VERBS__ = array(
    "POST" => "create",
    "GET" => "show",
    "PUT" => "update",
    "DELETE" => "delete"
  );

  protected $__ROUTES__;

  private function matchRoute($uri) {
    if (!$this->__ROUTES__ ) return false;
    foreach ($this->__ROUTES__ as $pattern => $method) {
      $pattern = preg_quote($pattern, '/');
      $pattern = str_replace('\:id', '(\d+)', $pattern);
      if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
        array_shift($matches);
        return array($method, $matches);
      }
    }
    return false;
  }

  public function __run($runnableData) {
    $components = $runnableData->components;
    $originalURI = $runnableData->originalURI;
    $matchingRoute = $this->matchRoute($originalURI);
    if ($matchingRoute) {
      list($method, $params) = $matchingRoute;
      $return = call_user_func_array(array($this, $method), $params);
      header('Content-Type: application/json');
      echo json_encode($return);
      return;
    }

    $method = strtolower($_SERVER["REQUEST_METHOD"]) . ucfirst($runnableData->controller);
    $newComponents = array_slice($components, 1);
    if (!method_exists($this, $method)) {
      $method = self::__HTTP_VERBS__[$_SERVER["REQUEST_METHOD"]];
      $newComponents = $components;
    }
    $return = call_user_func_array(array($this, $method), $newComponents);
    header('Content-Type: application/json');
    echo json_encode($return);
    return;
  }

  /**
   * Send a JSON response with optional status code
   */
  protected function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    return $data;
  }

  public function show() {
    echo "Executed GET";
  }
  public function create() {
    echo "Executed POST";
  }
  public function update() {
    echo "Executed PUT";
  }
  public function delete() {
    echo "Executed DELETE";
  }

}
