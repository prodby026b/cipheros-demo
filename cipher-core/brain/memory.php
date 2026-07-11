<?php
session_start();

if(!isset($_SESSION['cipher_ai'])){
    $_SESSION['cipher_ai'] = [
        "history" => [],
        "current_app" => null,
        "user_mode" => "normal"
    ];
}

function remember($role,$message){

    $_SESSION['cipher_ai']['history'][] = [
        "role"=>$role,
        "message"=>$message,
        "time"=>time()
    ];

    if(count($_SESSION['cipher_ai']['history']) > 50){
        array_shift($_SESSION['cipher_ai']['history']);
    }
}

function get_memory(){
    return $_SESSION['cipher_ai'];
}
