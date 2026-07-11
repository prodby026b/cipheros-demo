<?php
header("Content-Type: application/json");

require "brain.php";
require "intents.php";
require "system_actions.php";

$data = json_decode(file_get_contents("php://input"), true);
$prompt = strtolower(trim($data['prompt']));

$intent = detect_intent($prompt);
$result = system_action($intent, $prompt);

if($result["type"] == "action"){
    echo json_encode([
        "type" => "action",
        "app" => $result["app"],
        "message" => ai_reply("opening ".$result["app"])
    ]);
}

else {
    echo json_encode([
        "type" => "text",
        "message" => $result["message"]
    ]);
}

?>
