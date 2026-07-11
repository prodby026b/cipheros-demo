<?php

function system_action($intent, $prompt){

    switch($intent){

        case "open_app":
            if(strpos($prompt,"music")!==false || strpos($prompt,"موزیک")!==false)
                return ["type"=>"action","app"=>"cipher-music"];

            if(strpos($prompt,"video")!==false || strpos($prompt,"ویدیو")!==false)
                return ["type"=>"action","app"=>"cipher-stream"];

            if(strpos($prompt,"cloud")!==false || strpos($prompt,"ابر")!==false)
                return ["type"=>"action","app"=>"cipher-cloud"];

            return ["type"=>"text","message"=>"نمیدونم کدوم برنامه رو میخوای باز کنم!"];

        case "open_music":
            return ["type"=>"action","app"=>"cipher-music"];

        case "open_stream":
            return ["type"=>"action","app"=>"cipher-stream"];

        case "open_cloud":
            return ["type"=>"action","app"=>"cipher-cloud"];

        case "desktop":
            return ["type"=>"action","app"=>"cipher-desktop"];

        case "play_media":
            return ["type"=>"text","message"=>"باشه! چه آهنگی یا ویدیویی میخوای پخش کنم؟"];

        case "chat":
        default:
            return ["type"=>"text","message"=>"باشه! $prompt"];
    }
}
