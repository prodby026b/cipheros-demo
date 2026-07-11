<?php

function analyze_prompt($prompt){

    $prompt = mb_strtolower($prompt);

    $map = [

        "music" => [
            "موزیک",
            "آهنگ",
            "music",
            "play song",
            "spotify"
        ],

        "video" => [
            "ویدیو",
            "فیلم",
            "stream",
            "video"
        ],

        "cloud" => [
            "آپلود",
            "فضای ابری",
            "cloud",
            "file"
        ],

        "desktop" => [
            "دسکتاپ",
            "پنجره",
            "window"
        ]
    ];

    foreach($map as $intent=>$words){

        foreach($words as $w){

            if(strpos($prompt,$w)!==false){
                return $intent;
            }
        }
    }

    return "chat";
}
