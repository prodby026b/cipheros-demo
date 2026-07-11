<!DOCTYPE html>
<html>
<head>
    <title>Cipher AI Core</title>
    <link rel="stylesheet" href="../cipher-dashboard/assets/css/bootstrap.min.css">

    <style>
        body { background:#0f172a; color:white; padding:30px; }
        #response { background:#1e293b; padding:25px; border-radius:15px; min-height:150px; margin-top:20px; }
        .ai { font-weight:bold; color:#38bdf8; }
    </style>
</head>

<body>

<h2>🤖 Cipher AI Core v3</h2>

<input class="form-control" id="prompt" placeholder="چیزی بنویس... (مثلاً: موزیک باز کن)">
<br>

<button onclick="ask()" class="btn btn-info w-100">ارسال</button>

<div id="response"></div>

<script>
async function ask(){
    const prompt = document.getElementById("prompt").value;

    const res = await fetch("ai_engine.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({prompt})
    });

    const data = await res.json();

    if(data.type === "action"){
        document.getElementById("response").innerHTML = 
            "<div class='ai'>"+data.message+"</div>";

        // اجرای اپ (در دسکتاپ بعداً تبدیل به window می‌شود)
        window.location.href = "../" + data.app + "/index.php";
    } 
    else {
        document.getElementById("response").innerHTML = 
            "<div class='ai'>"+data.message+"</div>";
    }
}
</script>

</body>
</html>
