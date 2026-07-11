<?php

function detect_intent($prompt) {

    $intent = "chat"; // پیش‌فرض: فقط مکالمه

    $keywords = [
        "open" => "open_app",
        "باز کن" => "open_app",
        "پخش کن" => "play_media",
        "پخش موسیقی" => "open_music",
        "موزیک" => "open_music",
        "ویدیو" => "open_stream",
        "آپلود" => "cloud_upload",
        "آپلود کن" => "cloud_upload",
        "فایل" => "open_cloud",
        "ابر" => "open_cloud",
        "دسکتاپ" => "desktop",
    ];

    foreach($keywords as $key=>$val){
        if(strpos($prompt, $key) !== false){
            return $val;
        }
    }

    return $intent;
}
