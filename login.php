<?php
require_once 'config.php';

$error = '';

if (isset($_SESSION['player_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM `" . TABLE_USERS . "` WHERE `" . COL_USERNAME . "` = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Hna l-verification d l-password
            // ILa kan plain text f database, dert hna checks b jojo (Plain Text aw hash sha256)
            $hashed_password = hash('sha256', $password); 

            if ($password === $user[COL_PASSWORD] || $hashed_password === $user[COL_PASSWORD]) {
                $_SESSION['player_id'] = $user[COL_USER_ID]; 
                $_SESSION['username'] = $user[COL_USERNAME];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "L-password li dkhalti ghalat!";
            }
        } else {
            $error = "Hada l-compte makaynach f l-database!";
        }
    } else {
        $error = "Afak dakhal l-smiya o l-password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMP - Login Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            background: radial-gradient(circle at center, #1b1c31 0%, #0a0b12 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .logo {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #5d6cfc, #3f4fe0);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            margin: 0 auto 20px;
            box-shadow: 0 4px 20px rgba(93, 108, 252, 0.4);
        }
        h2 { font-size: 24px; font-weight: 600; margin-bottom: 8px; }
        p.sub { font-size: 13px; color: #8c8fae; margin-bottom: 30px; }
        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 12px; color: #8c8fae; margin-bottom: 8px; text-transform: uppercase; }
        .input-group input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            outline: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        .input-group input:focus { border-color: #5d6cfc; background: rgba(255, 255, 255, 0.08); }
        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #5d6cfc, #4a59e0);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(93, 108, 252, 0.5); }
        .error-msg {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid #f44336;
            color: #f44336;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">K</div>
        <h2>SAMP Dashboard</h2>
        <p class="sub">Log in with your in-game username & password</p>

        <?php if($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Username (In-game Name)</label>
                <input type="text" name="username" placeholder="E.g., Lahcen_Idh" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Connect to Dashboard</button>
        </form>
    </div>
</body>
</html>