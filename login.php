<?php
require_once "config.php";

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login    = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login === "" || $password === "") {
        $error = "نام کاربری و رمز عبور را وارد کنید.";
    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ? OR email = ?
            LIMIT 1
        ");

        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];

            $pdo->prepare("
                UPDATE users
                SET status='online', last_seen=NOW()
                WHERE id=?
            ")->execute([$user["id"]]);

            header("Location: index.php");
            exit;
        } else {
            $error = "اطلاعات ورود صحیح نیست.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود | MiraChat</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            min-height: 100vh;
            font-family: Tahoma, Arial, sans-serif;
            background:
                radial-gradient(circle at 15% 25%, rgba(112, 73, 255, .2), transparent 35%),
                radial-gradient(circle at 85% 75%, rgba(8, 189, 233, .15), transparent 35%),
                #070d20;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 440px;
            background: rgba(17, 28, 59, .85);
            border: 1px solid #26365d;
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .5);
            backdrop-filter: blur(20px);
        }

        .logo {
            width: 68px;
            height: 68px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 21px;
            font-size: 31px;
            font-weight: 900;
            background: linear-gradient(135deg, #08bde9, #7049ff);
        }

        h1 {
            text-align: center;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #8491af;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            color: #aab5ce;
            font-size: 12px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            height: 52px;
            border-radius: 15px;
            border: 1px solid #29385d;
            background: #0b1530;
            color: #fff;
            outline: none;
            padding: 0 16px;
        }

        input:focus {
            border-color: #08bde9;
        }

        button {
            width: 100%;
            height: 53px;
            border: 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #08bde9, #7049ff);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        .error {
            color: #ff9aaa;
            background: rgba(255, 70, 100, .1);
            border: 1px solid rgba(255, 70, 100, .25);
            border-radius: 13px;
            padding: 12px;
            font-size: 12px;
            margin-bottom: 18px;
        }

        .bottom {
            text-align: center;
            color: #7d8ba8;
            font-size: 12px;
            margin-top: 22px;
        }

        .bottom a {
            color: #08bde9;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="card">

        <div class="logo">M</div>

        <h1>ورود به MiraChat</h1>
        <div class="subtitle">به فضای گفت‌وگوی خودت برگرد</div>

        <?php if ($error): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="field">
                <label>نام کاربری یا ایمیل</label>
                <input
                    type="text"
                    name="login"
                    placeholder="username یا email"
                    required>
            </div>

            <div class="field">
                <label>رمز عبور</label>
                <input
                    type="password"
                    name="password"
                    placeholder="رمز عبور"
                    required>
            </div>

            <button type="submit">ورود</button>

        </form>

        <div class="bottom">
            حساب نداری؟
            <a href="register.php">ساخت حساب جدید</a>
        </div>

    </div>

</body>

</html>