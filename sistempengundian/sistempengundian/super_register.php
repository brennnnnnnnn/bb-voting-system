<?php
// 1. TURN ON ALL ERROR MESSAGES
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. MANUAL DATABASE CONNECTION (Bypassing db.php)
// Try 'undi' first, if that fails, use the long name.
$dbname = "undi"; 
$conn = new mysqli("localhost", "root", "", $dbname);

// If 'undi' doesn't exist, try the long name automatically
if ($conn->connect_error) {
    $dbname = "sistem_pengundian_jawatankuasa_briged_putera";
    $conn = new mysqli("localhost", "root", "", $dbname);
    
    if ($conn->connect_error) {
        die("<h2 style='color:red; background:white; padding:20px;'>
            CRITICAL ERROR: Could not connect to database.<br>
            Please make sure you created the database 'undi' or 'sistem_pengundian...'.
            </h2>");
    }
}

$status = "";

// 3. HANDLE FORM SUBMISSION
if (isset($_POST['try_register'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $pass = $_POST['password'];

    // Check if ID exists
    $check = $conn->query("SELECT * FROM pengguna WHERE id_Pengguna='$id'");
    if ($check->num_rows > 0) {
        $status = "ID '$id' already exists! Try a different ID.";
    } else {
        // Attempt Insert
        $sql = "INSERT INTO pengguna (id_Pengguna, nama_Pengguna, kelas_Pengguna, kata_laluan) 
                VALUES ('$id', '$nama', '$kelas', '$pass')";
        
        if ($conn->query($sql) === TRUE) {
            $status = "SUCCESS! User '$nama' saved to database.";
        } else {
            $status = "SQL ERROR: " . $conn->error;
        }
    }
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
    <title>Debug Register</title>
    <style>
        body { font-family: sans-serif; background: #333; color: white; padding: 20px; }
        .container { background: white; color: black; max-width: 600px; margin: auto; padding: 20px; border-radius: 8px; }
        input { width: 95%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; }
        button { width: 100%; padding: 15px; background: blue; color: white; font-weight: bold; cursor: pointer; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠️ Debug Registration Page</h2>
    <p>Connected to Database: <strong><?php echo $dbname; ?></strong></p>

    <?php if ($status != ""): ?>
        <script>alert('<?php echo addslashes($status); ?>');</script>
    <?php endif; ?>

    <form method="post" action="super_register.php">
        <label>ID Pengguna (Must be unique):</label>
        <input type="text" name="id" placeholder="e.g., TEST01" required>

        <label>Nama:</label>
        <input type="text" name="nama" placeholder="e.g., Ali" required>

        <label>Kelas:</label>
        <input type="text" name="kelas" placeholder="e.g., 5 Bestari" required>

        <label>Password:</label>
        <input type="text" name="password" value="1234" required>

        <button type="submit" name="try_register">REGISTER NOW</button>
    </form>

    <hr>

    <h3>👥 Current Users in Database</h3>
    <p>If you see names below, the database is working.</p>
    <table>
        <tr><th>ID</th><th>Name</th><th>Class</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM pengguna");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id_Pengguna']}</td>
                        <td>{$row['nama_Pengguna']}</td>
                        <td>{$row['kelas_Pengguna']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No users found in database.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
