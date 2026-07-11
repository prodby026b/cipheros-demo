let username = "";
let chatBox = document.getElementById("chat-box");

// اجرای خودکار هنگام لود صفحه
window.onload = function () {
    let savedUser = localStorage.getItem("cipherUser");
    if (savedUser && savedUser !== "") {
        username = savedUser;
        document.getElementById("username-box").style.display = "none";
    }

    // تعریف EventListener ها در اینجا که مطمئن باشیم المنت‌ها ساخته شده‌اند
    const messageInput = document.getElementById('message');
    const usernameInput = document.getElementById('username');

    if (messageInput) {
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    if (usernameInput) {
        usernameInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                setName();
            }
        });
    }
};

function setName() {
    let input = document.getElementById("username");
    username = input.value.trim();

    if (username === "") {
        alert("لطفا نام خود را وارد کنید");
        return;
    }

    localStorage.setItem("cipherUser", username);
    document.getElementById("username-box").style.display = "none";
}

// ارسال پیام
function sendMessage() {
    let msgInput = document.getElementById("message");
    let msg = msgInput.value.trim();

    if (msg === "" || username === "") return; // جلوگیری از ارسال پیام خالی

    fetch("send.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "username=" + encodeURIComponent(username) + "&message=" + encodeURIComponent(msg)
    }).then(() => {
        loadChat(); // بلافاصله چت را بروزرسانی کن تا سرعت بالا به نظر برسد
    });

    msgInput.value = "";
}

// ارسال عکس
document.getElementById("fileInput").addEventListener("change", function () {
    let file = this.files[0];
    if (!file) return;

    let form = new FormData();
    form.append("file", file);
    form.append("username", username);

    fetch("upload.php", {
        method: "POST",
        body: form
    }).then(() => {
        loadChat();
    });
});

// دریافت پیام‌ها
function loadChat() {
    if (!chatBox) return;
    
    let oldHeight = chatBox.scrollHeight;
    let isAtBottom = chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 50;

    fetch("fetch.php")
        .then(res => res.text())
        .then(data => {
            chatBox.innerHTML = data;

            // اگر کاربر خودش بالا نرفته بود، اتوماتیک اسکرول کن پایین
            if (isAtBottom) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
}

setInterval(loadChat, 2000); // ۲ ثانیه برای هاست‌های معمولی بهینه‌تر است
