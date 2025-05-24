<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$login_error = '';
$register_error = '';
$register_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM pelanggan WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            
            if ($user['role'] == 'admin') {
                $_SESSION['is_admin'] = true;
                header("Location: admin.php");
            } else {
                $_SESSION['is_admin'] = false;
                header("Location: index.php");
            }
            exit();
        } else {
            $login_error = "Username/Email atau password salah";
        }
    } else {
        $login_error = "Username/Email atau password salah";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['reg_username']);
    $email = $conn->real_escape_string($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $nama = $conn->real_escape_string($_POST['nama']);
    
    $check_user = "SELECT id FROM pelanggan WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($check_user);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $register_error = "Username atau email sudah terdaftar";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql_register = "INSERT INTO pelanggan (username, nama_lengkap, email, password, role) 
                        VALUES (?, ?, ?, ?, 'user')";
        $stmt_register = $conn->prepare($sql_register);
        $stmt_register->bind_param("ssss", $username, $nama, $email, $hashed_password);
        
        if ($stmt_register->execute()) {
            $register_success = "Registrasi berhasil! Silakan login.";
        } else {
            $register_error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

$page_title = "Login / Register";
include 'header.php';
?>

<section class="page-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">Login</h4>
                            </div>
                            <div class="card-body">
                                <?php if($login_error): ?>
                                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                                <?php endif; ?>
                                
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username atau Email</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <h4 class="mb-0">Daftar Akun Baru</h4>
                            </div>
                            <div class="card-body">
                                <?php if($register_error): ?>
                                    <div class="alert alert-danger"><?php echo $register_error; ?></div>
                                <?php endif; ?>
                                <?php if($register_success): ?>
                                    <div class="alert alert-success"><?php echo $register_success; ?></div>
                                <?php endif; ?>
                                
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="reg_username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="reg_username" name="reg_username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reg_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="reg_email" name="reg_email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reg_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="register" class="btn btn-success">Daftar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>