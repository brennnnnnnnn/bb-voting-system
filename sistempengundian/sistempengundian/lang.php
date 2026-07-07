<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ms';
}

// Change language
if (isset($_GET['lang'])) {
    if ($_GET['lang'] == 'en') {
        $_SESSION['lang'] = 'en';
    } else {
        $_SESSION['lang'] = 'ms';
    }
}

$lang = $_SESSION['lang'];

$translations = [
    'ms' => [
        'site_title' => 'Sistem Pengundian',
        'site_subtitle' => 'Briged Putera',
        'welcome' => 'Selamat Datang!',
        'system_desc' => 'Sistem Pengundian Jawatankuasa<br>Briged Putera Malaysia',
        'login' => 'Log Masuk',
        'login_subtitle' => 'Masukkan maklumat anda untuk meneruskan',
        'register' => 'Daftar Akaun',
        'register_subtitle' => 'Sila masukkan maklumat akaun anda',
        'register_now' => 'Daftar Sekarang',
        'forgot_password' => 'Lupa Kata Laluan?',
        'back_to_login' => '← Kembali ke Log Masuk',
        'id_user' => 'ID Pengguna',
        'password' => 'Kata Laluan',
        'remember_me' => 'Ingat saya',
        'no_account' => 'Belum ada akaun?',
        'has_account' => 'Sudah ada akaun?',
        'id_placeholder' => 'Masukkan ID anda (cth: 12345)',
        'pass_placeholder' => 'Masukkan kata laluan',
        'error_invalid' => 'ID atau Kata Laluan tidak sah.',
        'admin_hint' => 'Admin:',
        'name' => 'Nama Penuh',
        'class' => 'Kelas',
        'name_placeholder' => 'Nama Penuh',
        'class_placeholder' => 'Cth: 4SK1',
        'forgot_title' => 'Tukar Kata Laluan',
        'forgot_subtitle' => 'Sila masukkan maklumat anda',
        'new_password' => 'Kata Laluan Baru',
        'id_placeholder_forgot' => 'Cth: 88888',
        'name_placeholder_forgot' => 'Nama seperti dalam sistem',
        'pass_placeholder_forgot' => 'Kata laluan baru',
        'success_reset' => '✅ Kata laluan berjaya ditukar!',
        'error_reset' => '❌ ID dan Nama tidak sepadan.',
        'error_system' => 'Ralat sistem:',
        'format_id_error' => 'Format ID Salah! Mesti 5 digit nombor.',
        'name_error' => 'Nama hanya boleh mengandungi huruf.',
        'id_exists' => 'ID sudah wujud!',
        'success_reg' => '✅ Pendaftaran berjaya! Sila log masuk.',
        // Navigation
        'nav_dashboard' => 'Papan Pemuka',
        'nav_vote' => 'Undi',
        'nav_results' => 'Keputusan',
        'nav_profile' => 'Profil',
        'nav_logout' => 'Log Keluar',
        'nav_back_dashboard' => 'Kembali ke Papan Pemuka',
        // Results Page
        'results_title' => 'Keputusan Undian Terkini',
        'total_votes' => 'Jumlah Undi',
        'votes' => 'undi',
        'admin_details' => 'Perincian Undian (Admin Sahaja)',
        'time' => 'Masa',
        'username' => 'Nama Pengguna',
        'position' => 'Jawatan',
        'candidate_chosen' => 'Calon Dipilih',
        'no_data' => 'Tiada data undian terperinci.',
        // Vote Page
        'vote_welcome' => 'Selamat Datang',
        'vote_instruction' => 'Sila pilih calon untuk setiap jawatan',
        'progress' => 'Kemajuan',
        'voted_success' => '✓✓ Terima kasih! Undian anda telah berjaya direkodkan.',
        'already_voted' => '✓✓ Telah mengundi',
        'submit_all' => 'Hantar Semua Undi',
        'submit_msg_pending' => 'Sila pilih calon untuk SEMUA 3 jawatan sebelum hantar undi',
        'submit_msg_complete' => '✓✓ Semua jawatan dipilih! Klik butang di bawah untuk hantar undi.',
        'congrats' => '🎉 Tahniah! Anda telah selesai mengundi.',
        'thanks_voted' => 'Teriba kasih kerana menjalankan tanggungjawab anda.',
        'pilih_lagi' => 'Pilih %s lagi jawatan sebelum hantar undi',
        // Admin Dashboard
        'admin_panel' => 'Panel Pentadbir',
        'user_list' => 'Senarai Pengguna',
        'candidate_list' => 'Senarai Calon',
        'add' => 'Tambah',
        'edit' => 'Edit',
        'delete' => 'Padam',
        'actions' => 'Tindakan',
        'confirm_delete_user' => 'Adakah anda pasti mahu memadam pengguna ini?',
        'confirm_delete_candidate' => 'Adakah anda pasti mahu memadam calon ini?',
        'success_delete_user' => 'Pengguna berjaya dipadam.',
        'success_delete_candidate' => 'Calon berjaya dipadam.',
        // Admin Management
        'manage_user' => 'Urus Pengguna',
        'manage_candidate' => 'Urus Calon',
        'update_user' => 'Kemaskini Pengguna',
        'add_user' => 'Tambah Pengguna',
        'update_candidate' => 'Kemaskini Calon',
        'add_candidate' => 'Tambah Calon',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'id_hint' => 'Format: 5 digit nombor (Contoh: 12345)',
        'name_hint' => 'Hanya huruf dibenarkan',
        'id_readonly' => 'ID Pengguna tidak boleh diubah! Sila daftar semula jika perlu.',
        'success_add_user' => 'Pengguna berjaya ditambah!',
        'success_update_user' => 'Maklumat berjaya dikemaskini!',
        'success_add_candidate' => 'Calon berjaya ditambah!',
        'success_update_candidate' => 'Maklumat berjaya dikemaskini!',
        // Candidate Dashboard
        'candidate_profile' => 'Profil Calon',
        'current_votes' => 'Jumlah Undi Semasa',
        'save_changes' => 'Simpan Perubahan',
        'update_success' => 'Maklumat berjaya dikemaskini.',
        // Position Names
        'pos_pengerusi' => 'PENGERUSI',
        'pos_naib_pengerusi' => 'NAIB PENGERUSI',
        'pos_setiausaha' => 'SETIAUSAHA'
    ],
    'en' => [
        'site_title' => 'Voting System',
        'site_subtitle' => 'Boys\' Brigade',
        'welcome' => 'Welcome!',
        'system_desc' => 'Committee Voting System<br>Boys\' Brigade Malaysia',
        'login' => 'Login',
        'login_subtitle' => 'Enter your information to continue',
        'register' => 'Register Account',
        'register_subtitle' => 'Please enter your account information',
        'register_now' => 'Register Now',
        'forgot_password' => 'Forgot Password?',
        'back_to_login' => '← Back to Login',
        'id_user' => 'User ID',
        'password' => 'Password',
        'remember_me' => 'Remember Me',
        'no_account' => "Don't have an account?",
        'has_account' => 'Already have an account?',
        'id_placeholder' => 'Enter your ID (e.g., 12345)',
        'pass_placeholder' => 'Enter your password',
        'error_invalid' => 'Invalid ID or Password.',
        'admin_hint' => 'Admin:',
        'name' => 'Full Name',
        'class' => 'Class',
        'name_placeholder' => 'Full Name',
        'class_placeholder' => 'e.g., 4SK1',
        'forgot_title' => 'Change Password',
        'forgot_subtitle' => 'Please enter your information',
        'new_password' => 'New Password',
        'id_placeholder_forgot' => 'e.g., 88888',
        'name_placeholder_forgot' => 'Name as in system',
        'pass_placeholder_forgot' => 'New password',
        'success_reset' => '✅ Password changed successfully!',
        'error_reset' => '❌ ID and Name do not match.',
        'error_system' => 'System error:',
        'format_id_error' => 'Invalid ID Format! Must be 5 digits.',
        'name_error' => 'Name can only contain letters.',
        'id_exists' => 'ID already exists!',
        'success_reg' => '✅ Registration successful! Please log in.',
        // Navigation
        'nav_dashboard' => 'Dashboard',
        'nav_vote' => 'Vote',
        'nav_results' => 'Results',
        'nav_profile' => 'Profile',
        'nav_logout' => 'Logout',
        'nav_back_dashboard' => 'Back to Dashboard',
        // Results Page
        'results_title' => 'Latest Voting Results',
        'total_votes' => 'Total Votes',
        'votes' => 'votes',
        'admin_details' => 'Voting Details (Admin Only)',
        'time' => 'Time',
        'username' => 'Username',
        'position' => 'Position',
        'candidate_chosen' => 'Candidate Chosen',
        'no_data' => 'No detailed voting data.',
        // Vote Page
        'vote_welcome' => 'Welcome',
        'vote_instruction' => 'Please select a candidate for each position',
        'progress' => 'Progress',
        'voted_success' => '✓✓ Thank you! Your vote has been successfully recorded.',
        'already_voted' => '✓✓ Already voted',
        'submit_all' => 'Submit All Votes',
        'submit_msg_pending' => 'Please select candidates for ALL 3 positions before submitting',
        'submit_msg_complete' => '✓✓ All positions selected! Click the button below to submit.',
        'congrats' => '🎉 Congratulations! You have finished voting.',
        'thanks_voted' => 'Thank you for fulfilling your responsibility.',
        'pilih_lagi' => 'Select %s more position(s) before submitting',
        // Admin Dashboard
        'admin_panel' => 'Admin Panel',
        'user_list' => 'User List',
        'candidate_list' => 'Candidate List',
        'add' => 'Add',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'actions' => 'Actions',
        'confirm_delete_user' => 'Are you sure you want to delete this user?',
        'confirm_delete_candidate' => 'Are you sure you want to delete this candidate?',
        'success_delete_user' => 'User deleted successfully.',
        'success_delete_candidate' => 'Candidate deleted successfully.',
        // Admin Management
        'manage_user' => 'Manage Users',
        'manage_candidate' => 'Manage Candidates',
        'update_user' => 'Update User',
        'add_user' => 'Add User',
        'update_candidate' => 'Update Candidate',
        'add_candidate' => 'Add Candidate',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'id_hint' => 'Format: 5 digit numbers (e.g., 12345)',
        'name_hint' => 'Only letters allowed',
        'id_readonly' => 'User ID cannot be changed! Please register again if needed.',
        'success_add_user' => 'User added successfully!',
        'success_update_user' => 'Information updated successfully!',
        'success_add_candidate' => 'Candidate added successfully!',
        'success_update_candidate' => 'Information updated successfully!',
        // Candidate Dashboard
        'candidate_profile' => 'Candidate Profile',
        'current_votes' => 'Current Vote Total',
        'save_changes' => 'Save Changes',
        'update_success' => 'Information updated successfully.',
        // Position Names
        'pos_pengerusi' => 'CHAIRMAN',
        'pos_naib_pengerusi' => 'VICE CHAIRMAN',
        'pos_setiausaha' => 'SECRETARY'
    ]
];

$t = $translations[$lang];

/**
 * Generates a URL for switching language while preserving other GET parameters.
 */
function get_lang_url($new_lang) {
    $params = $_GET;
    $params['lang'] = $new_lang;
    return "?" . http_build_query($params);
}
?>
