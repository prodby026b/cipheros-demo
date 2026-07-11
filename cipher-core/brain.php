<?php

session_start();

if(!isset($_SESSION['cipher_memory'])){
    $_SESSION['cipher_memory'] = [];
}

function ai_reply($text){

    // ذخیره در حافظه مکالمه
    $_SESSION['cipher_memory'][] = $text;

    // اگر حافظه بلند شد، کوتاه می‌کنیم
    if(count($_SESSION['cipher_memory']) > 20){
        array_shift($_SESSION['cipher_memory']);
    }

    // پاسخ طبیعی‌تر
    $responses = [
        "باشه انجامش میدم!",
        "چشم، همین الان...",
        "اوکی 😊",
        "حتماً!",
        "متوجه شدم!",
        "در حال انجام..."
    ];

    return $responses[array_rand($responses)];
}
