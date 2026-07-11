<?php

function execute_skill($intent){

    switch($intent){

        case "music":

            $_SESSION['cipher_ai']['current_app']="music";

            return [
                "type"=>"open",
                "target"=>"cipher-music"
            ];

        case "video":

            $_SESSION['cipher_ai']['current_app']="stream";

            return [
                "type"=>"open",
                "target"=>"cipher-stream"
            ];

        case "cloud":

            $_SESSION['cipher_ai']['current_app']="cloud";

            return [
                "type"=>"open",
                "target"=>"cipher-cloud"
            ];

        case "desktop":

            return [
                "type"=>"open",
                "target"=>"cipher-desktop"
            ];

        default:

            return [
                "type"=>"chat"
            ];
    }
}
