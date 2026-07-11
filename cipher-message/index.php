<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CipherMessage - Premium OS Suite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="app-container">
    
    <aside class="sidebar-right">
        <div class="brand">
            <i class="fas fa-shield-alt"></i>
            <span>CipherMessage</span>
        </div>
        
        <div class="section-title">کانال‌های گفتگو</div>
        <ul class="channel-list">
            <li class="channel-item active" data-channel="general">
                <i class="fas fa-hashtag"></i> عمومی
            </li>
            <li class="channel-item" data-channel="dev">
                <i class="fas fa-code"></i> توسعه و کدنویسی
            </li>
            <li class="channel-item" data-channel="design">
                <i class="fas fa-palette"></i> طراحی و UI/UX
            </li>
            <li class="channel-item" data-channel="random">
                <i class="fas fa-comments"></i> گفتگو آزاد
            </li>
        </ul>

        <div class="user-profile-box">
            <div class="avatar-wrapper">
                <img src="assets/default_avatar.png" id="myAvatar" alt="Avatar">
                <div class="status-indicator online"></div>
            </div>
            <div class="user-meta">
                <span class="username" id="currentUsername">بارگذاری...</span>
                <span class="role">کاربر سایفر</span>
            </div>
        </div>
    </aside>

    <main class="chat-main">
        <header class="chat-header">
            <div class="channel-info">
                <h2 id="currentChannelTitle"><i class="fas fa-hashtag"></i> عمومی</h2>
            </div>
            
            <div class="header-controls">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="جستجو در پیام‌ها...">
                    <i class="fas fa-times clear-search" id="clearSearch" style="display:none;"></i>
                </div>
                <button class="icon-btn" id="pinnedMessagesBtn" title="پیام‌های پین شده">
                    <i class="fas fa-thumbtack"></i>
                </button>
            </div>
        </header>

        <div class="messages-container" id="messagesContainer">
            </div>

        <div class="action-preview-bar" id="actionPreviewBar" style="display: none;">
            <div class="preview-content">
                <i class="fas fa-reply" id="previewIcon"></i>
                <div class="preview-text-wrapper">
                    <span id="previewTitle">پاسخ به فلانی</span>
                    <p id="previewText">متن پیام...</p>
                </div>
            </div>
            <button class="close-preview-btn" onclick="cancelAction()"><i class="fas fa-times"></i></button>
        </div>

        <footer class="chat-footer">
            <form id="messageForm">
                <input type="hidden" id="replyToInput" value="">
                <input type="hidden" id="editMessageIdInput" value="">
                
                <div class="input-wrapper">
                    <textarea id="messageInput" placeholder="پیام خود را بنویسید... (برای ارسال کد کادر مخصوص را باز کنید)" rows="1"></textarea>
                    
                    <div class="input-actions">
                        <button type="button" class="action-trigger" id="codeBlockBtn" title="ارسال کد برنامه‌نویسی">
                            <i class="fas fa-code"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </footer>
    </main>

    <aside class="sidebar-left">
        <div class="section-title"><i class="fas fa-users"></i> کاربران آنلاین</div>
        <ul class="online-users-list" id="onlineUsersList">
            </ul>
    </aside>
</div>

<div class="modal" id="codeModal">
    <div class="modal-content">
        <h3><i class="fas fa-code"></i> ارسال کد با لایتنینگ هوشمند</h3>
        <select id="codeLanguage">
            <option value="javascript">JavaScript</option>
            <option value="php">PHP</option>
            <option value="html">HTML</option>
            <option value="css">CSS</option>
            <option value="python">Python</option>
            <option value="sql">SQL</option>
        </select>
        <textarea id="codeTextarea" placeholder="کد خود را اینجا کپی کنید..."></textarea>
        <div class="modal-actions">
            <button class="btn btn-cancel" id="closeCodeModal">انصراف</button>
            <button class="btn btn-confirm" id="submitCodeBtn">درج کد در چت</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>

<script src="assets/app.js"></script>
</body>
</html>