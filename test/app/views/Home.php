<?php

namespace Views;

class Home {
  public function postsPage($posts = array()) {
    \View::renderHTML("home/index", array("posts" => $posts));
  }
}
