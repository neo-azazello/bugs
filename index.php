<?php
date_default_timezone_set('Asia/Baku');

// server should keep session data 
ini_set('session.gc_maxlifetime', 86400);

// each client should remember their session id
session_set_cookie_params(86400);

require __DIR__ . '/bootstrap/app.php';

//Here we run our application
$app->run();