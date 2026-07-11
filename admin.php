<?php
session_start();

// Admin panel password is read from a local, gitignored secret file.
// Generated automatically by install.sh — never hardcode it here.
$secretFile = __DIR__ . '/cipher-core/.admin_panel_secret.php';
if (file_exists($secretFile)) {
    $adminPassword = require $secretFile;
} else {
    // Not configured yet — block access until install.sh (or manual setup) runs.
    http_response_code(503);
    die('پنل مدیریت هنوز پیکربندی نشده است. لطفاً ./install.sh را اجرا کنید یا فایل cipher-core/.admin_panel_secret.php را دستی بسازید.');
}
$dataFile = __DIR__ . '/dashboard_data.json';

$defaultData = [
    "management_notice" => "",
    "activities" => [
        ["icon" => "☁️", "title" => "", "desc" => "", "time" => ""],
        ["icon" => "💬", "title" => "", "desc" => "", "time" => ""],
        ["icon" => "🎬", "title" => "", "desc" => "", "time" => ""]
    ]
];

if (isset($_POST['login_password'])) {
    if (hash_equals((string)$adminPassword, (string)$_POST['login_password'])) {
        $_SESSION['is_admin'] = true;
        // Redirect to the admin page after successful login
        header("Location: admin.php");
        exit;
    } else {
        $error = "incorrect password";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Ensure data file exists and is populated
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($dataFile), true);
// Fallback to default data if JSON is invalid or empty
if (!$data) {
    $data = $defaultData;
    // Optionally, re-save the default data if it was invalid
    file_put_contents($dataFile, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// --- Admin Login Form ---
if (!isset($_SESSION['is_admin'])):
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport Meta Tag -->
    <title>Managment Login</title>
    <style>
        body {
            background:#030712;
            color:#fff;
            font-family:system-ui;
            display:flex;
            align-items:center;
            justify-content:center;
            min-height:100vh;
            margin:0;
            padding: 16px; /* Added padding for small screens */
            box-sizing: border-box;
        }
        .box {
            width:100%;
            max-width:400px; /* Max width for larger screens */
            background:rgba(255,255,255,0.05);
            padding:30px;
            border-radius:20px;
            border:1px solid rgba(255,255,255,0.1);
            box-sizing: border-box; /* Include padding in width */
        }
        input, button {
            width:100%;
            padding:14px;
            border:none;
            border-radius:12px;
            margin-top:12px;
            font-size:16px; /* Responsive font size */
            box-sizing: border-box; /* Include padding in width */
        }
        input { background:#111827; color:#fff; }
        button { background:#00eaff; color:#000; font-weight:700; cursor:pointer; }
        .error { color:#ff7070; margin-top:10px; font-size:14px; }

        /* Responsive adjustments for very small screens */
        @media (max-width: 360px) {
            .box { padding: 20px; }
            h2 { font-size: 24px; }
            input, button { padding: 12px; font-size: 15px;}
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Managment</h2>
        <form method="post">
            <input type="password" name="login_password" placeholder="password">
            <button type="submit">Enter</button>
        </form>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport Meta Tag -->
    <title>مدیریت داشبورد</title>
    <style>
        body {
            background:#030712;
            color:#fff;
            font-family:system-ui;
            padding:20px;
            margin:0;
            box-sizing: border-box;
        }
        .wrap {
            max-width:900px;
            margin:auto;
            width: 100%; /* Take full width up to max-width */
        }
        .card {
            background:rgba(255,255,255,0.05);
            padding:20px;
            border-radius:20px;
            border:1px solid rgba(255,255,255,0.1);
            margin-bottom:20px;
            width: 100%; /* Ensure card takes full width */
            box-sizing: border-box; /* Include padding in width */
        }
        textarea, input {
            width:100%;
            padding:12px;
            margin-top:10px;
            border-radius:12px;
            border:none;
            background:#111827;
            color:#fff;
            box-sizing: border-box; /* Include padding in width */
            font-size:16px; /* Responsive font size */
        }
        textarea { min-height:120px; resize:vertical; }
        button {
            background:#00eaff;
            color:#000;
            padding:14px 18px;
            border:none;
            border-radius:12px;
            font-weight:700;
            cursor:pointer;
            font-size:16px; /* Responsive font size */
        }

        /* Grid for activities, but stacks on small screens */
        .row {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* Default 4 columns */
            gap:10px;
            margin-top:10px;
            align-items: center; /* Vertically align items in the row */
        }
        .row input {
            margin-top: 0; /* Remove margin-top when in grid */
        }

        .top {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }
        h1 { font-size: 28px; } /* Adjust heading size */
        h3 { font-size: 20px; } /* Adjust heading size */

        a { color:#00eaff; text-decoration:none; }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .row {
                grid-template-columns: repeat(2, 1fr); /* 2 columns on medium screens */
            }
             .row input[placeholder="زمان"] { /* Adjust specific input if needed */
                /* Example: maybe hide time on smaller screens or make it take full width */
             }
            h1 { font-size: 24px; }
            h3 { font-size: 18px; }
            button { padding: 12px 16px; font-size: 15px; }
        }

        @media (max-width: 480px) {
            .row {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
            .top { flex-direction: column; gap: 10px; }
            .top h1 { margin-bottom: 10px; }
            body { padding: 12px; }
            .card { padding: 15px; }
            textarea, input, button { font-size: 15px; padding: 10px; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <h1>مدیریت داشبورد</h1>
        <a href="?logout=1">خروج</a>
    </div>

    <form action="save_dashboard.php" method="post">
        <div class="card">
            <h3>اطلاعیه مدیریت</h3>
            <textarea name="management_notice" placeholder="متن اطلاعیه را اینجا وارد کنید"><?= htmlspecialchars($data['management_notice']) ?></textarea>
        </div>

        <div class="card">
            <h3>آخرین فعالیت‌ها</h3>

            <?php foreach ($data['activities'] as $i => $activity): ?>
                <div class="row">
                    <input type="text" name="activities[<?= $i ?>][icon]" value="<?= htmlspecialchars($activity['icon']) ?>" placeholder="آیکون">
                    <input type="text" name="activities[<?= $i ?>][title]" value="<?= htmlspecialchars($activity['title']) ?>" placeholder="عنوان">
                    <input type="text" name="activities[<?= $i ?>][desc]" value="<?= htmlspecialchars($activity['desc']) ?>" placeholder="توضیح">
                    <input type="text" name="activities[<?= $i ?>][time]" value="<?= htmlspecialchars($activity['time']) ?>" placeholder="زمان">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">ذخیره تغییرات</button>
    </form>
</div>
</body>
</html>
