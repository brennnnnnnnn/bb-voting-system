<?php
session_start();
include 'db.php';
include 'lang.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: index.php"); 
    exit(); 
}

// Initialize search parameters for SQL search
$searchUser = isset($_GET['searchUser']) ? trim($_GET['searchUser']) : '';
$searchCalon = isset($_GET['searchCalon']) ? trim($_GET['searchCalon']) : '';


// Handle CSV Import - Post/Redirect/Get pattern to prevent resubmit on refresh
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
    
    if ($ext !== 'csv') {
        $_SESSION['import_msg']  = ($lang == 'ms') ? '⚠️ Sila muat naik fail CSV sahaja.' : '⚠️ Please upload a CSV file only.';
        $_SESSION['import_type'] = 'error';
        header('Location: admin_dashboard.php');
        exit();
    } else if ($file) {
        $handle = fopen($file, 'r');
        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);
        $header = fgetcsv($handle); // skip header row
        $success_user = 0;
        $updated_user = 0;
        $failed_user = 0;
        $skipped_user = 0;

        $success_calon = 0;
        $updated_calon = 0;
        $failed_calon = 0;
        $skipped_calon = 0;
        $errors = [];
        $lines_ms = ["Import selesai."];
        $lines_en = ["Import complete."];
        
        $row_index = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $row_index++;
            if (count($row) < 3) {
                continue;
            }

            // Helper: clean a cell value - strip invisible/non-printable chars Excel may inject
            $clean = function($val) {
                return trim(preg_replace('/[\x00-\x1F\x7F\xC2\xA0\xFF]/u', '', $val ?? ''));
            };
            
            $type = strtolower($clean($row[0]));
            if ($type == 'pengguna') {
                // Row format: pengguna, ID, Nama, Kelas, Password
                $id_raw    = $clean($row[1]);
                $id        = $conn->real_escape_string($id_raw);
                $nama      = $conn->real_escape_string($clean($row[2]));
                $kelas     = $conn->real_escape_string($clean($row[3] ?? ''));
                $pass_raw  = $clean($row[4] ?? '');
                $has_pass  = ($pass_raw !== ''); // true only if CSV explicitly has a password
                $pass      = $has_pass ? $conn->real_escape_string($pass_raw) : '';

                // Use TRIM() on DB side too - catches whitespace stored in DB or from CSV
                $check  = $conn->query("SELECT * FROM pengguna WHERE TRIM(id_Pengguna) = TRIM('$id')");
                $exists = $check && $check->num_rows > 0;

                if ($exists) {
                    $existing = $check->fetch_assoc();
                    // Trim DB values before comparing - fixes mismatches from trailing spaces in DB
                    $db_nama  = trim($existing['nama_Pengguna']);
                    $db_kelas = trim($existing['kelas_Pengguna']);
                    $name_changed  = ($db_nama  !== $nama);
                    $kelas_changed = ($db_kelas !== $kelas);
                    $pass_changed  = $has_pass && (trim($existing['password_Pengguna']) !== $pass);

                    if ($name_changed || $kelas_changed || $pass_changed) {
                        file_put_contents(
                            'update_debug.txt', 
                            "ID $id_raw changed. " .
                            "name_changed: $name_changed ('$db_nama' vs '$nama'). " .
                            "kelas_changed: $kelas_changed ('$db_kelas' vs '$kelas'). " .
                            "pass_changed: $pass_changed ('" . trim($existing['password_Pengguna']) . "' vs '$pass').\n", 
                            FILE_APPEND
                        );
                        if ($has_pass) {
                            $sql = "UPDATE pengguna SET nama_Pengguna='$nama', kelas_Pengguna='$kelas', password_Pengguna='$pass' WHERE TRIM(id_Pengguna) = TRIM('$id')";
                        } else {
                            // Don't touch the password - keep whatever is in DB
                            $sql = "UPDATE pengguna SET nama_Pengguna='$nama', kelas_Pengguna='$kelas' WHERE TRIM(id_Pengguna) = TRIM('$id')";
                        }
                        if ($conn->query($sql)) { 
                            $updated_user++; 
                        } else { 
                            $failed_user++; 
                            $errors[] = "ID '$id_raw': " . $conn->error; 
                        }
                    } else {
                        $skipped_user++;
                    }
                } else {
                    $insert_pass = $has_pass ? $pass : '123';
                    $sql = "INSERT INTO pengguna (id_Pengguna, nama_Pengguna, kelas_Pengguna, password_Pengguna) VALUES ('$id', '$nama', '$kelas', '$insert_pass')";
                    if ($conn->query($sql)) { 
                        $success_user++; 
                    } else { 
                        $failed_user++; 
                        $errors[] = "ID '$id_raw': " . $conn->error; 
                    }
                }

            } else if ($type == 'calon') {
                // Row format: calon, ID, Nama, Password
                $id_raw   = $clean($row[1]);
                $id       = $conn->real_escape_string($id_raw);
                $raw_nama = $clean($row[2]);
                $raw_pass = $clean($row[3] ?? '');
                
                $nama     = $conn->real_escape_string($raw_nama);
                $pass     = $conn->real_escape_string($raw_pass);
                $has_pass = ($raw_pass !== '');

                $check  = $conn->query("SELECT * FROM calon WHERE TRIM(id_Calon) = TRIM('$id')");
                $exists = $check && $check->num_rows > 0;

                if ($exists) {
                    $existing     = $check->fetch_assoc();
                    $db_nama      = trim($existing['nama_Calon']);
                    $name_changed = ($db_nama !== $raw_nama);
                    $pass_changed = $has_pass && (trim($existing['kata_laluan']) !== $raw_pass);

                    if ($name_changed || $pass_changed) {
                        if ($has_pass) {
                            $sql = "UPDATE calon SET nama_Calon='$nama', kata_laluan='$pass' WHERE TRIM(id_Calon) = TRIM('$id')";
                        } else {
                            $sql = "UPDATE calon SET nama_Calon='$nama' WHERE TRIM(id_Calon) = TRIM('$id')";
                        }
                        if ($conn->query($sql)) { 
                            $updated_calon++; 
                        } else { 
                            $failed_calon++; 
                            $errors[] = "ID '$id_raw': " . $conn->error; 
                        }
                    } else {
                        $skipped_calon++;
                    }
                } else {
                    $insert_pass = $has_pass ? $pass : '123';
                    $sql = "INSERT INTO calon (id_Calon, nama_Calon, kata_laluan) VALUES ('$id', '$nama', '$insert_pass')";
                    if ($conn->query($sql)) { 
                        $success_calon++; 
                    } else { 
                        $failed_calon++; 
                        $errors[] = "ID '$id_raw': " . $conn->error; 
                    }
                }

            } else {
                $failed_user++;
                $errors[] = (($lang == 'ms') ? "Jenis '$type' tidak dikenali" : "Unknown type '$type'");
            }
        }
        fclose($handle);
        
        $total_user = $success_user + $updated_user + $failed_user + $skipped_user;
        if ($total_user > 0) {
            $lines_ms[] = "Pengguna (baharu: $success_user, kemas kini: $updated_user, gagal: $failed_user, tiada perubahan: $skipped_user)";
            $lines_en[] = "Users (new: $success_user, updated: $updated_user, failed: $failed_user, no changes: $skipped_user)";
        }
        
        $total_calon = $success_calon + $updated_calon + $failed_calon + $skipped_calon;
        if ($total_calon > 0) {
            $lines_ms[] = "Calon (baharu: $success_calon, kemas kini: $updated_calon, gagal: $failed_calon, tiada perubahan: $skipped_calon)";
            $lines_en[] = "Candidates (new: $success_calon, updated: $updated_calon, failed: $failed_calon, no changes: $skipped_calon)";
        }

        if ($total_user == 0 && $total_calon == 0) {
            $lines_ms[] = "Tiada data diimport.";
            $lines_en[] = "No data imported.";
            $import_type = 'error';
        } else if ($failed_user > 0 || $failed_calon > 0) {
            $import_type = 'warning';
        } else {
            $import_type = 'success';
        }
        
        $final_msg_ms = implode("<br>", $lines_ms);
        $final_msg_en = implode("<br>", $lines_en);
        $_SESSION['import_msg'] = ($lang == 'ms') ? $final_msg_ms : $final_msg_en;
        $_SESSION['import_type'] = $import_type;
    } else {
        $_SESSION['import_msg'] = ($lang == 'ms') ? 'Ralat: Tiada fail dimuat naik.' : 'Error: No file uploaded.';
        $_SESSION['import_type'] = 'error';
    }
    // PRG: redirect so refresh won't resubmit the form
    header('Location: admin_dashboard.php');
    exit();
}

