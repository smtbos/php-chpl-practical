<?php

session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'chpl');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

function auth()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit();
    }
}

function guest()
{
    if (isset($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit();
    }
}
