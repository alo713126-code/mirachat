<?php
require_once "config.php";
requireLogin();

$me = currentUser();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiraChat</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #070d20;
            --panel: #0c1530;
            --panel2: #101b39;
            --panel3: #142044;
            --border: #202e52;
            --cyan: #08bde9;
            --purple: #7049ff;
            --text: #f4f7ff;
            --muted: #8d9abb;
            --danger: #ff5470;
            --success: #35d49a;
        }

        body {
            font-family: Tahoma, Arial, sans-serif;
            background:
                radial-gradient(circle at 20% 10%, rgba(112, 73, 255, .13), transparent 28%),
                radial-gradient(circle at 80% 90%, rgba(8, 189, 233, .10), transparent 28%),
                var(--bg);
            color: var(--text);
            height: 100vh;
            overflow: hidden;
        }

        button,
        input,
        textarea {
            font-family: inherit;
        }

        button {
            border: 0;
            cursor: pointer;
        }

        .app {
            height: 100vh;
            display: grid;
            grid-template-columns: 86px 330px 1fr 285px;
        }

        /* =========================
   SIDEBAR
========================= */

        .sidebar {
            background: rgba(8, 14, 33, .88);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
            gap: 24px;
        }

        .logo {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            font-weight: bold;
            box-shadow: 0 10px 35px rgba(112, 73, 255, .3);
        }

        .side-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .side-btn {
            width: 100%;
            height: 58px;
            border-radius: 16px;
            background: transparent;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: .2s;
        }

        .side-btn:hover,
        .side-btn.active {
            color: white;
            background: linear-gradient(135deg,
                    rgba(8, 189, 233, .13),
                    rgba(112, 73, 255, .16));
        }

        .side-icon {
            font-size: 20px;
        }

        .side-label {
            font-size: 10px;
        }

        .sidebar-bottom {
            margin-top: auto;
            width: 100%;
        }

        /* =========================
   CHAT LIST
========================= */

        .chat-list {
            background: rgba(12, 21, 48, .86);
            border-left: 1px solid var(--border);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .chat-head {
            padding: 22px 18px 15px;
        }

        .brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .brand-row h1 {
            font-size: 21px;
            letter-spacing: -.5px;
        }

        .plus-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            color: white;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            font-size: 21px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            height: 44px;
            background: var(--panel2);
            border: 1px solid var(--border);
            border-radius: 13px;
            color: white;
            outline: none;
            padding: 0 42px 0 14px;
        }

        .search-box span {
            position: absolute;
            right: 14px;
            top: 11px;
            color: var(--muted);
        }

        .chat-items {
            flex: 1;
            overflow-y: auto;
            padding: 5px 10px 20px;
        }

        .chat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 16px;
            cursor: pointer;
            transition: .2s;
            margin-bottom: 4px;
        }

        .chat-item:hover,
        .chat-item.active {
            background: linear-gradient(135deg,
                    rgba(8, 189, 233, .09),
                    rgba(112, 73, 255, .10));
        }

        .avatar {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 15px;
            background: linear-gradient(135deg, #182858, #24366e);
            border: 1px solid #304274;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            overflow: hidden;
            position: relative;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .online-dot {
            position: absolute;
            width: 11px;
            height: 11px;
            background: var(--success);
            border: 2px solid var(--panel);
            border-radius: 50%;
            bottom: 1px;
            right: 1px;
        }

        .chat-info {
            min-width: 0;
            flex: 1;
        }

        .chat-name-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .chat-name {
            font-size: 14px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-time {
            color: var(--muted);
            font-size: 9px;
            white-space: nowrap;
        }

        .chat-preview {
            color: var(--muted);
            font-size: 11px;
            margin-top: 7px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread {
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 10px;
            background: var(--cyan);
            color: #03111b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: bold;
        }

        /* =========================
   MAIN
========================= */

        .main {
            min-width: 0;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at 50% 10%, rgba(112, 73, 255, .07), transparent 30%);
        }

        .welcome {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 30px;
        }

        .welcome-box {
            max-width: 450px;
        }

        .welcome-logo {
            width: 92px;
            height: 92px;
            margin: 0 auto 25px;
            border-radius: 30px;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 46px;
            font-weight: bold;
            box-shadow: 0 25px 70px rgba(112, 73, 255, .25);
        }

        .welcome h2 {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .welcome p {
            color: var(--muted);
            line-height: 2;
            font-size: 13px;
        }

        .chat-window {
            height: 100%;
            display: none;
            flex-direction: column;
        }

        .chat-window.show {
            display: flex;
        }

        .chat-header {
            height: 76px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 0 20px;
            background: rgba(8, 14, 33, .48);
        }

        .back-mobile {
            display: none;
            background: transparent;
            color: white;
            font-size: 20px;
        }

        .header-info {
            flex: 1;
            min-width: 0;
        }

        .header-name {
            font-size: 15px;
            font-weight: bold;
        }

        .header-status {
            color: var(--muted);
            font-size: 10px;
            margin-top: 5px;
        }

        .header-actions {
            display: flex;
            gap: 7px;
        }

        .header-action {
            width: 37px;
            height: 37px;
            border-radius: 11px;
            color: var(--muted);
            background: var(--panel2);
        }

        .header-action:hover {
            color: white;
            background: var(--panel3);
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message-row {
            display: flex;
        }

        .message-row.mine {
            justify-content: flex-start;
        }

        .message-row.theirs {
            justify-content: flex-end;
        }

        .message {
            max-width: min(70%, 560px);
            padding: 11px 14px;
            border-radius: 17px;
            line-height: 1.8;
            font-size: 13px;
            position: relative;
        }

        .mine .message {
            background: linear-gradient(135deg, #254d82, #47318f);
            border-bottom-left-radius: 5px;
        }

        .theirs .message {
            background: var(--panel2);
            border: 1px solid var(--border);
            border-bottom-right-radius: 5px;
        }

        .message-text {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .message-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 5px;
            font-size: 9px;
            color: #b7c2dc;
        }

        .edited {
            color: #9ca9c9;
        }

        .seen {
            color: var(--cyan);
        }

        .composer {
            padding: 14px 18px;
            border-top: 1px solid var(--border);
            background: rgba(8, 14, 33, .7);
            display: flex;
            align-items: flex-end;
            gap: 9px;
        }

        .composer textarea {
            flex: 1;
            min-height: 45px;
            max-height: 140px;
            resize: none;
            border: 1px solid var(--border);
            border-radius: 15px;
            outline: none;
            background: var(--panel2);
            color: white;
            padding: 12px 14px;
            line-height: 1.7;
        }

        .composer button {
            width: 45px;
            height: 45px;
            border-radius: 14px;
        }

        .attach {
            background: var(--panel2);
            color: var(--muted);
        }

        .send {
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            color: white;
        }

        /* =========================
   MESSAGE ATTACHMENTS
========================= */

        .voice-btn {
            width: 45px;
            height: 45px;
            min-width: 45px;
            border-radius: 14px;
            background: var(--panel2);
            border: 1px solid var(--border);
            color: var(--muted);
            transition: .2s;
        }

        .voice-btn:hover {
            color: white;
            border-color: var(--cyan);
        }

        .voice-btn.recording {
            background: rgba(255, 84, 112, .14);
            border-color: var(--danger);
            color: #ff8296;
            animation: recordingPulse 1s infinite;
        }

        @keyframes recordingPulse {
            50% {
                transform: scale(.94);
                opacity: .7;
            }
        }

        .message-image {
            display: block;
            max-width: 330px;
            max-height: 350px;
            border-radius: 13px;
            object-fit: cover;
            cursor: pointer;
            margin-bottom: 4px;
        }

        .message-file {
            min-width: 220px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .05);
        }

        .file-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 11px;
            background: rgba(8, 189, 233, .12);
            color: var(--cyan);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .file-info {
            min-width: 0;
            flex: 1;
        }

        .file-name {
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-size {
            color: var(--muted);
            font-size: 9px;
            margin-top: 5px;
        }

        .file-download {
            color: var(--cyan);
            text-decoration: none;
            font-size: 11px;
        }

        .message-audio {
            width: 250px;
            max-width: 100%;
            height: 38px;
        }

        .upload-status {
            position: fixed;
            left: 50%;
            bottom: 25px;
            transform: translateX(-50%);
            background: rgba(12, 21, 48, .97);
            border: 1px solid var(--border);
            color: white;
            padding: 12px 18px;
            border-radius: 13px;
            font-size: 11px;
            z-index: 200;
            display: none;
        }

        .upload-status.show {
            display: block;
        }


        /* =========================
   PROFILE PANEL
========================= */

        .profile {
            background: rgba(12, 21, 48, .82);
            border-right: 1px solid var(--border);
            overflow-y: auto;
            padding: 22px 18px;
        }

        .profile-title {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 22px;
        }

        .profile-card {
            text-align: center;
        }

        .profile-avatar-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 16px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 30px;
            background: linear-gradient(135deg, #182858, #24366e);
            border: 2px solid #304274;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-online {
            position: absolute;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--success);
            border: 3px solid var(--panel);
            bottom: 3px;
            right: 3px;
        }

        .profile-card h2 {
            font-size: 18px;
        }

        .profile-username {
            color: var(--cyan);
            font-size: 11px;
            margin-top: 6px;
        }

        .profile-bio {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.8;
            margin-top: 12px;
        }

        .profile-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 20px;
        }

        .profile-btn {
            height: 40px;
            border-radius: 12px;
            background: var(--panel2);
            border: 1px solid var(--border);
            color: #dce4f8;
            font-size: 11px;
        }

        .profile-btn:hover {
            border-color: #3a4e7e;
        }

        .profile-section {
            border-top: 1px solid var(--border);
            margin-top: 22px;
            padding-top: 18px;
        }

        .profile-section-title {
            color: var(--muted);
            font-size: 10px;
            margin-bottom: 13px;
        }

        .profile-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 13px;
        }

        .profile-line-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--panel2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cyan);
        }

        .profile-line-text {
            min-width: 0;
        }

        .profile-line-label {
            font-size: 9px;
            color: var(--muted);
        }

        .profile-line-value {
            font-size: 11px;
            margin-top: 3px;
            word-break: break-word;
        }

        /* =========================
   MODAL
========================= */

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(2, 5, 15, .72);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-box {
            width: min(500px, 100%);
            max-height: 90vh;
            overflow-y: auto;
            background: linear-gradient(145deg,
                    rgba(17, 28, 59, .98),
                    rgba(8, 14, 33, .98));
            border: 1px solid var(--border);
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 30px 100px rgba(0, 0, 0, .45);
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .modal-head h2 {
            font-size: 18px;
        }

        .close {
            width: 35px;
            height: 35px;
            border-radius: 11px;
            background: var(--panel2);
            color: var(--muted);
        }

        .edit-avatar {
            text-align: center;
            margin-bottom: 24px;
        }

        .edit-avatar-box {
            width: 90px;
            height: 90px;
            margin: 0 auto 12px;
            border-radius: 27px;
            overflow: hidden;
            background: linear-gradient(135deg, #182858, #24366e);
            border: 1px solid #304274;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
        }

        .edit-avatar-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            padding: 0 15px;
            border-radius: 11px;
            background: var(--panel2);
            border: 1px solid var(--border);
            color: #dce4f8;
            font-size: 10px;
            cursor: pointer;
        }

        .upload-btn input {
            display: none;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 10px;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .field input,
        .field textarea {
            width: 100%;
            background: var(--panel2);
            border: 1px solid var(--border);
            border-radius: 13px;
            color: white;
            outline: none;
            padding: 11px 13px;
        }

        .field textarea {
            min-height: 90px;
            resize: vertical;
        }

        .field input:focus,
        .field textarea:focus {
            border-color: var(--cyan);
        }

        .save-btn {
            width: 100%;
            height: 45px;
            border-radius: 13px;
            color: white;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            font-weight: bold;
        }

        .message-box {
            margin-bottom: 15px;
            padding: 11px 13px;
            border-radius: 12px;
            font-size: 11px;
            display: none;
        }

        .message-box.show {
            display: block;
        }

        .message-box.success {
            background: rgba(53, 212, 154, .1);
            border: 1px solid rgba(53, 212, 154, .25);
            color: #7cf0c2;
        }

        .message-box.error {
            background: rgba(255, 84, 112, .1);
            border: 1px solid rgba(255, 84, 112, .25);
            color: #ff9aac;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }

        .logout-btn {
            width: 100%;
            height: 43px;
            border-radius: 13px;
            background: rgba(255, 84, 112, .08);
            border: 1px solid rgba(255, 84, 112, .18);
            color: #ff8296;
        }

        /* =========================
   EMPTY
========================= */

        .empty {
            text-align: center;
            color: var(--muted);
            padding: 40px 15px;
            font-size: 11px;
        }

        /* =========================
   RESPONSIVE
========================= */

        @media(max-width:1250px) {
            .app {
                grid-template-columns: 86px 330px 1fr;
            }

            .profile {
                display: none;
            }
        }

        @media(max-width:850px) {
            .app {
                grid-template-columns: 86px 1fr;
            }

            .chat-list {
                display: block;
            }

            .main {
                display: none;
            }

            .app.chat-open .chat-list {
                display: none;
            }

            .app.chat-open .main {
                display: flex;
            }

            .back-mobile {
                display: block;
            }
        }

        @media(max-width:600px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .chat-list {
                border: 0;
            }

            .message {
                max-width: 85%;
            }

            .messages {
                padding: 16px;
            }

            .composer {
                padding: 10px;
            }

            .header-actions .header-action:nth-child(1),
            .header-actions .header-action:nth-child(2) {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="app" id="app">

        <!-- SIDEBAR -->

        <aside class="sidebar">

            <div class="logo">M</div>

            <div class="side-nav">

                <button class="side-btn active">
                    <span class="side-icon">◈</span>
                    <span class="side-label">گفتگوها</span>
                </button>

                <button class="side-btn">
                    <span class="side-icon">◎</span>
                    <span class="side-label">گروه‌ها</span>
                </button>

                <button class="side-btn">
                    <span class="side-icon">◇</span>
                    <span class="side-label">کانال‌ها</span>
                </button>

                <button class="side-btn">
                    <span class="side-icon">⌁</span>
                    <span class="side-label">تماس‌ها</span>
                </button>

                <button class="side-btn">
                    <span class="side-icon">☆</span>
                    <span class="side-label">ذخیره‌شده</span>
                </button>

            </div>

            <div class="sidebar-bottom">

                <button class="side-btn" onclick="openMyProfile()">
                    <span class="side-icon">◎</span>
                    <span class="side-label">پروفایل</span>
                </button>

            </div>

        </aside>


        <!-- CHAT LIST -->

        <aside class="chat-list">

            <div class="chat-head">

                <div class="brand-row">

                    <h1>MiraChat</h1>

                    <button class="plus-btn" onclick="openMyProfile()">+</button>

                </div>

                <div class="search-box">

                    <span>⌕</span>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="جستجوی کاربر..."
                        autocomplete="off">

                </div>

            </div>

            <div class="chat-items" id="chatItems">
                <div class="empty">در حال بارگذاری گفتگوها...</div>
            </div>

        </aside>


        <!-- MAIN -->

        <main class="main">

            <section class="welcome" id="welcome">

                <div class="welcome-box">

                    <div class="welcome-logo">M</div>

                    <h2>به MiraChat خوش آمدید</h2>

                    <p>
                        یک گفتگو را انتخاب کنید یا از بخش جستجو،
                        کاربر مورد نظر خود را پیدا کنید.
                    </p>

                </div>

            </section>


            <section class="chat-window" id="chatWindow">

                <header class="chat-header">

                    <button class="back-mobile" onclick="closeChat()">‹</button>

                    <div class="avatar" id="headerAvatar">
                        M
                    </div>

                    <div class="header-info">

                        <div class="header-name" id="headerName">
                            کاربر
                        </div>

                        <div class="header-status" id="headerStatus">
                            آفلاین
                        </div>

                    </div>

                    <div class="header-actions">

                        <button class="header-action">⌕</button>
                        <button class="header-action">⌁</button>

                        <button
                            class="header-action"
                            onclick="openCurrentProfile()">
                            ⋮
                        </button>

                    </div>

                </header>


                <div class="messages" id="messages"></div>


                <form class="composer" id="messageForm">

                    <button
                        type="button"
                        class="attach"
                        id="attachBtn"
                        title="ارسال عکس یا فایل">
                        +
                    </button>

                    <input
                        type="file"
                        id="messageFile"
                        hidden
                        accept="image/*,.pdf,.zip,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx">

                    <button
                        type="button"
                        class="voice-btn"
                        id="voiceBtn"
                        title="ضبط ویس">
                        ●
                    </button>

                    <textarea
                        id="messageInput"
                        placeholder="پیامت را بنویس..."
                        rows="1"></textarea>

                    <button
                        type="submit"
                        class="send">
                        ➤
                    </button>

                </form>
            </section>

        </main>


        <!-- PROFILE -->

        <aside class="profile">

            <div class="profile-title">
                پروفایل
            </div>

            <div id="profileContent">

                <div class="profile-card">

                    <div class="profile-avatar-wrap">

                        <div class="profile-avatar" id="profileAvatar">
                            M
                        </div>

                        <div
                            class="profile-online"
                            id="profileOnline"></div>

                    </div>

                    <h2 id="profileName">
                        -
                    </h2>

                    <div class="profile-username" id="profileUsername">
                        @-
                    </div>

                    <div class="profile-bio" id="profileBio">
                        -
                    </div>

                </div>

                <div class="profile-buttons">

                    <button
                        class="profile-btn"
                        onclick="openCurrentProfile()">
                        پروفایل
                    </button>

                    <button
                        class="profile-btn">
                        جستجو
                    </button>

                </div>

            </div>

        </aside>

    </div>


    <!-- PROFILE MODAL -->

    <div class="modal" id="profileModal">

        <div class="modal-box">

            <div class="modal-head">

                <h2>پروفایل من</h2>

                <button
                    class="close"
                    onclick="closeModal('profileModal')">
                    ×
                </button>

            </div>


            <div id="profileMessage" class="message-box"></div>


            <div class="edit-avatar">

                <div class="edit-avatar-box" id="editAvatar">
                    M
                </div>

                <label class="upload-btn">

                    تغییر عکس پروفایل

                    <input
                        type="file"
                        id="avatarInput"
                        accept="image/jpeg,image/png,image/webp">

                </label>

            </div>


            <form id="profileForm">

                <div class="field">

                    <label>نام نمایشی</label>

                    <input
                        type="text"
                        id="editFullName"
                        maxlength="100">

                </div>


                <div class="field">

                    <label>Username</label>

                    <input
                        type="text"
                        id="editUsername"
                        disabled>

                </div>


                <div class="field">

                    <label>ایمیل</label>

                    <input
                        type="email"
                        id="editEmail"
                        disabled>

                </div>


                <div class="field">

                    <label>Bio</label>

                    <textarea
                        id="editBio"
                        maxlength="255"
                        placeholder="کمی درباره خودت بنویس..."></textarea>

                </div>


                <button
                    type="submit"
                    class="save-btn">
                    ذخیره تغییرات
                </button>

            </form>


            <div class="divider"></div>


            <h3 style="font-size:14px;margin-bottom:15px;">
                تغییر رمز عبور
            </h3>


            <form id="passwordForm">

                <div class="field">

                    <label>رمز فعلی</label>

                    <input
                        type="password"
                        id="currentPassword">

                </div>


                <div class="field">

                    <label>رمز جدید</label>

                    <input
                        type="password"
                        id="newPassword">

                </div>


                <div class="field">

                    <label>تکرار رمز جدید</label>

                    <input
                        type="password"
                        id="confirmPassword">

                </div>


                <button
                    type="submit"
                    class="save-btn">
                    تغییر رمز عبور
                </button>

            </form>


            <div class="divider"></div>


            <button
                class="logout-btn"
                onclick="logout()">
                خروج از حساب
            </button>

        </div>

    </div>


    <script>
        let currentUser = null;
        let currentChat = null;
        let messageTimer = null;
        let chatTimer = null;


        /* =========================
           HELPERS
        ========================= */

        function api(url, options = {}) {
            return fetch(url, options)
                .then(response => response.json());
        }


        function avatarHTML(user, className = "avatar") {

            const name = user.full_name || user.username || "M";

            const letter = name
                .trim()
                .charAt(0)
                .toUpperCase();

            let html = `
        <div class="${className}">
    `;

            if (user.avatar) {

                html += `
            <img
                src="uploads/${encodeURIComponent(user.avatar)}"
                alt=""
            >
        `;

            } else {

                html += letter;

            }

            html += `</div>`;

            return html;
        }


        function setAvatar(element, user) {

            if (!element) return;

            const name = user.full_name || user.username || "M";

            const letter = name
                .trim()
                .charAt(0)
                .toUpperCase();

            if (user.avatar) {

                element.innerHTML = `
            <img
                src="uploads/${encodeURIComponent(user.avatar)}"
                alt=""
            >
        `;

            } else {

                element.innerHTML = letter;

            }
        }


        function formatTime(dateString) {

            if (!dateString) return "";

            const date = new Date(
                dateString.replace(" ", "T")
            );

            if (isNaN(date.getTime())) return "";

            return date.toLocaleTimeString(
                "fa-IR", {
                    hour: "2-digit",
                    minute: "2-digit"
                }
            );
        }


        /* =========================
           LOAD MY PROFILE
        ========================= */

        async function loadMyProfile() {

            const result = await api(
                "api.php?action=profile"
            );

            if (!result.success) return;

            currentUser = result.user;

            updateMyProfileUI();
        }


        function updateMyProfileUI() {

            if (!currentUser) return;

            const name =
                currentUser.full_name ||
                currentUser.username ||
                "M";

            document.getElementById("profileName").textContent =
                name;

            document.getElementById("profileUsername").textContent =
                "@" + currentUser.username;

            document.getElementById("profileBio").textContent =
                currentUser.bio ||
                "هنوز Bio ثبت نشده است.";

            setAvatar(
                document.getElementById("profileAvatar"),
                currentUser
            );
        }


        /* =========================
           OPEN MY PROFILE
        ========================= */

        async function openMyProfile() {

            await loadMyProfile();

            if (!currentUser) return;

            document.getElementById("editFullName").value =
                currentUser.full_name || "";

            document.getElementById("editUsername").value =
                currentUser.username || "";

            document.getElementById("editEmail").value =
                currentUser.email || "";

            document.getElementById("editBio").value =
                currentUser.bio || "";

            setAvatar(
                document.getElementById("editAvatar"),
                currentUser
            );

            clearProfileMessage();

            document
                .getElementById("profileModal")
                .classList.add("show");
        }


        function closeModal(id) {

            document
                .getElementById(id)
                .classList.remove("show");

        }


        /* =========================
           PROFILE MESSAGE
        ========================= */

        function showProfileMessage(
            message,
            type = "success"
        ) {

            const box =
                document.getElementById("profileMessage");

            box.textContent = message;

            box.className =
                "message-box show " + type;
        }


        function clearProfileMessage() {

            const box =
                document.getElementById("profileMessage");

            box.textContent = "";

            box.className =
                "message-box";
        }


        /* =========================
           UPDATE PROFILE
        ========================= */

        document
            .getElementById("profileForm")
            .addEventListener("submit", async function(e) {

                e.preventDefault();

                const formData = new FormData();

                formData.append(
                    "action",
                    "update_profile"
                );

                formData.append(
                    "full_name",
                    document.getElementById("editFullName").value
                );

                formData.append(
                    "bio",
                    document.getElementById("editBio").value
                );

                const result = await api(
                    "api.php", {
                        method: "POST",
                        body: formData
                    }
                );

                if (!result.success) {

                    showProfileMessage(
                        result.message || "خطا در ذخیره اطلاعات.",
                        "error"
                    );

                    return;
                }

                await loadMyProfile();

                showProfileMessage(
                    "پروفایل با موفقیت ذخیره شد."
                );

                setTimeout(() => {

                    closeModal("profileModal");

                }, 700);

            });


        /* =========================
           AVATAR UPLOAD
        ========================= */

        document
            .getElementById("avatarInput")
            .addEventListener("change", async function() {

                const file = this.files[0];

                if (!file) return;

                const formData = new FormData();

                formData.append(
                    "action",
                    "upload_avatar"
                );

                formData.append(
                    "avatar",
                    file
                );

                showProfileMessage(
                    "در حال آپلود تصویر..."
                );

                const result = await api(
                    "api.php", {
                        method: "POST",
                        body: formData
                    }
                );

                if (!result.success) {

                    showProfileMessage(
                        result.message || "آپلود ناموفق بود.",
                        "error"
                    );

                    return;
                }

                await loadMyProfile();

                setAvatar(
                    document.getElementById("editAvatar"),
                    currentUser
                );

                showProfileMessage(
                    "تصویر پروفایل تغییر کرد."
                );

            });


        /* =========================
           CHANGE PASSWORD
        ========================= */

        document
            .getElementById("passwordForm")
            .addEventListener("submit", async function(e) {

                e.preventDefault();

                const formData = new FormData();

                formData.append(
                    "action",
                    "change_password"
                );

                formData.append(
                    "current_password",
                    document.getElementById("currentPassword").value
                );

                formData.append(
                    "new_password",
                    document.getElementById("newPassword").value
                );

                formData.append(
                    "confirm_password",
                    document.getElementById("confirmPassword").value
                );

                const result = await api(
                    "api.php", {
                        method: "POST",
                        body: formData
                    }
                );

                if (!result.success) {

                    showProfileMessage(
                        result.message || "خطا در تغییر رمز.",
                        "error"
                    );

                    return;
                }

                document.getElementById(
                    "passwordForm"
                ).reset();

                showProfileMessage(
                    "رمز عبور با موفقیت تغییر کرد."
                );

            });


        /* =========================
           SEARCH
        ========================= */

        let searchTimeout;

        document
            .getElementById("searchInput")
            .addEventListener("input", function() {

                clearTimeout(searchTimeout);

                const q = this.value.trim();

                if (!q) {

                    loadChats();

                    return;
                }

                searchTimeout = setTimeout(
                    () => searchUsers(q),
                    300
                );

            });


        async function searchUsers(q) {

            const result = await api(
                "api.php?action=search&q=" +
                encodeURIComponent(q)
            );

            const container =
                document.getElementById("chatItems");

            if (!result.success || !result.users.length) {

                container.innerHTML =
                    `<div class="empty">کاربری پیدا نشد.</div>`;

                return;
            }

            container.innerHTML = "";

            result.users.forEach(user => {

                const item =
                    document.createElement("div");

                item.className = "chat-item";

                item.innerHTML = `

            ${avatarHTML(user)}

            <div class="chat-info">

                <div class="chat-name">
                    ${escapeHTML(user.full_name)}
                </div>

                <div class="chat-preview">
                    @${escapeHTML(user.username)}
                </div>

            </div>

        `;

                item.onclick = () =>
                    openChat(user.id);

                container.appendChild(item);

            });
        }


        /* =========================
           CHATS
        ========================= */

        async function loadChats() {

            const result = await api(
                "api.php?action=chats"
            );

            const container =
                document.getElementById("chatItems");

            if (!result.success) return;

            if (!result.chats.length) {

                container.innerHTML =
                    `<div class="empty">
                هنوز گفتگویی ندارید.
            </div>`;

                return;
            }

            container.innerHTML = "";

            result.chats.forEach(chat => {

                const item =
                    document.createElement("div");

                item.className =
                    "chat-item" +
                    (
                        currentChat &&
                        currentChat.id == chat.id ?
                        " active" :
                        ""
                    );

                const preview =
                    chat.last_message ||
                    "شروع گفتگو";

                const unread =
                    Number(chat.unread || 0);

                item.innerHTML = `

            ${avatarHTML(chat)}

            <div class="chat-info">

                <div class="chat-name-row">

                    <div class="chat-name">
                        ${escapeHTML(chat.full_name)}
                    </div>

                    <div class="chat-time">
                        ${formatTime(chat.last_message_time)}
                    </div>

                </div>

                <div class="chat-preview">
                    ${escapeHTML(preview)}
                </div>

            </div>

            ${
                unread > 0
                ? `<div class="unread">${unread}</div>`
                : ""
            }

        `;

                item.onclick = () =>
                    openChat(chat.id);

                container.appendChild(item);

            });
        }


        /* =========================
           OPEN CHAT
        ========================= */

        async function openChat(userId) {

            const result = await api(
                "api.php?action=user&id=" +
                encodeURIComponent(userId)
            );

            if (!result.success) return;

            currentChat = result.user;

            document
                .getElementById("app")
                .classList.add("chat-open");

            document
                .getElementById("welcome")
                .style.display = "none";

            document
                .getElementById("chatWindow")
                .classList.add("show");

            document.getElementById("headerName").textContent =
                currentChat.full_name;

            document.getElementById("headerStatus").textContent =
                currentChat.status === "online" ?
                "آنلاین" :
                "آخرین بازدید: " +
                formatTime(currentChat.last_seen);

            setAvatar(
                document.getElementById("headerAvatar"),
                currentChat
            );

            updateRightProfile(currentChat);

            await loadMessages();

            startMessageTimer();

            loadChats();
        }


        function closeChat() {

            document
                .getElementById("app")
                .classList.remove("chat-open");

            currentChat = null;

            clearInterval(messageTimer);

            document
                .getElementById("chatWindow")
                .classList.remove("show");

            document
                .getElementById("welcome")
                .style.display = "flex";
        }


        function openCurrentProfile() {

            if (!currentChat) return;

            updateRightProfile(currentChat);

            document
                .getElementById("profileContent")
                .scrollIntoView({
                    behavior: "smooth"
                });
        }


        /* =========================
           RIGHT PROFILE
        ========================= */

        function updateRightProfile(user) {

            document.getElementById(
                    "profileName"
                ).textContent =
                user.full_name;

            document.getElementById(
                    "profileUsername"
                ).textContent =
                "@" + user.username;

            document.getElementById(
                    "profileBio"
                ).textContent =
                user.bio ||
                "Bio ثبت نشده است.";

            setAvatar(
                document.getElementById("profileAvatar"),
                user
            );

            document.getElementById(
                    "profileOnline"
                ).style.display =
                user.status === "online" ?
                "block" :
                "none";
        }


        /* =========================
           MESSAGES
        ========================= */

        async function loadMessages() {

            if (!currentChat) return;

            const result = await api(
                "api.php?action=messages&user_id=" +
                encodeURIComponent(currentChat.id)
            );

            if (!result.success) return;

            const container =
                document.getElementById("messages");

            container.innerHTML = "";

            if (!result.messages.length) {

                container.innerHTML =
                    `<div class="empty">
                هنوز پیامی وجود ندارد.
                اولین پیام را بفرستید.
            </div>`;

                return;
            }

            result.messages.forEach(message => {

                const mine =
                    Number(message.sender_id) ===
                    Number(currentUser.id);

                const row =
                    document.createElement("div");

                row.className =
                    "message-row " +
                    (
                        mine ?
                        "mine" :
                        "theirs"
                    );

                const time =
                    formatTime(message.created_at);

                let content = "";

                /*
                 * TEXT
                 */

                if (
                    !message.message_type ||
                    message.message_type === "text"
                ) {

                    content = `
                <div class="message-text">
                    ${escapeHTML(message.message)}
                </div>
            `;

                }

                /*
                 * IMAGE
                 */
                else if (
                    message.message_type === "image"
                ) {

                    const path =
                        escapeHTML(
                            message.file_path || ""
                        );

                    content = `
                <img
                    class="message-image"
                    src="${path}"
                    alt="${escapeHTML(message.file_name || "تصویر")}"
                    onclick="window.open('${path}', '_blank')"
                >
            `;

                }

                /*
                 * FILE
                 */
                else if (
                    message.message_type === "file"
                ) {

                    const path =
                        escapeHTML(
                            message.file_path || ""
                        );

                    content = `
                <div class="message-file">

                    <div class="file-icon">
                        FILE
                    </div>

                    <div class="file-info">

                        <div class="file-name">
                            ${escapeHTML(
                                message.file_name || "فایل"
                            )}
                        </div>

                        <div class="file-size">
                            ${formatFileSize(
                                message.file_size
                            )}
                        </div>

                    </div>

                    <a
                        class="file-download"
                        href="${path}"
                        target="_blank"
                        download>
                        دریافت
                    </a>

                </div>
            `;

                }

                /*
                 * VOICE
                 */
                else if (
                    message.message_type === "voice"
                ) {

                    const path =
                        escapeHTML(
                            message.file_path || ""
                        );

                    content = `
                <audio
                    class="message-audio"
                    controls
                    preload="metadata">
                    <source
                        src="${path}"
                        type="${escapeHTML(
                            message.mime_type || "audio/webm"
                        )}">
                    مرورگر شما از پخش این فایل صوتی پشتیبانی نمی‌کند.
                </audio>
            `;

                }

                row.innerHTML = `

            <div class="message">

                ${content}

                <div class="message-meta">

                    ${
                        message.edited == 1
                        ? `<span class="edited">
                            ویرایش‌شده
                           </span>`
                        : ""
                    }

                    <span>
                        ${time}
                    </span>

                    ${
                        mine
                        ? `
                            <span class="seen">
                                ${
                                    message.is_read == 1
                                    ? "✓✓"
                                    : "✓"
                                }
                            </span>
                        `
                        : ""
                    }

                </div>

            </div>

        `;

                container.appendChild(row);

            });

            container.scrollTop =
                container.scrollHeight;
        }

        /* =========================
   FILE SIZE
========================= */

        function formatFileSize(bytes) {

            bytes = Number(bytes || 0);

            if (bytes < 1024) {
                return bytes + " B";
            }

            if (bytes < 1024 * 1024) {
                return (
                    (bytes / 1024).toFixed(1) +
                    " KB"
                );
            }

            return (
                (bytes / (1024 * 1024)).toFixed(1) +
                " MB"
            );
        }


        /* =========================
           UPLOAD STATUS
        ========================= */

        function showUploadStatus(text) {

            let box =
                document.getElementById("uploadStatus");

            if (!box) {

                box =
                    document.createElement("div");

                box.id = "uploadStatus";

                box.className =
                    "upload-status";

                document.body.appendChild(box);
            }

            box.textContent = text;

            box.classList.add("show");
        }


        function hideUploadStatus() {

            const box =
                document.getElementById("uploadStatus");

            if (box) {
                box.classList.remove("show");
            }
        }


        /* =========================
           UPLOAD IMAGE / FILE
        ========================= */

        document
            .getElementById("attachBtn")
            .addEventListener("click", function() {

                if (!currentChat) {

                    alert("اول یک گفتگو را انتخاب کنید.");

                    return;
                }

                document
                    .getElementById("messageFile")
                    .click();

            });


        document
            .getElementById("messageFile")
            .addEventListener("change", async function() {

                const file = this.files[0];

                if (!file || !currentChat) {
                    this.value = "";
                    return;
                }

                const formData =
                    new FormData();

                formData.append(
                    "action",
                    "upload_message"
                );

                formData.append(
                    "receiver_id",
                    currentChat.id
                );

                formData.append(
                    "file",
                    file
                );

                showUploadStatus(
                    "در حال ارسال فایل..."
                );

                try {

                    const result =
                        await api(
                            "api.php", {
                                method: "POST",
                                body: formData
                            }
                        );

                    if (!result.success) {

                        alert(
                            result.message ||
                            "ارسال فایل ناموفق بود."
                        );

                        return;
                    }

                    await loadMessages();

                    loadChats();

                } catch (error) {

                    console.error(error);

                    alert(
                        "خطا در ارسال فایل."
                    );

                } finally {

                    hideUploadStatus();

                    this.value = "";

                }

            });


        /* =========================
           VOICE RECORDING
        ========================= */

        let mediaRecorder = null;
        let voiceChunks = [];
        let voiceStream = null;


        document
            .getElementById("voiceBtn")
            .addEventListener("click", async function() {

                if (!currentChat) {

                    alert(
                        "اول یک گفتگو را انتخاب کنید."
                    );

                    return;
                }

                /*
                 * STOP RECORDING
                 */

                if (
                    mediaRecorder &&
                    mediaRecorder.state === "recording"
                ) {

                    mediaRecorder.stop();

                    return;
                }

                /*
                 * START RECORDING
                 */

                if (
                    !navigator.mediaDevices ||
                    !navigator.mediaDevices.getUserMedia
                ) {

                    alert(
                        "مرورگر شما امکان ضبط صدا را ندارد."
                    );

                    return;
                }

                try {

                    voiceStream =
                        await navigator.mediaDevices
                        .getUserMedia({
                            audio: true
                        });

                    voiceChunks = [];

                    mediaRecorder =
                        new MediaRecorder(
                            voiceStream
                        );

                    mediaRecorder.ondataavailable =
                        function(event) {

                            if (
                                event.data &&
                                event.data.size > 0
                            ) {
                                voiceChunks.push(
                                    event.data
                                );
                            }

                        };

                    mediaRecorder.onstop =
                        async function() {

                            if (voiceStream) {

                                voiceStream
                                    .getTracks()
                                    .forEach(
                                        track =>
                                        track.stop()
                                    );

                            }

                            document
                                .getElementById("voiceBtn")
                                .classList.remove(
                                    "recording"
                                );

                            if (!voiceChunks.length) {
                                return;
                            }

                            const blob =
                                new Blob(
                                    voiceChunks, {
                                        type: mediaRecorder.mimeType ||
                                            "audio/webm"
                                    }
                                );

                            await uploadVoice(blob);

                        };

                    mediaRecorder.start();

                    this.classList.add(
                        "recording"
                    );

                    this.title =
                        "برای توقف ضبط کلیک کنید";

                } catch (error) {

                    console.error(error);

                    alert(
                        "دسترسی به میکروفون داده نشد."
                    );

                }

            });


        /* =========================
           UPLOAD VOICE
        ========================= */

        async function uploadVoice(blob) {

            if (!currentChat) return;

            const extension =
                blob.type.includes("ogg") ?
                "ogg" :
                blob.type.includes("mp4") ?
                "m4a" :
                "webm";

            const voiceFile =
                new File(
                    [blob],
                    "voice_" +
                    Date.now() +
                    "." +
                    extension, {
                        type: blob.type ||
                            "audio/webm"
                    }
                );

            const formData =
                new FormData();

            formData.append(
                "action",
                "upload_message"
            );

            formData.append(
                "receiver_id",
                currentChat.id
            );

            formData.append(
                "file",
                voiceFile
            );

            showUploadStatus(
                "در حال ارسال ویس..."
            );

            try {

                const result =
                    await api(
                        "api.php", {
                            method: "POST",
                            body: formData
                        }
                    );

                if (!result.success) {

                    alert(
                        result.message ||
                        "ارسال ویس ناموفق بود."
                    );

                    return;
                }

                await loadMessages();

                loadChats();

            } catch (error) {

                console.error(error);

                alert(
                    "خطا در ارسال ویس."
                );

            } finally {

                hideUploadStatus();

                document
                    .getElementById("voiceBtn")
                    .classList.remove(
                        "recording"
                    );

                document
                    .getElementById("voiceBtn")
                    .title =
                    "ضبط ویس";

            }

        }

        /* =========================
           SEND MESSAGE
        ========================= */

        document
            .getElementById("messageForm")
            .addEventListener("submit", async function(e) {

                e.preventDefault();

                if (!currentChat) return;

                const input =
                    document.getElementById("messageInput");

                const message =
                    input.value.trim();

                if (!message) return;

                const formData =
                    new FormData();

                formData.append(
                    "action",
                    "send"
                );

                formData.append(
                    "receiver_id",
                    currentChat.id
                );

                formData.append(
                    "message",
                    message
                );

                const result = await api(
                    "api.php", {
                        method: "POST",
                        body: formData
                    }
                );

                if (!result.success) {

                    alert(
                        result.message ||
                        "ارسال پیام ناموفق بود."
                    );

                    return;
                }

                input.value = "";

                input.style.height = "45px";

                await loadMessages();

                loadChats();

            });


        /* =========================
           ENTER TO SEND
        ========================= */

        document
            .getElementById("messageInput")
            .addEventListener("keydown", function(e) {

                if (
                    e.key === "Enter" &&
                    !e.shiftKey
                ) {

                    e.preventDefault();

                    document
                        .getElementById("messageForm")
                        .requestSubmit();

                }

            });


        /* =========================
           AUTO RESIZE
        ========================= */

        document
            .getElementById("messageInput")
            .addEventListener("input", function() {

                this.style.height = "45px";

                this.style.height =
                    Math.min(
                        this.scrollHeight,
                        140
                    ) + "px";

            });


        /* =========================
           HEARTBEAT
        ========================= */

        async function heartbeat() {

            await api(
                "api.php?action=heartbeat"
            );

        }

        setInterval(
            heartbeat,
            25000
        );


        /* =========================
           LOGOUT
        ========================= */

        async function logout() {

            const result = await api(
                "api.php?action=logout"
            );

            if (result.success) {

                window.location.href =
                    "login.php";

            }

        }


        /* =========================
           ESCAPE HTML
        ========================= */

        function escapeHTML(value) {

            const div =
                document.createElement("div");

            div.textContent =
                value ?? "";

            return div.innerHTML;
        }


        /* =========================
           CLOSE MODAL OUTSIDE
        ========================= */

        document
            .getElementById("profileModal")
            .addEventListener("click", function(e) {

                if (e.target === this) {

                    closeModal("profileModal");

                }

            });


        /* =========================
           INITIAL LOAD
        ========================= */

        (async function() {

            await loadMyProfile();

            await loadChats();

            heartbeat();

            chatTimer = setInterval(
                loadChats,
                7000
            );

        })();
    </script>

</body>

</html>