<?php

namespace Controllers;

use Core;
use Services\DemoService;

class Home extends Core\Controller {
    private $demoService;
    public function __construct(DemoService $demoService) {
        $this->demoService = $demoService;
    }

    public function main() {
        // ToDo: Have auto complete for view methods
        $this->getView()->postsPage($this->demoService->getPosts());
    }
}
