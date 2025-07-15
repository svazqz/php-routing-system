<?php

namespace Controllers\API;

use Core;
use Services\DemoService;

class Blog extends Core\APIController {
  private $demoService;

  public function __construct(DemoService $demoService) {
    $this->__ROUTES__ = array(
      "/api/blog/post/:id" => "getPostData"
    );
    $this->demoService = $demoService;
  }

  // Default GET method
  public function show() {
    $posts = $this->demoService->getPosts();
    return $posts;
  }

  public function getPostData($id = 0) {
    if ($id ==  1) {
      $post = $this->demoService->getPost($id);
      return $post;
    }
  }
}