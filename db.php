<?php
$servername = "sql300.infinityfree.com";
$username = "if0_39258521";
$password = "trademart33";
$database = "if0_39258521_trademart";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " .$conn->connect_error);
}
?>