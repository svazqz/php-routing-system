<?php

namespace Controllers\API;

use Core\APIController;

final class Probe extends APIController
{
    protected $__ROUTES__ = ['/api/probe/item/:id' => 'readItem'];

    public function show()
    {
        return ['ok' => true, 'name' => $_GET['name'] ?? null];
    }

    public function create()
    {
        return $this->jsonResponse(['created' => true], 201);
    }

    public function update()
    {
        return ['input' => \Input::all()];
    }

    public function readItem(string $id): array
    {
        return ['id' => (int) $id];
    }
}
