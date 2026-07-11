<?php
include "db.php";

$username = $_POST['username'] ?? '';
$message  = $_POST['message'] ?? '';

if($username == "" || $message == ""){
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (username,message,type) VALUES (?,?,?)");

$type="text";

$stmt->bind_param("sss",$username,$message,$type);

$stmt->execute();
