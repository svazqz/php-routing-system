<?php

// The Docker runner serves a fixture controller through the real public router.
function expectResponse(string $path, string $method, int $status, array $expected, ?string $payload = null, ?string $contentType = null): void
{
    $options = ['http' => ['method' => $method, 'ignore_errors' => true, 'timeout' => 4]];
    if ($payload !== null) {
        $options['http']['content'] = $payload;
        $options['http']['header'] = "Content-Type: $contentType";
    }
    $body = file_get_contents('http://127.0.0.1:8080'.$path, false, stream_context_create($options));
    if ($body === false || ! str_contains($http_response_header[0] ?? '', " $status ")) {
        throw new RuntimeException("$method $path did not return HTTP $status");
    }

    $headers = implode("\n", $http_response_header);
    if (stripos($headers, 'Content-Type: application/json') === false) {
        throw new RuntimeException("$method $path did not return JSON");
    }

    if (json_decode($body, true, 512, JSON_THROW_ON_ERROR) !== $expected) {
        throw new RuntimeException("$method $path returned an unexpected payload");
    }
}

expectResponse('/api/probe?name=fixture', 'GET', 200, ['ok' => true, 'name' => 'fixture']);
expectResponse('/index.php/api/probe/item/12', 'GET', 200, ['id' => 12]);
expectResponse('/api/probe', 'POST', 201, ['created' => true]);
expectResponse('/api/probe?value=query', 'PUT', 200, ['input' => ['value' => 'json', 'count' => 0]], '{"value":"json","count":0}', 'application/json');
expectResponse('/api/probe?value=query', 'PUT', 200, ['input' => ['value' => 'query']], '{"incomplete":', 'application/json');
expectResponse('/api/probe?value=query', 'PUT', 200, ['input' => ['value' => 'query']], 'true', 'application/json');
expectResponse('/api/probe?value=query', 'PUT', 200, ['input' => ['value' => 'form', 'count' => '0']], 'value=form&count=0', 'application/x-www-form-urlencoded');
echo "HTTP fixture routes passed: 7/7\n";