// Read import result from session (set by redirect above)
$import_msg = $_SESSION['import_msg'] ?? '';
$import_type = $_SESSION['import_type'] ?? '';
unset($_SESSION['import_msg'], $_SESSION['import_type']);

// Handle Deletion Logic
if (isset($_GET['del_user'])) {
    $id = $conn->real_escape_string($_GET['del_user']);
    $conn->query("DELETE FROM undian WHERE id_Pengguna='$id'");
    $conn->query("DELETE FROM pengguna WHERE id_Pengguna='$id'");
    echo "<script>alert('" . addslashes($t['success_delete_user']) . "'); window.location.href='admin_dashboard.php';</script>";
}

if (isset($_GET['del_calon'])) {
    $id = $conn->real_escape_string($_GET['del_calon']);
    $conn->query("DELETE FROM undian WHERE id_Calon='$id'");
    $conn->query("DELETE FROM calon WHERE id_Calon='$id'");
    echo "<script>alert('" . addslashes($t['success_delete_candidate']) . "'); window.location.href='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
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
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* CSV Buttons */
        .csv-btn, .import-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .csv-btn {
            background: linear-gradient(135deg, #1e7e34, #28a745);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        .csv-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.5);
        }
        .import-btn {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
        }
        .import-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.5);
        }

        /* Import Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9998;
            animation: fadeIn 0.2s ease;
        }
        .modal-box {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 20px;
            padding: 35px;
            width: 480px;
            max-width: 95%;
            z-index: 9999;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translate(-50%, -45%); } to { opacity: 1; transform: translate(-50%, -50%); } }
        .modal-box h3 {
            margin: 0 0 20px 0;
            font-size: calc(22px * var(--font-scale, 1));
            color: #333;
        }
        .modal-box label {
            display: block;
            font-size: calc(13px * var(--font-scale, 1));
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .modal-box select, .modal-box input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: calc(14px * var(--font-scale, 1));
            font-family: 'Poppins', sans-serif;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s;
            background: #fafafa;
        }
        .modal-box select:focus, .modal-box input[type="file"]:focus {
            border-color: #e67e22;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .modal-actions button {
            padding: 10px 25px;
            border: none;
            border-radius: 10px;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }
        .btn-cancel {
            background: #e0e0e0;
            color: #555;
        }
        .btn-cancel:hover { background: #d0d0d0; }
        .btn-import-submit {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
        }
        .btn-import-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
        }
        .csv-format-hint {
            background: #fff8e1;
            border-left: 4px solid #f39c12;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: calc(12px * var(--font-scale, 1));
            color: #8B4513;
            line-height: 1.6;
        }
        .csv-format-hint code {
            background: rgba(0,0,0,0.06);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: calc(11px * var(--font-scale, 1));
        }

        /* Search Box */
        .search-box {
            position: relative;
            margin-bottom: 15px;
        }
        .search-box input {
            width: 100%;
            padding: 11px 15px 11px 42px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: calc(14px * var(--font-scale, 1));
            font-family: 'Poppins', sans-serif;
            background: #fafafa;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
            color: #333;
        }
        .search-box input:focus {
            border-color: #8B0000;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        .search-box input::placeholder {
            color: #aaa;
        }
        .search-box .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: calc(16px * var(--font-scale, 1));
            color: #999;
            pointer-events: none;
            transition: color 0.3s;
        }
        .search-box input:focus ~ .search-icon {
            color: #8B0000;
        }
        .no-results-row td {
            text-align: center;
            padding: 25px;
            color: #999;
            font-style: italic;
            font-size: calc(14px * var(--font-scale, 1));
        }



        /* Print Button */
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            box-shadow: 0 4px 12px rgba(52, 73, 94, 0.3);
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 73, 94, 0.5);
        }

        /* Print Styles - Report Only - Fit everything on ONE page */
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
                font-size: 8px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .top-header,
            .floating-logo,
            .nav-links,
            .lang-switcher,
            .search-box,
            .csv-btn,
            .import-btn,
            .print-btn,
            .no-print,
            .import-toast,
            .modal-overlay,
            .modal-box,
            .font-switcher,
            footer {
                display: none !important;
            }
            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            h2, h3 {
                color: #000 !important;
                margin: 0 0 4px 0 !important;
                font-size: 11px !important;
            }
            h2 { font-size: 13px !important; }

            /* Keep the 2-column side-by-side layout */
            .grid-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 10px !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                margin-bottom: 0 !important;
                padding: 6px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            /* Hide the add button row border */
            .card > div[style*="border-bottom"] {
                border-bottom: 1px solid #eee !important;
                padding-bottom: 4px !important;
                margin-bottom: 4px !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #bbb !important;
                padding: 2px 4px !important;
                color: #000 !important;
                background: white !important;
                font-size: 7px !important;
                line-height: 1.3 !important;
            }
            th {
                background: #f0f0f0 !important;
                font-weight: 700 !important;
                font-size: 7.5px !important;
            }
            /* Hide the Actions column in print */
            td:last-child, th:last-child {
                display: none !important;
            }
            /* Prevent any page breaks */
            * {
                page-break-before: auto !important;
                page-break-after: auto !important;
            }
        }
    </style>
