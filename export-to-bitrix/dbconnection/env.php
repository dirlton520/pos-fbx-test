<?php

$host = 'localhost';
$user = 'codilya';
$password = 'pass@123';
$database = 'demopos_sparkfn_local_new_pos';

$connect = new mysqli($host, $user, $password, $database);

if ($connect->connect_errno) {
  echo "Failed to connect to MySQL: " . $connect->connect_error;
  exit();
}

?>