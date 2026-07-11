// cipher-message/assets/app.js

let currentChannel = 'general';
let currentEditingId = null;
let currentReplyToId = null;

document.addEventListener("DOMContentLoaded", () => {
    // بارگذاری اولیه اطلاعات
    fetchMessages();
    fetchOnlineUsers();
    
    // پولینگ منظم (هر ۲ ثانیه پیام‌ها و هر ۵ ثانیه آنلاین‌ها)
    setInterval(fetchMessages, 2000);
    setInterval(fetchOnlineUsers, 5000);

    // ۱. مدیریت فرم ارسال پیام
    const messageForm = document.getElementById("messageForm");
    messageForm.addEventListener("submit", function(e) {
        e.preventDefault();
        const input = document.getElementById("messageInput");
        const msgText = input.value.trim();
        
        if (!msgText) return;

        let url = `api.php?action=send_message`;
        let formData = new FormData();
        formData.append('channel', currentChannel);
        
        if (currentEditingId) {
            // حالت ویرایش پیام
            url = `api.php?action=edit_message`;
            formData.append('message_id', currentEditingId);
            formData.append('message', msgText);
        } else {
            // حالت ارسال پیام جدید یا ریپلای
            formData.append('message', msgText);
            if (currentReplyToId) {
                formData.append('reply_to', currentReplyToId);
            }
        }

        fetch(url, { method: "POST", body: formData })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                input.value = "";
                cancelAction();
                fetchMessages();
            } else {
                alert(res.message);
            }
        });
    });

    // ۲. سوییچ کردن بین کانال‌ها
    document.querySelectorAll(".channel-item").forEach(item => {
        item.addEventListener("click", function() {
            document.querySelectorAll(".channel-item").forEach(i => i.classList.remove("active"));
            this.classList.add("active");
            currentChannel = this.getAttribute("data-channel");
            document.getElementById("currentChannelTitle").innerHTML = this.innerHTML;
            cancelAction();
            fetchMessages();
        });
    });

    // ۳. مدیریت مودال ارسال کدهای برنامه‌نویسی
    const codeModal = document.getElementById("codeModal");
    document.getElementById("codeBlockBtn").addEventListener("click", () => codeModal.style.display = "flex");
    document.getElementById("closeCodeModal").addEventListener("click", () => codeModal.style.display = "none");
    
    document.getElementById("submitCodeBtn").addEventListener("click", () => {
        const lang = document.getElementById("codeLanguage").value;
        const code = document.getElementById("codeTextarea").value.trim();
        if(!code) return;

        // ساخت فرمت مارک‌داون استاندارد برای سینتکس هایلایت
        const formattedCode = `\`\`\`${lang}\n${code}\n\`\`\``;
        
        let formData = new FormData();
        formData.append('channel', currentChannel);
        formData.append('message', formattedCode);

        fetch(`api.php?action=send_message`, { method: "POST", body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                document.getElementById("codeTextarea").value = "";
                codeModal.style.display = "none";
                fetchMessages();
            }
        });
    });

    // ۴. موتور جستجوی لایو
    const searchInput = document.getElementById("searchInput");
    const clearSearch = document.getElementById("clearSearch");
    
    searchInput.addEventListener("input", function() {
        const term = this.value.trim();
        if(term.length > 1) {
            clearSearch.style.display = "block";
            fetch(`api.php?action=search_messages&channel=${currentChannel}&term=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(res => renderMessagesList(res.data, true)); // رندر در حالت نتایج سرچ
        } else {
            clearSearch.style.display = "none";
            fetchMessages();
        }
    });

    clearSearch.addEventListener("click", () => {
        searchInput.value = "";
        clearSearch.style.display = "none";
        fetchMessages();
    });
});

// تابع واکشی پیام‌ها از API
function fetchMessages() {
    // اگر کاربر در حال تایپ سرچ است، پولینگ پیام‌ها را متوقف کن تا نتایج به هم نریزد
    if(document.getElementById("searchInput").value.trim().length > 1) return;

    fetch(`api.php?action=fetch_messages&channel=${currentChannel}`)
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success') {
            renderMessagesList(res.data, false);
        }
    });
}

// رندر کردن درختی و پیشرفته پکیج پیام‌ها
function renderMessagesList(messages, isSearchMode) {
    const container = document.getElementById("messagesContainer");
    let shouldScroll = container.scrollTop + container.clientHeight >= container.scrollHeight - 80;
    
    container.innerHTML = "";
    
    if(messages.length === 0) {
        container.innerHTML = `<div style="text-align:center;color:var(--text-muted);margin-top:20px;">هنوز پیامی در این کانال ارسال نشده است.</div>`;
        return;
    }

    messages.forEach(msg => {
        let isMine = (msg.username === document.getElementById("currentUsername").innerText);
        // اگر سیستم سشن نام کاربری لود نکرده، موقتاً با آی‌دی چک می‌کند
        if(document.getElementById("currentUsername").innerText === 'بارگذاری...') {
            document.getElementById("currentUsername").innerText = msg.username; // ست کردن نام فرستنده اول به عنوان کلاینت لایو
        }

        let cardClass = isMine ? "msg-mine" : "msg-others";
        if(msg.is_pinned == 1) cardClass += " pinned";

        // مدیریت نمایش کدهای برنامه‌نویسی (Syntax Highlighting)
        let processedContent = msg.message;
        if(msg.message.startsWith("\`\`\`")) {
            const match = msg.message.match(/\`\`\`(\w+)\n([\s\S]+?)\n\`\`\`/);
            if(match) {
                const lang = match[1];
                const code = escapeHtml(match[2]);
                processedContent = `<pre><code class="language-${lang}">${code}</code></pre>`;
            }
        }

        // رندر باکس ریپلای بالای پیام
        let replyBoxMarkup = "";
        if(msg.replied_to_user) {
            replyBoxMarkup = `
                <div class="replied-box">
                    <small>پاسخ به @${msg.replied_to_user}:</small>
                    <p>${msg.replied_to_msg.substring(0,60)}...</p>
                </div>
            `;
        }

        // رندر دکمه‌های تولبار پیام
        let toolbarMarkup = `
            <div class="msg-actions-toolbar">
                <span onclick="initiateReply(${msg.id}, '${msg.username}', '${escapeJs(msg.message)}')"><i class="fas fa-reply" title="ریپلای"></i></span>
                <span onclick="togglePinMessage(${msg.id})"><i class="fas fa-thumbtack" title="پین"></i></span>
                <span onclick="toggleReaction(${msg.id}, '👍')">👍</span>
                <span onclick="toggleReaction(${msg.id}, '🔥')">🔥</span>
                <span onclick="toggleReaction(${msg.id}, '💻')">💻</span>
        `;
        if(isMine) {
            toolbarMarkup += `
                <span onclick="initiateEdit(${msg.id}, '${escapeJs(msg.message)}')"><i class="fas fa-edit" title="ویرایش"></i></span>
                <span onclick="deleteMessage(${msg.id})"><i class="fas fa-trash" style="color:#ff4a4a;" title="حذف"></i></span>
            `;
        }
        toolbarMarkup += `</div>`;

        // رندر دکمه‌های ری‌آکشن
        let reactionsMarkup = `<div class="reactions-wrapper">`;
        if(msg.reactions) {
            for (let emoji in msg.reactions) {
                reactionsMarkup += `
                    <div class="reaction-badge" onclick="toggleReaction(${msg.id}, '${emoji}')">
                        ${emoji} <span>${msg.reactions[emoji].length}</span>
                    </div>
                `;
            }
        }
        reactionsMarkup += `</div>`;

        const msgHtml = `
            <div class="msg-card ${cardClass}" id="msg-${msg.id}">
                ${msg.is_pinned == 1 ? '<span class="pin-indicator"><i class="fas fa-thumbtack"></i> پین شده</span>' : ''}
                ${toolbarMarkup}
                <div class="msg-top">
                    <span class="msg-user">@${msg.username}</span>
                    <span class="msg-time">${msg.created_at.substring(11,16)} ${msg.is_edited == 1 ? '(ویرایش شده)' : ''}</span>
                </div>
                ${replyBoxMarkup}
                <div class="msg-body">${processedContent}</div>
                ${reactionsMarkup}
            </div>
        `;
        container.innerHTML += msgHtml;
    });

    // فعال‌سازی رندر هایلایت کدهای برنامه‌نویسی توسط Prism
    Prism.highlightAll();

    // اسکرول خودکار به انتهای صفحه در صورتی که کاربر خودش اسکرول نکرده باشد
    if (shouldScroll && !isSearchMode) {
        container.scrollTop = container.scrollHeight;
    }
}

// ابزارهای تعاملی پیام‌ها
function initiateReply(id, user, text) {
    currentReplyToId = id;
    currentEditingId = null;
    document.getElementById("replyToInput").value = id;
    document.getElementById("previewTitle").innerText = `پاسخ به @${user}`;
    document.getElementById("previewText").innerText = text.substring(0,60);
    document.getElementById("previewIcon").className = "fas fa-reply";
    document.getElementById("actionPreviewBar").style.display = "flex";
    document.getElementById("messageInput").focus();
}

function initiateEdit(id, text) {
    currentEditingId = id;
    currentReplyToId = null;
    document.getElementById("editMessageIdInput").value = id;
    document.getElementById("previewTitle").innerText = `در حال ویرایش پیام خود...`;
    document.getElementById("previewText").innerText = text.substring(0,60);
    document.getElementById("previewIcon").className = "fas fa-edit";
    document.getElementById("actionPreviewBar").style.display = "flex";
    document.getElementById("messageInput").value = text;
    document.getElementById("messageInput").focus();
}

function cancelAction() {
    currentReplyToId = null;
    currentEditingId = null;
    document.getElementById("replyToInput").value = "";
    document.getElementById("editMessageIdInput").value = "";
    document.getElementById("actionPreviewBar").style.display = "none";
    document.getElementById("messageInput").value = "";
}

function deleteMessage(id) {
    if(confirm("آیا از حذف این پیام مطمئن هستید؟")) {
        fetch(`api.php?action=delete_message&message_id=${id}`)
        .then(res => res.json())
        .then(() => fetchMessages());
    }
}

function togglePinMessage(id) {
    fetch(`api.php?action=toggle_pin&message_id=${id}`)
    .then(res => res.json())
    .then(() => fetchMessages());
}

function toggleReaction(id, emoji) {
    fetch(`api.php?action=toggle_reaction&message_id=${id}&emoji=${encodeURIComponent(emoji)}`)
    .then(res => res.json())
    .then(() => fetchMessages());
}

// لود لیست کاربران آنلاین
function fetchOnlineUsers() {
    fetch(`api.php?action=fetch_online_users`)
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success') {
            const list = document.getElementById("onlineUsersList");
            list.innerHTML = "";
            res.data.forEach(user => {
                list.innerHTML += `
                    <li class="online-user-item">
                        <img src="assets/${user.avatar}" class="online-avatar" onerror="this.src='assets/default_avatar.png'">
                        <span class="online-name">@${user.username}</span>
                    </li>
                `;
            });
        }
    });
}

// ابزارهای کمکی امنیتی
function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
function escapeJs(text) {
    return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
}