</head>
<body class="theme-bg">
    <!-- Bouncing BB Logos -->
    <div class="floating-logo logo-large"></div>
    <div class="floating-logo logo-small"></div>
    <div class="top-header">
        <div class="header-center">
            <img src="logo.png" alt="BB Logo" onerror="this.style.display='none'">
            <div class="site-title"><?php echo $t['site_title']; ?> <span><?php echo $t['site_subtitle']; ?></span></div>
        </div>
        
        <div class="nav-links">
            <a href="admin_dashboard.php" class="active"><?php echo $t['nav_dashboard']; ?></a>
            <a href="results.php"><?php echo $t['nav_results']; ?></a>
            <a href="logout.php"><?php echo $t['nav_logout']; ?></a>
        </div>

        <div class="lang-switcher">
            <div class="font-switcher no-print" style="display:flex; gap:5px; margin-right:15px; align-items:center;">
                <button onclick="changeFontSize(-1)" class="lang-btn" style="background:transparent; padding:4px 10px; font-size: calc(12px * var(--font-scale, 1)); cursor:pointer;" title="Decrease Font Size">A-</button>
                <button onclick="changeFontSize(0)" class="lang-btn" style="background:transparent; padding:4px 10px; font-size: calc(14px * var(--font-scale, 1)); cursor:pointer;" title="Reset Font Size">A</button>
                <button onclick="changeFontSize(1)" class="lang-btn" style="background:transparent; padding:4px 10px; font-size: calc(16px * var(--font-scale, 1)); cursor:pointer;" title="Increase Font Size">A+</button>
            </div>
            <a href="<?php echo get_lang_url('ms'); ?>" class="lang-btn <?php echo $lang == 'ms' ? 'active' : ''; ?>">BM</a>
            <a href="<?php echo get_lang_url('en'); ?>" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>

    <div class="main-content" style="position: relative; z-index: 10;">
        <?php if($import_msg): ?>
            <script>
                alert('<?php echo addslashes(str_replace("<br>", "\\n", $import_msg)); ?>');
            </script>
        <?php endif; ?>

        <?php if(isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
            <?php unset($_SESSION['login_success']); ?>
            <script>alert('<?php echo ($lang == "ms") ? "Berjaya log masuk, Admin!" : "Successfully logged in, Admin!"; ?>');</script>
        <?php endif; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;"><?php echo $t['admin_panel']; ?></h2>
            <div style="display:flex; gap:10px;">
                <button class="print-btn no-print" onclick="window.print();">🖨️ <?php echo ($lang == 'ms') ? 'Cetak' : 'Print'; ?></button>
                <button class="csv-btn" onclick="downloadCSV()">📥 <?php echo ($lang == 'ms') ? 'Eksport CSV' : 'Download CSV'; ?></button>
                <button class="import-btn" onclick="openImportModal()">📤 <?php echo ($lang == 'ms') ? 'Import CSV' : 'Import CSV'; ?></button>
            </div>
        </div>
        
        <div class="grid-2">
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:2px solid #f0f0f0; padding-bottom:10px;">
                    <h3 style="margin:0;"><?php echo $t['user_list']; ?></h3>
                    <a href="admin_manage_pengguna.php?action=add" class="btn no-print" style="width:auto; padding:8px 15px; font-size: calc(12px * var(--font-scale, 1)); background: #8B0000;">+ <?php echo $t['add']; ?></a>
                </div>
                
                <form method="GET" action="admin_dashboard.php" style="display:flex; gap:10px; margin-bottom:15px;" class="no-print">
                    <?php if ($searchCalon !== ''): ?>
                        <input type="hidden" name="searchCalon" value="<?php echo htmlspecialchars($searchCalon); ?>">
                    <?php endif; ?>
                    <div class="search-box" style="flex:1; margin-bottom:0;">
                        <input type="text" name="searchUser" value="<?php echo htmlspecialchars($searchUser); ?>" placeholder="<?php echo ($lang == 'ms') ? '🔍 Cari pengguna...' : '🔍 Search users...'; ?>">
                    </div>
                    <button type="submit" class="btn" style="background:#8B0000; width:auto; padding:0 15px; margin-bottom:0; font-size: calc(14px * var(--font-scale, 1));"><?php echo ($lang == 'ms') ? 'Cari' : 'Search'; ?></button>
                    <?php if ($searchUser !== ''): ?>
                        <a href="admin_dashboard.php?<?php echo http_build_query(array_filter(['searchCalon' => $searchCalon])); ?>" class="btn" style="background:#e0e0e0; color:#555; width:auto; padding:10px 15px; margin-bottom:0; font-size: calc(14px * var(--font-scale, 1)); text-decoration:none; display:inline-flex; align-items:center; border-radius:10px; font-weight:600; box-sizing:border-box;"><?php echo ($lang == 'ms') ? 'Reset' : 'Reset'; ?></a>
                    <?php endif; ?>
                </form>

                <table id="userTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ID</th>
                            <th><?php echo $t['name']; ?></th>
                            <th><?php echo $t['class']; ?></th>
                            <th><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($searchUser !== '') {
                        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE id_Pengguna LIKE ? OR nama_Pengguna LIKE ? OR kelas_Pengguna LIKE ? ORDER BY id_Pengguna ASC");
                        $like_search = "%" . $searchUser . "%";
                        $stmt->bind_param("sss", $like_search, $like_search, $like_search);
                        $stmt->execute();
                        $res = $stmt->get_result();
                    } else {
                        $res = $conn->query("SELECT * FROM pengguna ORDER BY id_Pengguna ASC");
                    }

                    if ($res && $res->num_rows == 0) {
                        echo "<tr class='no-results-row'><td colspan='5'>" . (($lang == 'ms') ? "Tiada hasil dijumpai" : "No results found") . "</td></tr>";
                    } else if ($res) {
                        $no = 1;
                        while($row = $res->fetch_assoc()) {
                            echo "<tr data-pass='".htmlspecialchars($row['password_Pengguna'], ENT_QUOTES)."'>";
                            echo "<td>".$no."</td>";
                            echo "<td>".$row['id_Pengguna']."</td>";
                            echo "<td>".$row['nama_Pengguna']."</td>";
                            echo "<td>".$row['kelas_Pengguna']."</td>";
                            echo "<td>
                                    <a href='admin_manage_pengguna.php?action=edit&id=" . $row['id_Pengguna'] . "' style='color:#8B0000; font-weight:bold; margin-right:5px;'>" . $t['edit'] . "</a>
                                    <a href='admin_dashboard.php?del_user=" . $row['id_Pengguna'] . "' onclick='return confirm(\"" . addslashes($t['confirm_delete_user']) . "\")' style='color:#c0392b; font-weight:bold;'>" . $t['delete'] . "</a>
                                  </td>";
                            echo "</tr>";
                            $no++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:2px solid #f0f0f0; padding-bottom:10px;">
                    <h3 style="margin:0;"><?php echo $t['candidate_list']; ?></h3>
                    <a href="admin_manage_calon.php?action=add" class="btn no-print" style="width:auto; padding:8px 15px; font-size: calc(12px * var(--font-scale, 1)); background: #8B0000;">+ <?php echo $t['add']; ?></a>
                </div>
                
                <form method="GET" action="admin_dashboard.php" style="display:flex; gap:10px; margin-bottom:15px;" class="no-print">
                    <?php if ($searchUser !== ''): ?>
                        <input type="hidden" name="searchUser" value="<?php echo htmlspecialchars($searchUser); ?>">
                    <?php endif; ?>
                    <div class="search-box" style="flex:1; margin-bottom:0;">
                        <input type="text" name="searchCalon" value="<?php echo htmlspecialchars($searchCalon); ?>" placeholder="<?php echo ($lang == 'ms') ? '🔍 Cari calon...' : '🔍 Search candidates...'; ?>">
                    </div>
                    <button type="submit" class="btn" style="background:#8B0000; width:auto; padding:0 15px; margin-bottom:0; font-size: calc(14px * var(--font-scale, 1));"><?php echo ($lang == 'ms') ? 'Cari' : 'Search'; ?></button>
                    <?php if ($searchCalon !== ''): ?>
                        <a href="admin_dashboard.php?<?php echo http_build_query(array_filter(['searchUser' => $searchUser])); ?>" class="btn" style="background:#e0e0e0; color:#555; width:auto; padding:10px 15px; margin-bottom:0; font-size: calc(14px * var(--font-scale, 1)); text-decoration:none; display:inline-flex; align-items:center; border-radius:10px; font-weight:600; box-sizing:border-box;"><?php echo ($lang == 'ms') ? 'Reset' : 'Reset'; ?></a>
                    <?php endif; ?>
                </form>

                <table id="calonTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ID</th>
                            <th><?php echo $t['name']; ?></th>
                            <th><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($searchCalon !== '') {
                        $stmt = $conn->prepare("SELECT * FROM calon WHERE id_Calon LIKE ? OR nama_Calon LIKE ? ORDER BY id_Calon ASC");
                        $like_search = "%" . $searchCalon . "%";
                        $stmt->bind_param("ss", $like_search, $like_search);
                        $stmt->execute();
                        $res = $stmt->get_result();
                    } else {
                        $res = $conn->query("SELECT * FROM calon ORDER BY id_Calon ASC");
                    }

                    if ($res && $res->num_rows == 0) {
                        echo "<tr class='no-results-row'><td colspan='4'>" . (($lang == 'ms') ? "Tiada hasil dijumpai" : "No results found") . "</td></tr>";
                    } else if ($res) {
                        $no = 1;
                        while($row = $res->fetch_assoc()) {
                            echo "<tr data-pass='".htmlspecialchars($row['kata_laluan'], ENT_QUOTES)."'>";
                            echo "<td>".$no."</td>";
                            echo "<td>".$row['id_Calon']."</td>";
                            echo "<td>".$row['nama_Calon']."</td>";
                            echo "<td>
                                    <a href='admin_manage_calon.php?action=edit&id=" . $row['id_Calon'] . "' style='color:#8B0000; font-weight:bold; margin-right:5px;'>" . $t['edit'] . "</a>
                                    <a href='admin_dashboard.php?del_calon=" . $row['id_Calon'] . "' onclick='return confirm(\"" . addslashes($t['confirm_delete_candidate']) . "\")' style='color:#c0392b; font-weight:bold;'>" . $t['delete'] . "</a>
                                  </td>";
                            echo "</tr>";
                            $no++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Import CSV Modal -->
    <div class="modal-overlay" id="importOverlay" onclick="closeImportModal()"></div>
    <div class="modal-box" id="importModal" style="display:none;">
        <h3>📤 <?php echo ($lang == 'ms') ? 'Import Fail CSV' : 'Import CSV File'; ?></h3>
        
        <form method="POST" enctype="multipart/form-data" id="importForm">
            <div class="csv-format-hint">
                <strong><?php echo ($lang == 'ms') ? 'Format CSV:' : 'CSV Format:'; ?></strong><br>
                <code><?php echo ($lang == 'ms') ? 'Jenis' : 'Type'; ?>, ID, <?php echo ($lang == 'ms') ? 'Nama' : 'Name'; ?>, <?php echo ($lang == 'ms') ? 'Kelas/Kata Laluan' : 'Class/Password'; ?>, <?php echo ($lang == 'ms') ? 'Kata Laluan' : 'Password'; ?></code><br><br>
                <strong><?php echo ($lang == 'ms') ? 'Contoh:' : 'Example:'; ?></strong><br>
                <code>pengguna, U001, Ali bin Abu, 5A, 123</code><br>
                <code>calon, C001, Ahmad bin Hassan, 123</code>
            </div>

            <label><?php echo ($lang == 'ms') ? 'Pilih Fail CSV' : 'Select CSV File'; ?></label>
            <input type="file" name="csv_file" accept=".csv" required>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeImportModal()"><?php echo ($lang == 'ms') ? 'Batal' : 'Cancel'; ?></button>
                <button type="submit" class="btn-import-submit">📤 <?php echo ($lang == 'ms') ? 'Import' : 'Import'; ?></button>
            </div>
        </form>
    </div>

    <footer>&copy; 2026 Briged Putera</footer>

    <script>
    // Import Modal
    function openImportModal() {
        document.getElementById('importOverlay').style.display = 'block';
        document.getElementById('importModal').style.display = 'block';
    }
    function closeImportModal() {
        document.getElementById('importOverlay').style.display = 'none';
        document.getElementById('importModal').style.display = 'none';
    }

    // Download CSV - format compatible with import
    function downloadCSV() {
        var csv = [];
        // Header row - matches the import format expected by the server
        csv.push('Type,ID,<?php echo addslashes($t["name"]); ?>,<?php echo addslashes($t["class"]); ?>,Password');

        // Pengguna (users) rows
        var userTable = document.querySelectorAll('.card')[0].querySelectorAll('tbody tr');
        for (var i = 0; i < userTable.length; i++) {
            var tr = userTable[i];
            var cols = tr.querySelectorAll('td');
            if (cols.length < 2) continue;
            var id    = cols[1].textContent.trim().replace(/"/g, '""');
            var name  = cols[2].textContent.trim().replace(/"/g, '""');
            var kelas = cols[3] ? cols[3].textContent.trim().replace(/"/g, '""') : '';
            var pass  = (tr.getAttribute('data-pass') || '').replace(/"/g, '""');
            csv.push('pengguna,"' + id + '","' + name + '","' + kelas + '","' + pass + '"');
        }

        // Calon (candidates) rows
        var calonTable = document.querySelectorAll('.card')[1].querySelectorAll('tbody tr');
        for (var i = 0; i < calonTable.length; i++) {
            var tr = calonTable[i];
            var cols = tr.querySelectorAll('td');
            if (cols.length < 2) continue;
            var id   = cols[1].textContent.trim().replace(/"/g, '""');
            var name = cols[2].textContent.trim().replace(/"/g, '""');
            var pass = (tr.getAttribute('data-pass') || '').replace(/"/g, '""');
            csv.push('calon,"' + id + '","' + name + '",,"' + pass + '"');
        }

        var csvContent = '\uFEFF' + csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'admin_data_<?php echo date("Ymd_His"); ?>.csv';
        link.click();
    }
    </script>
</body>
</html>