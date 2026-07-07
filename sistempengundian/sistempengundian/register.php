<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include 'db.php';
include 'lang.php';

$message = "";
$msg_type = "";

if (isset($_POST['register'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $password = $_POST['password'];

    if (!preg_match("/^\d{5}$/", $id)) {
        $message = $t['format_id_error'];
        $msg_type = "error";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $nama)) {
        $message = $t['name_error'];
        $msg_type = "error";
    } else {
        try {
            $check = $conn->query("SELECT * FROM pengguna WHERE id_Pengguna='$id'");
            if ($check->num_rows > 0) {
                $message = $t['id_exists'];
                $msg_type = "error";
            } else {
                $sql = "INSERT INTO pengguna (id_Pengguna, nama_Pengguna, kelas_Pengguna, password_Pengguna) VALUES ('$id', '$nama', '$kelas', '$password')";
                $conn->query($sql);
                $message = $t['success_reg'];
                $msg_type = "success";
            }
        } catch (Exception $e) {
            $message = $t['error_system'] . " " . $e->getMessage();
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <script>
    (function(){
        let currentScale = parseFloat(localStorage.getItem('fontSizeScale')) || 1.0;
        document.documentElement.style.setProperty("--font-scale", currentScale);
    })();
    function changeFontSize(direction) {
        let currentScale = parseFloat(localStorage.getItem('fontSizeScale')) || 1.0;
        if (direction === 0) currentScale = 1.0;
        else if (direction > 0) currentScale = Math.min(1.4, currentScale + 0.1);
        else currentScale = Math.max(0.7, currentScale - 0.1);
        document.documentElement.style.setProperty("--font-scale", currentScale);
        localStorage.setItem('fontSizeScale', currentScale);
    }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Briged Putera</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 80px;
        }

         /* Top Header styles moved to style.css */

         /* Removed redundant styles - now in style.css */

        .login-container {
            position: relative;
            z-index: 10;
            display: flex;
            width: 900px;
            min-height: 650px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .left-panel {
            width: 45%;
            background: linear-gradient(160deg, #8B0000 0%, #CD853F 50%, #DAA520 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,215,0,0.2) 0%, transparent 60%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        .left-panel img {
            width: 160px;
            height: auto;
            margin-bottom: 25px;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3));
            position: relative;
            z-index: 1;
        }

        .left-panel h2 {
            font-size: calc(28px * var(--font-scale, 1));
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .left-panel p {
            font-size: calc(14px * var(--font-scale, 1));
            color: rgba(255,255,255,0.9);
            line-height: 1.7;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .btn-outline {
            padding: 12px 40px;
            border: 2px solid white;
            background: transparent;
            color: white;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            z-index: 1;
        }

        .btn-outline:hover {
            background: white;
            color: #8B0000;
        }

        .right-panel {
            width: 55%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
        }

        .right-panel h1 {
            font-size: calc(32px * var(--font-scale, 1));
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-group label {
            display: block;
            font-size: calc(13px * var(--font-scale, 1));
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: calc(15px * var(--font-scale, 1));
            outline: none;
            transition: all 0.3s;
            background: #fafafa;
            color: #333;
        }

        .input-group input:focus {
            border-color: #CD5C5C;
            background: white;
            box-shadow: 0 0 0 4px rgba(205, 92, 92, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #8B0000, #CD5C5C);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: calc(16px * var(--font-scale, 1));
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.4);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #777;
            font-size: calc(14px * var(--font-scale, 1));
        }

        .register-link a {
            color: #8B0000;
            font-weight: 600;
            text-decoration: none;
        }

        .msg-box {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: calc(14px * var(--font-scale, 1));
        }

        .success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

        @media (max-width: 768px) {
            .login-container { flex-direction: column; width: 95%; max-width: 420px; }
            .left-panel { width: 100%; padding: 40px 30px; }
            .right-panel { width: 100%; padding: 40px 30px; }
        }

        #toast-popup {
            display: none;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: #fff;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 500;
            text-align: center;
            box-shadow: 0 4px 15px rgba(204, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .font-btn {
            background: transparent;
            padding: 4px 10px;
            cursor: pointer;
        }
        .font-btn-small { font-size: calc(12px * var(--font-scale, 1)); }
        .font-btn-normal { font-size: calc(14px * var(--font-scale, 1)); }
        .font-btn-large { font-size: calc(16px * var(--font-scale, 1)); }
    </style>
</head>
<body class="theme-bg">
    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="header-center">
            <img src="logo.png" alt="BB Logo" onerror="this.style.display='none'">
            <div class="site-title"><?php echo $t['site_title']; ?> <span><?php echo $t['site_subtitle']; ?></span></div>
        </div>
        <div style="width: 150px;"></div> <!-- Spacer -->
        <div class="lang-switcher">
            <div class="font-switcher no-print" style="display:flex; gap:5px; margin-right:15px; align-items:center;">
                <button onclick="changeFontSize(-1)" class="lang-btn font-btn font-btn-small" title="Decrease Font Size">A-</button>
                <button onclick="changeFontSize(0)" class="lang-btn font-btn font-btn-normal" title="Reset Font Size">A</button>
                <button onclick="changeFontSize(1)" class="lang-btn font-btn font-btn-large" title="Increase Font Size">A+</button>
            </div>
            <a href="<?php echo get_lang_url('ms'); ?>" class="lang-btn <?php echo $lang == 'ms' ? 'active' : ''; ?>">BM</a>
            <a href="<?php echo get_lang_url('en'); ?>" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>

    <!-- Bouncing BB Logos -->
    <div class="floating-logo logo-large"></div>
    <div class="floating-logo logo-small"></div>

    <div class="login-container">
        <div class="left-panel">
            <img src="logo.png" alt="BB Logo" onerror="this.style.display='none'">
            <h2><?php echo $t['welcome']; ?></h2>
            <p><?php echo $t['system_desc']; ?></p>
            <a href="index.php" class="btn-outline"><?php echo $t['login']; ?></a>
        </div>

        <div class="right-panel">
            <h1><?php echo $t['register']; ?></h1>
            <p class="subtitle"><?php echo $t['register_subtitle']; ?></p>

            <!-- Toast Popup (Styles defined in head stylesheet) -->
            <div id="toast-popup">
                <span id="toast-msg"></span>
            </div>

            <?php if ($message != ""): ?>
                <script>
                    alert('<?php echo addslashes($message); ?>');
                </script>
            <?php endif; ?>

            <form method="post" id="registerForm" novalidate>
                <div class="input-group" style="margin-top: 25px;">
                    <label><?php echo $t['id_user']; ?></label>
                    <input type="text" name="id" id="field_id" placeholder="<?php echo $t['id_placeholder']; ?>">
                </div>

                <div class="input-group">
                    <label><?php echo $t['name']; ?></label>
                    <input type="text" name="nama" id="field_nama" placeholder="<?php echo $t['name_placeholder']; ?>">
                </div>

                <div class="input-group">
                    <label><?php echo $t['class']; ?></label>
                    <input type="text" name="kelas" id="field_kelas" placeholder="<?php echo $t['class_placeholder']; ?>">
                </div>

                <div class="input-group">
                    <label><?php echo $t['password']; ?></label>
                    <input type="password" name="password" id="field_password" placeholder="<?php echo $t['password']; ?>">
                </div>

                <button type="submit" name="register" class="btn-submit"><?php echo $t['register_now']; ?></button>
            </form>

            <div class="register-link">
                <?php echo $t['has_account']; ?> <a href="index.php"><?php echo $t['login']; ?></a>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        var fields = [
            { id: 'field_id', label: '<?php echo addslashes($t["id_user"]); ?>' },
            { id: 'field_nama', label: '<?php echo addslashes($t["name"]); ?>' },
            { id: 'field_kelas', label: '<?php echo addslashes($t["class"]); ?>' },
            { id: 'field_password', label: '<?php echo addslashes($t["password"]); ?>' }
        ];

        for (var i = 0; i < fields.length; i++) {
            var input = document.getElementById(fields[i].id);
            if (!input.value.trim()) {
                e.preventDefault();
                var msg = 'localhost: ' + fields[i].label + ' <?php echo ($lang == "ms") ? "mesti diisi!" : "must be filled in!"; ?>';
                alert(msg);
                input.focus();
                return;
            }
        }

        // ID format validation: 5 digits only
        var idValue = document.getElementById('field_id').value.trim();
        if (!/^\d{5}$/.test(idValue)) {
            e.preventDefault();
            alert('<?php echo addslashes($t["format_id_error"]); ?>');
            document.getElementById('field_id').focus();
            return;
        }

        // Name format validation: letters only
        var namaValue = document.getElementById('field_nama').value.trim();
        if (!/^[A-Za-z\s]+$/.test(namaValue)) {
            e.preventDefault();
            alert('<?php echo addslashes($t["name_error"]); ?>');
            document.getElementById('field_nama').focus();
            return;
        }
    });

    function showToast(msg) {
        var toast = document.getElementById('toast-popup');
        document.getElementById('toast-msg').textContent = '⚠️ ' + msg;
        toast.style.display = 'block';
        toast.style.animation = 'none';
        toast.offsetHeight; // trigger reflow
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(function() { toast.style.display = 'none'; }, 3000);
    }
    </script>
</body>
</html>