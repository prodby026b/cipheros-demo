<?php
include "db.php";

$username = $_POST['username'];

$file = $_FILES['file'];

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

$allowed = ["jpg","jpeg","png","gif"];

if(!in_array(strtolower($ext),$allowed)){
exit;
}

$path = "uploads/".time().".".$ext;

move_uploaded_file($file['tmp_name'],$path);

$conn->query("INSERT INTO messages (username,type,file_path) 
VALUES ('$username','image','$path')");
?>
