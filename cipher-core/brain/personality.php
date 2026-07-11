<?php

function ai_personality_reply($intent){

    $moods = [

        "music" => [
            "🎵 بزن بریم موزیک!",
            "دارم موزیک رو برات آماده میکنم",
            "آهنگ‌ها آماده‌ان 😎"
        ],

        "video" => [
            "🎬 وقت فیلمه!",
            "دارم استریم رو باز میکنم",
        ],

        "cloud" => [
            "☁️ فضای ابری آماده است",
        ],

        "chat" => [
            "بگو ببینم 😄",
            "گوش میدم...",
            "در خدمتم"
        ]
    ];

    return $moods[$intent][array_rand($moods[$intent])];
}
