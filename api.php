<?php

require_once "config.php";

if (!isLoggedIn()) {
    jsonResponse([
        "success" => false,
        "message" => "Unauthorized"
    ]);
}

$me = (int)$_SESSION["user_id"];

$action = $_GET["action"] ?? $_POST["action"] ?? "";

switch ($action) {

    /* =========================
       HEARTBEAT
    ========================= */

    case "heartbeat":

        $stmt = $pdo->prepare("
            UPDATE users
            SET status='online', last_seen=NOW()
            WHERE id=?
        ");

        $stmt->execute([$me]);

        jsonResponse([
            "success" => true
        ]);

        break;


    /* =========================
       LOGOUT
    ========================= */

    case "logout":

        $stmt = $pdo->prepare("
            UPDATE users
            SET status='offline', last_seen=NOW()
            WHERE id=?
        ");

        $stmt->execute([$me]);

        session_destroy();

        jsonResponse([
            "success" => true
        ]);

        break;


    /* =========================
       MY PROFILE
    ========================= */

    case "profile":

        $stmt = $pdo->prepare("
            SELECT
                id,
                username,
                full_name,
                email,
                avatar,
                bio,
                status,
                last_seen,
                created_at
            FROM users
            WHERE id=?
            LIMIT 1
        ");

        $stmt->execute([$me]);

        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse([
                "success" => false,
                "message" => "پروفایل پیدا نشد."
            ]);
        }

        jsonResponse([
            "success" => true,
            "user" => $user
        ]);

        break;


    /* =========================
       UPDATE PROFILE
    ========================= */

    case "update_profile":

        $fullName = trim($_POST["full_name"] ?? "");
        $bio = trim($_POST["bio"] ?? "");

        if ($fullName === "") {
            jsonResponse([
                "success" => false,
                "message" => "نام نمی‌تواند خالی باشد."
            ]);
        }

        if (mb_strlen($fullName) > 100) {
            jsonResponse([
                "success" => false,
                "message" => "نام بیش از حد طولانی است."
            ]);
        }

        if (mb_strlen($bio) > 255) {
            jsonResponse([
                "success" => false,
                "message" => "Bio بیش از حد طولانی است."
            ]);
        }

        $stmt = $pdo->prepare("
            UPDATE users
            SET full_name=?, bio=?
            WHERE id=?
        ");

        $stmt->execute([
            $fullName,
            $bio,
            $me
        ]);

        jsonResponse([
            "success" => true,
            "message" => "پروفایل با موفقیت بروزرسانی شد."
        ]);

        break;


    /* =========================
       CHANGE PASSWORD
    ========================= */

    case "change_password":

        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        if (
            $currentPassword === "" ||
            $newPassword === "" ||
            $confirmPassword === ""
        ) {
            jsonResponse([
                "success" => false,
                "message" => "همه فیلدها را کامل کنید."
            ]);
        }

        if (strlen($newPassword) < 6) {
            jsonResponse([
                "success" => false,
                "message" => "رمز جدید باید حداقل ۶ کاراکتر باشد."
            ]);
        }

        if ($newPassword !== $confirmPassword) {
            jsonResponse([
                "success" => false,
                "message" => "تکرار رمز جدید صحیح نیست."
            ]);
        }

        $stmt = $pdo->prepare("
            SELECT password
            FROM users
            WHERE id=?
            LIMIT 1
        ");

        $stmt->execute([$me]);

        $user = $stmt->fetch();

        if (
            !$user ||
            !password_verify($currentPassword, $user["password"])
        ) {
            jsonResponse([
                "success" => false,
                "message" => "رمز فعلی اشتباه است."
            ]);
        }

        $newHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            UPDATE users
            SET password=?
            WHERE id=?
        ");

        $stmt->execute([
            $newHash,
            $me
        ]);

        jsonResponse([
            "success" => true,
            "message" => "رمز عبور با موفقیت تغییر کرد."
        ]);

        break;


    /* =========================
       UPLOAD AVATAR
    ========================= */

    case "upload_avatar":

        if (!isset($_FILES["avatar"])) {
            jsonResponse([
                "success" => false,
                "message" => "هیچ تصویری انتخاب نشده است."
            ]);
        }

        $file = $_FILES["avatar"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            jsonResponse([
                "success" => false,
                "message" => "آپلود تصویر ناموفق بود."
            ]);
        }

        if ($file["size"] > 3 * 1024 * 1024) {
            jsonResponse([
                "success" => false,
                "message" => "حجم تصویر نباید بیشتر از ۳ مگابایت باشد."
            ]);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        $allowedTypes = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        if (!isset($allowedTypes[$mime])) {
            jsonResponse([
                "success" => false,
                "message" => "فرمت تصویر مجاز نیست. فقط JPG، PNG و WEBP."
            ]);
        }

        $extension = $allowedTypes[$mime];

        $uploadDir = __DIR__ . "/uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = "avatar_" .
            $me . "_" .
            bin2hex(random_bytes(8)) .
            "." .
            $extension;

        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file(
            $file["tmp_name"],
            $destination
        )) {
            jsonResponse([
                "success" => false,
                "message" => "ذخیره تصویر انجام نشد."
            ]);
        }

        $stmt = $pdo->prepare("
            SELECT avatar
            FROM users
            WHERE id=?
        ");

        $stmt->execute([$me]);

        $oldAvatar = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            UPDATE users
            SET avatar=?
            WHERE id=?
        ");

        $stmt->execute([
            $fileName,
            $me
        ]);

        if (
            $oldAvatar &&
            $oldAvatar !== "default.png"
        ) {
            $oldPath = $uploadDir . basename($oldAvatar);

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        jsonResponse([
            "success" => true,
            "message" => "تصویر پروفایل با موفقیت تغییر کرد.",
            "avatar" => $fileName
        ]);

        break;


    /* =========================
       SEARCH USERS
    ========================= */

    case "search":

        $q = trim($_GET["q"] ?? "");

        if ($q === "") {
            jsonResponse([
                "success" => true,
                "users" => []
            ]);
        }

        $stmt = $pdo->prepare("
            SELECT
                id,
                username,
                full_name,
                avatar,
                bio,
                status,
                last_seen
            FROM users
            WHERE id != ?
            AND (
                username LIKE ?
                OR full_name LIKE ?
            )
            ORDER BY
                CASE WHEN status='online' THEN 0 ELSE 1 END,
                full_name ASC
            LIMIT 30
        ");

        $like = "%" . $q . "%";

        $stmt->execute([
            $me,
            $like,
            $like
        ]);

        jsonResponse([
            "success" => true,
            "users" => $stmt->fetchAll()
        ]);

        break;


    /* =========================
       CHAT LIST
    ========================= */

    case "chats":

        $stmt = $pdo->prepare("
            SELECT
                u.id,
                u.username,
                u.full_name,
                u.avatar,
                u.bio,
                u.status,
                u.last_seen,

                (
                    SELECT m.message
                    FROM messages m
                    WHERE
                    (
                        m.sender_id = ?
                        AND m.receiver_id = u.id
                    )
                    OR
                    (
                        m.sender_id = u.id
                        AND m.receiver_id = ?
                    )
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_message,

                (
                    SELECT m.message_type
                    FROM messages m
                    WHERE
                    (
                        m.sender_id = ?
                        AND m.receiver_id = u.id
                    )
                    OR
                    (
                        m.sender_id = u.id
                        AND m.receiver_id = ?
                    )
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_message_type,

                (
                    SELECT m.created_at
                    FROM messages m
                    WHERE
                    (
                        m.sender_id = ?
                        AND m.receiver_id = u.id
                    )
                    OR
                    (
                        m.sender_id = u.id
                        AND m.receiver_id = ?
                    )
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_message_time,

                (
                    SELECT COUNT(*)
                    FROM messages m
                    WHERE
                        m.sender_id = u.id
                        AND m.receiver_id = ?
                        AND m.is_read = 0
                ) AS unread

            FROM users u

            WHERE u.id != ?

            AND EXISTS (
                SELECT 1
                FROM messages m2
                WHERE
                (
                    m2.sender_id = ?
                    AND m2.receiver_id = u.id
                )
                OR
                (
                    m2.sender_id = u.id
                    AND m2.receiver_id = ?
                )
            )

            ORDER BY last_message_time DESC
        ");

        $stmt->execute([
            $me,
            $me,
            $me,
            $me,
            $me,
            $me,
            $me,
            $me,
            $me,
            $me
        ]);

        jsonResponse([
            "success" => true,
            "chats" => $stmt->fetchAll()
        ]);

        break;


    /* =========================
       USER INFO
    ========================= */

    case "user":

        $id = (int)($_GET["id"] ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                id,
                username,
                full_name,
                avatar,
                bio,
                status,
                last_seen,
                created_at
            FROM users
            WHERE id=?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $user = $stmt->fetch();

        jsonResponse([
            "success" => (bool)$user,
            "user" => $user
        ]);

        break;


    /* =========================
       GET MESSAGES
    ========================= */

    case "messages":

        $userId = (int)($_GET["user_id"] ?? 0);

        if ($userId <= 0) {
            jsonResponse([
                "success" => false,
                "message" => "Invalid user"
            ]);
        }

        $pdo->prepare("
            UPDATE messages
            SET is_read=1
            WHERE sender_id=?
            AND receiver_id=?
        ")->execute([
            $userId,
            $me
        ]);

        $stmt = $pdo->prepare("
            SELECT
                m.*,
                u.full_name,
                u.username,
                u.avatar
            FROM messages m
            JOIN users u ON u.id=m.sender_id
            WHERE
            (
                m.sender_id=?
                AND m.receiver_id=?
            )
            OR
            (
                m.sender_id=?
                AND m.receiver_id=?
            )
            ORDER BY m.id ASC
            LIMIT 500
        ");

        $stmt->execute([
            $me,
            $userId,
            $userId,
            $me
        ]);

        jsonResponse([
            "success" => true,
            "messages" => $stmt->fetchAll()
        ]);

        break;


    /* =========================
       SEND TEXT MESSAGE
    ========================= */

    case "send":

        $receiver = (int)($_POST["receiver_id"] ?? 0);
        $message = trim($_POST["message"] ?? "");

        if ($receiver <= 0 || $message === "") {
            jsonResponse([
                "success" => false,
                "message" => "پیام خالی است."
            ]);
        }

        if (mb_strlen($message) > 5000) {
            jsonResponse([
                "success" => false,
                "message" => "پیام بیش از حد طولانی است."
            ]);
        }

        if ($receiver === $me) {
            jsonResponse([
                "success" => false,
                "message" => "نمی‌توانید به خودتان پیام بفرستید."
            ]);
        }

        $check = $pdo->prepare("
            SELECT id
            FROM users
            WHERE id=?
        ");

        $check->execute([$receiver]);

        if (!$check->fetch()) {
            jsonResponse([
                "success" => false,
                "message" => "کاربر پیدا نشد."
            ]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO messages
            (
                sender_id,
                receiver_id,
                message,
                message_type
            )
            VALUES (?, ?, ?, 'text')
        ");

        $stmt->execute([
            $me,
            $receiver,
            $message
        ]);

        jsonResponse([
            "success" => true,
            "id" => $pdo->lastInsertId()
        ]);

        break;


    /* =========================
       UPLOAD CHAT MEDIA
       IMAGE / FILE / VOICE
    ========================= */

    case "upload_message":

        $receiver = (int)($_POST["receiver_id"] ?? 0);

        if ($receiver <= 0) {
            jsonResponse([
                "success" => false,
                "message" => "گیرنده نامعتبر است."
            ]);
        }

        if ($receiver === $me) {
            jsonResponse([
                "success" => false,
                "message" => "نمی‌توانید برای خودتان فایل بفرستید."
            ]);
        }

        $check = $pdo->prepare("
            SELECT id
            FROM users
            WHERE id=?
            LIMIT 1
        ");

        $check->execute([$receiver]);

        if (!$check->fetch()) {
            jsonResponse([
                "success" => false,
                "message" => "کاربر پیدا نشد."
            ]);
        }

        if (!isset($_FILES["file"])) {
            jsonResponse([
                "success" => false,
                "message" => "فایلی انتخاب نشده است."
            ]);
        }

        $file = $_FILES["file"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            jsonResponse([
                "success" => false,
                "message" => "آپلود فایل ناموفق بود."
            ]);
        }

        if ($file["size"] <= 0) {
            jsonResponse([
                "success" => false,
                "message" => "فایل خالی است."
            ]);
        }

        /*
         * حداکثر حجم:
         * عکس: 10MB
         * فایل: 20MB
         * ویس: 10MB
         */

        $maxSize = 20 * 1024 * 1024;

        if ($file["size"] > $maxSize) {
            jsonResponse([
                "success" => false,
                "message" => "حجم فایل نباید بیشتر از ۲۰ مگابایت باشد."
            ]);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file(
            $finfo,
            $file["tmp_name"]
        );
        finfo_close($finfo);

        /*
         * تصاویر
         */

        $imageTypes = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp",
            "image/gif" => "gif"
        ];

        /*
         * فایل‌های معمولی
         */

        $fileTypes = [
            "application/pdf" => "pdf",
            "text/plain" => "txt",
            "application/zip" => "zip",
            "application/x-zip-compressed" => "zip",

            "application/msword" => "doc",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",

            "application/vnd.ms-excel" => "xls",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",

            "application/vnd.ms-powerpoint" => "ppt",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx"
        ];

        /*
         * فایل‌های صوتی برای ویس
         */

        $voiceTypes = [
            "audio/webm" => "webm",
            "audio/ogg" => "ogg",
            "audio/mpeg" => "mp3",
            "audio/mp4" => "m4a",
            "audio/wav" => "wav",
            "audio/x-wav" => "wav"
        ];

        $messageType = "";
        $extension = "";

        if (isset($imageTypes[$mime])) {

            $messageType = "image";
            $extension = $imageTypes[$mime];

            if ($file["size"] > 10 * 1024 * 1024) {
                jsonResponse([
                    "success" => false,
                    "message" => "حجم تصویر نباید بیشتر از ۱۰ مگابایت باشد."
                ]);
            }
        } elseif (isset($voiceTypes[$mime])) {

            $messageType = "voice";
            $extension = $voiceTypes[$mime];

            if ($file["size"] > 10 * 1024 * 1024) {
                jsonResponse([
                    "success" => false,
                    "message" => "حجم ویس نباید بیشتر از ۱۰ مگابایت باشد."
                ]);
            }
        } elseif (isset($fileTypes[$mime])) {

            $messageType = "file";
            $extension = $fileTypes[$mime];
        } else {

            jsonResponse([
                "success" => false,
                "message" => "این نوع فایل مجاز نیست."
            ]);
        }

        /*
         * ساخت پوشه uploads
         */

        $uploadDir = __DIR__ . "/uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        /*
         * نام امن و تصادفی
         */

        $storedName =
            "msg_" .
            $me .
            "_" .
            bin2hex(random_bytes(12)) .
            "." .
            $extension;

        $destination = $uploadDir . $storedName;

        if (!move_uploaded_file(
            $file["tmp_name"],
            $destination
        )) {
            jsonResponse([
                "success" => false,
                "message" => "ذخیره فایل انجام نشد."
            ]);
        }

        /*
         * نام اصلی فایل
         */

        $originalName = basename(
            $file["name"]
        );

        /*
         * جلوگیری از نام خیلی طولانی
         */

        if (mb_strlen($originalName) > 255) {
            $originalName = mb_substr(
                $originalName,
                0,
                255
            );
        }

        /*
         * ذخیره در دیتابیس
         */

        $stmt = $pdo->prepare("
            INSERT INTO messages
            (
                sender_id,
                receiver_id,
                message,
                message_type,
                file_name,
                file_path,
                file_size,
                mime_type
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $me,
            $receiver,
            $originalName,
            $messageType,
            $originalName,
            "uploads/" . $storedName,
            (int)$file["size"],
            $mime
        ]);

        jsonResponse([
            "success" => true,
            "id" => $pdo->lastInsertId(),
            "message_type" => $messageType,
            "file_name" => $originalName,
            "file_path" => "uploads/" . $storedName,
            "file_size" => (int)$file["size"],
            "mime_type" => $mime
        ]);

        break;


    /* =========================
       DELETE MESSAGE
    ========================= */

    case "delete_message":

        $id = (int)($_POST["id"] ?? 0);

        if ($id <= 0) {
            jsonResponse([
                "success" => false,
                "message" => "شناسه پیام نامعتبر است."
            ]);
        }

        /*
         * اول اطلاعات فایل را می‌گیریم
         */

        $stmt = $pdo->prepare("
            SELECT
                message_type,
                file_path
            FROM messages
            WHERE id=?
            AND sender_id=?
            LIMIT 1
        ");

        $stmt->execute([
            $id,
            $me
        ]);

        $message = $stmt->fetch();

        if (!$message) {
            jsonResponse([
                "success" => false,
                "message" => "پیام پیدا نشد."
            ]);
        }

        /*
         * حذف فایل از سرور
         */

        if (
            !empty($message["file_path"]) &&
            in_array(
                $message["message_type"],
                ["image", "file", "voice"],
                true
            )
        ) {

            $filePath = __DIR__ . "/" .
                ltrim(
                    $message["file_path"],
                    "/\\"
                );

            $filePath = realpath($filePath);
            $uploadRealPath = realpath(
                __DIR__ . "/uploads"
            );

            if (
                $filePath &&
                $uploadRealPath &&
                strpos(
                    $filePath,
                    $uploadRealPath
                ) === 0 &&
                is_file($filePath)
            ) {
                @unlink($filePath);
            }
        }

        /*
         * حذف پیام از دیتابیس
         */

        $stmt = $pdo->prepare("
            DELETE FROM messages
            WHERE id=?
            AND sender_id=?
        ");

        $stmt->execute([
            $id,
            $me
        ]);

        jsonResponse([
            "success" => true
        ]);

        break;


    /* =========================
       EDIT MESSAGE
    ========================= */

    case "edit_message":

        $id = (int)($_POST["id"] ?? 0);
        $message = trim($_POST["message"] ?? "");

        if ($message === "") {
            jsonResponse([
                "success" => false,
                "message" => "پیام خالی است."
            ]);
        }

        if (mb_strlen($message) > 5000) {
            jsonResponse([
                "success" => false,
                "message" => "پیام بیش از حد طولانی است."
            ]);
        }

        /*
         * فقط پیام متنی قابل ویرایش است
         */

        $check = $pdo->prepare("
            SELECT message_type
            FROM messages
            WHERE id=?
            AND sender_id=?
            LIMIT 1
        ");

        $check->execute([
            $id,
            $me
        ]);

        $existing = $check->fetch();

        if (!$existing) {
            jsonResponse([
                "success" => false,
                "message" => "پیام پیدا نشد."
            ]);
        }

        if ($existing["message_type"] !== "text") {
            jsonResponse([
                "success" => false,
                "message" => "فقط پیام‌های متنی قابل ویرایش هستند."
            ]);
        }

        $stmt = $pdo->prepare("
            UPDATE messages
            SET message=?, edited=1
            WHERE id=?
            AND sender_id=?
        ");

        $stmt->execute([
            $message,
            $id,
            $me
        ]);

        jsonResponse([
            "success" => true
        ]);

        break;


    /* =========================
       DEFAULT
    ========================= */

    default:

        jsonResponse([
            "success" => false,
            "message" => "Unknown action"
        ]);
}
