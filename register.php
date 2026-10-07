<?php
require_once "config.php";

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($fullName === "" || $username === "" || $password === "") {

        $error = "لطفاً اطلاعات ضروری را کامل کنید.";
    } elseif (strlen($username) < 3) {

        $error = "نام کاربری باید حداقل ۳ کاراکتر باشد.";
    } elseif (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {

        $error = "نام کاربری فقط می‌تواند شامل حروف انگلیسی، عدد، نقطه و _ باشد.";
    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "ایمیل وارد شده معتبر نیست.";
    } elseif (strlen($password) < 6) {

        $error = "رمز عبور باید حداقل ۶ کاراکتر باشد.";
    } else {

        // بررسی نام کاربری
        $checkUsername = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $checkUsername->execute([$username]);

        if ($checkUsername->fetch()) {

            $error = "این نام کاربری قبلاً استفاده شده است.";
        } else {

            // بررسی ایمیل
            if ($email !== "") {

                $checkEmail = $pdo->prepare("
                    SELECT id
                    FROM users
                    WHERE email = ?
                    LIMIT 1
                ");

                $checkEmail->execute([$email]);

                if ($checkEmail->fetch()) {
                    $error = "این ایمیل قبلاً در MiraChat ثبت شده است.";
                }
            }

            // اگر مشکلی نبود، ثبت کاربر
            if ($error === "") {

                $hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                try {

                    $stmt = $pdo->prepare("
                        INSERT INTO users
                        (
                            username,
                            full_name,
                            email,
                            password,
                            status,
                            last_seen
                        )
                        VALUES (?, ?, ?, ?, 'online', NOW())
                    ");

                    $stmt->execute([
                        $username,
                        $fullName,
                        $email !== "" ? $email : null,
                        $hash
                    ]);

                    $_SESSION["user_id"] =
                        $pdo->lastInsertId();

                    header("Location: index.php");
                    exit;
                } catch (PDOException $e) {

                    // اگر به هر دلیلی MySQL خطای duplicate داد
                    if ($e->errorInfo[1] == 1062) {

                        $error =
                            "این نام کاربری یا ایمیل قبلاً ثبت شده است.";
                    } else {

                        $error =
                            "خطایی هنگام ساخت حساب رخ داد. دوباره تلاش کنید.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>ساخت حساب | MiraChat</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            min-height: 100vh;

            font-family:
                Tahoma,
                Arial,
                sans-serif;

            background:

                radial-gradient(circle at 20% 20%,
                    rgba(112, 73, 255, .20),
                    transparent 35%),

                radial-gradient(circle at 80% 80%,
                    rgba(8, 189, 233, .14),
                    transparent 35%),

                #070d20;

            color: #fff;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;
        }

        .card {

            width: 100%;

            max-width: 470px;

            background:
                rgba(17, 28, 59, .84);

            border:
                1px solid #26365d;

            border-radius: 28px;

            padding: 38px;

            box-shadow:
                0 30px 80px rgba(0, 0, 0, .45);

            backdrop-filter: blur(20px);
        }

        .logo {

            width: 65px;
            height: 65px;

            border-radius: 20px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 20px;

            font-size: 30px;

            font-weight: 900;

            background:
                linear-gradient(135deg,
                    #08bde9,
                    #7049ff);

            box-shadow:
                0 12px 35px rgba(80, 90, 255, .30);
        }

        h1 {

            text-align: center;

            font-size: 25px;

            margin-bottom: 8px;
        }

        .subtitle {

            text-align: center;

            color: #8290b2;

            font-size: 13px;

            margin-bottom: 30px;
        }

        label {

            display: block;

            color: #aeb9d3;

            font-size: 12px;

            margin-bottom: 8px;
        }

        .field {

            margin-bottom: 17px;
        }

        input {

            width: 100%;

            height: 50px;

            border-radius: 14px;

            border:
                1px solid #29385d;

            background: #0b1530;

            color: white;

            padding: 0 15px;

            outline: none;

            transition: .2s;
        }

        input:focus {

            border-color: #08bde9;

            box-shadow:
                0 0 0 3px rgba(8, 189, 233, .08);
        }

        button {

            width: 100%;

            height: 52px;

            border: 0;

            border-radius: 15px;

            background:
                linear-gradient(135deg,
                    #08bde9,
                    #7049ff);

            color: white;

            font-weight: bold;

            cursor: pointer;

            margin-top: 5px;

            transition: .2s;
        }

        button:hover {

            transform: translateY(-1px);

            box-shadow:
                0 10px 30px rgba(70, 90, 255, .25);
        }

        .error {

            background:
                rgba(255, 75, 100, .10);

            border:
                1px solid rgba(255, 75, 100, .30);

            color: #ff9aaa;

            border-radius: 13px;

            padding: 12px;

            font-size: 12px;

            margin-bottom: 18px;

            line-height: 1.8;
        }

        .bottom {

            text-align: center;

            color: #7f8ca9;

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

        <h1>
            ساخت حساب MiraChat
        </h1>

        <div class="subtitle">
            یک حساب بساز و وارد دنیای گفت‌وگو شو
        </div>

        <?php if ($error): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="field">

                <label>
                    نام و نام خانوادگی
                </label>

                <input
                    type="text"
                    name="full_name"
                    placeholder="مثلاً علی شعبانی"
                    value="<?= e($_POST['full_name'] ?? '') ?>"
                    required>

            </div>


            <div class="field">

                <label>
                    نام کاربری
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="username"
                    value="<?= e($_POST['username'] ?? '') ?>"
                    required>

            </div>


            <div class="field">

                <label>
                    ایمیل
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="example@email.com"
                    value="<?= e($_POST['email'] ?? '') ?>">

            </div>


            <div class="field">

                <label>
                    رمز عبور
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="حداقل ۶ کاراکتر"
                    required>

            </div>


            <button type="submit">
                ساخت حساب
            </button>

        </form>


        <div class="bottom">

            قبلاً حساب ساخته‌ای؟

            <a href="login.php">
                ورود به حساب
            </a>

        </div>

    </div>

</body>

</html>