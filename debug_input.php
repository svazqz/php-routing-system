<?php
require_once 'core/drivers/Input.php';

// Simulate POST request with JSON data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

echo "Testing Input class with simulated JSON data:\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "CONTENT_TYPE: " . $_SERVER['CONTENT_TYPE'] . "\n";

// Test the Input::all() method
$result = Input::all();
echo "Input::all() result:\n";
var_dump($result);

// Test with $_POST data
$_POST['name'] = 'Test POST';
$_POST['email'] = 'test@post.com';
echo "\nWith \$_POST data:\n";
$result2 = Input::all();
var_dump($result2);