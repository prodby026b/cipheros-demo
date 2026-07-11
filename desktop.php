<!DOCTYPE html>
<html>
<head>
    <title>Cipher OS</title>

    <link rel="stylesheet" href="cipher-core/style.css">
    <link rel="stylesheet" href="cipher-core/desktop.css">
</head>
<body>

<div id="desktop">

    <div class="desktop-icon" onclick="openApp('cipher-tasks')">
        ✅
        <span>Tasks</span>
    </div>

    <div class="desktop-icon" onclick="openApp('cipher-music')">
        🎵
        <span>Music</span>
    </div>

    <div class="desktop-icon" onclick="openApp('cipher-stream')">
        🎬
        <span>Stream</span>
    </div>

</div>

<div id="taskbar">

    <button id="start-btn">⚡ Cipher</button>

    <div id="running-apps"></div>

    <div id="clock"></div>

</div>

<script src="cipher-core/windows.js"></script>

</body>
</html>
