<?php

$host = "sql207.infinityfree.com";
$dbname = "if0_40113975_academy";
$username = "if0_40113975";
$password = "WZ1ZxQ8ghVo";

$mysqli = new mysqli(
    hostname: $host,
    username: $username,
    password: $password,
    database: $dbname
);

if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;