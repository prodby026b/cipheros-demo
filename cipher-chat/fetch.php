<?php
include "db.php";

$result = $conn->query("SELECT * FROM messages ORDER BY id ASC");

while($row = $result->fetch_assoc()){

echo "<div class='msg'>";

echo "<b>".htmlspecialchars($row['username'])."</b><br>";

if($row['type']=="text"){
echo htmlspecialchars($row['message']);
}

if($row['type']=="image" && !empty($row['file_path'])){
echo "<br><img src='".$row['file_path']."'>";
}

echo "</div>";

}
?>
