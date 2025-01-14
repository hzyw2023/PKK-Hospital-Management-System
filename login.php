<?php
session_start();
session_regenerate_id(true);

// Security Configuration
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION', 300); // 5 minutes lockout
define('SALT', '2123293dsj2hu2nikhiljdsd');

// Include database connection
include('connect.php');

// Security Logging Function
function securityLogger($event_type, $username, $details) {
    $log_dir = '/var/log/application/security/';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0700, true);
    }

    $log_file = $log_dir . 'security_log_' . date('Y-m-d') . '.log';
    
    $log_entry = date('Y-m-d H:i:s') . " | $event_type | $username | " . 
                 $details . " | IP: " . $_SERVER['REMOTE_ADDR'] . 
                 " | User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    
    error_log($log_entry, 3, $log_file);
}

// Enhanced Password Hashing Function
function hashPassword($password) {
    $hash = hash('sha512', SALT . $password);
    return hash('sha512', $hash . SALT);
}

// Password Strength Validation
function isStrongPassword($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{10,}$/', $password);
}

// Login Attempt Management
function validateLoginAttempts($email) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $current_time = time();
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], 
        function($attempt) use ($current_time) {
            return ($current_time - $attempt['time']) < LOCKOUT_DURATION;
        }
    );

    $_SESSION['login_attempts'][] = [
        'email' => $email,
        'time' => $current_time
    ];

    $email_attempts = array_filter($_SESSION['login_attempts'], 
        function($attempt) use ($email) {
            return $attempt['email'] === $email;
        }
    );

    if (count($email_attempts) >= MAX_LOGIN_ATTEMPTS) {
        return [
            'status' => false, 
            'message' => "Too many failed login attempts. Please try again after 5 minutes."
        ];
    }

    return ['status' => true];
}

// Captcha Generation
function generateCaptcha() {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $captcha = substr(str_shuffle($characters), 0, 6);
    $_SESSION['captcha'] = strtoupper($captcha);
    return $captcha;
}

// Handle POST Request
if(isset($_POST['btn_login']))
{
    // Validate login attempts
    $attempt_validation = validateLoginAttempts($_POST['email']);
    if (!$attempt_validation['status']) {
        $_SESSION['error'] = $attempt_validation['message'];
        header("Location: login.php");
        exit();
    }

    // Validate Captcha
    if (!isset($_SESSION['captcha']) || 
        strtoupper(trim($_POST['captcha'])) !== $_SESSION['captcha']) {
        $_SESSION['error'] = "Invalid captcha. Please try again.";
        header("Location: login.php");
        exit();
    }

    // User type specific login
    if ($_POST['user'] === 'patient' && !isStrongPassword($_POST['password'])) {
        $_SESSION['error'] = "Password does not meet strength requirements. Must be at least 10 characters with uppercase, lowercase, numbers, and special characters.";
        header("Location: login.php");
        exit();
    }

    // Prepare password for comparison
    $passw = hash('sha256', $_POST['password']);
    $salt = '2123293dsj2hu2nikhiljdsd';
    $pass = hash('sha256', $salt . $passw);

    // Query based on user type
    $table = '';
    $id_field = '';
    if ($_POST['user'] === 'admin') {
        $table = 'admin';
        $id_field = 'id';
    } else if ($_POST['user'] === 'doctor') {
        $table = 'doctor';
        $id_field = 'doctorid';
    } else if ($_POST['user'] === 'patient') {
        $table = 'patient';
        $id_field = 'patientid';
    }

    $sql = "SELECT * FROM $table WHERE loginid = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $_POST['email'], $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $_SESSION[$id_field] = $row[$id_field];
        $_SESSION['id'] = $row[$id_field];
        $_SESSION['password'] = $row['password'];
        $_SESSION['email'] = $row['loginid'];
        $_SESSION['fname'] = $row['fname'] ?? $row['doctorname'] ?? $row['patientname'];
        $_SESSION['user'] = $_POST['user'];

        // Log successful login
        securityLogger('login_success', $_POST['email'], "Successful login for {$_POST['user']}");

        // Redirect to index
        ?>
        <div class="popup popup--icon -success js_success-popup popup--visible">
            <div class="popup__background"></div>
            <div class="popup__content">
                <h3 class="popup__content__title">Success</h3>
                <p>Login Successfully</p>
                echo "<script>window.location.href = 'index.php';</script>";
                exit();
            </div>
        </div>
        <?php
        exit();
    }

    // If login fails
    securityLogger('login_failed', $_POST['email'], "Invalid login attempt for {$_POST['user']}");
    ?>
    <div class="popup popup--icon -error js_error-popup popup--visible">
        <div class="popup__background"></div>
        <div class="popup__content">
            <h3 class="popup__content__title">Error</h3>
            <p>Invalid Email or Password</p>
            <p>
                <a href="login.php"><button class="button button--error" data-for="js_error-popup">Close</button></a>
            </p>
        </div>
    </div>
    <?php
}

// Generate Captcha
$captcha = generateCaptcha();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive">
    <meta name="author" content="Nikhil Bhalerao +919423979339.">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="files/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="files/assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="files/assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="files/assets/css/style.css">
    <link rel="stylesheet" href="popup_style.css">
</head>
<body class="fix-menu">
<section class="login-block">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="auth-box card">
                    <div class="text-center">
                        <image class="profile-img" src="uploadImage/Logo/PKK.png" style="width: 70%"></image>
                    </div> 
                    <div class="card-block">
                        <div class="row m-b-20">
                            <div class="col-md-12">
                                <h5 class="text-center txt-primary">Sign In</h5>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group form-primary">
                                <select name="user" class="form-control" required="">
                                    <option value="">-- Select One --</option>
                                    <option value="admin">Admin</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="patient">Patient</option>
                                </select>
                                <span class="form-bar"></span>
                            </div>
                            <div class="form-group form-primary">
                                <input type="email" name="email" class="form-control" required="" placeholder="Email">
                                <span class="form-bar"></span>
                            </div>
                            <div class="form-group form-primary">
                                <input type="password" name="password" class="form-control" required="" placeholder="Password">
                                <span class="form-bar"></span>
                            </div>
                            <div class="form-group form-primary">
                                <label>Captcha: <b><?= $captcha; ?></b></label>
                                <input type="text" name="captcha" class="form-control" required="" placeholder="Enter Captcha">
                                <span class="form-bar"></span>
                            </div>
                            <div class="row m-t-25 text-left">
                                <div class="col-12">
                                    <div class="forgot-phone text-right f-right">
                                        <a href="forgot_password.php" class="text-right f-w-600"> Forgot Password?</a>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-t-30">
                                <div class="col-md-12">
                                    <button type="submit" name="btn_login" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">LOGIN</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript" src="files/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript" src="files/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="files/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="files/bower_components/bootstrap/js/bootstrap.min.js"></script>

<script type="text/javascript" src="files/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>

<script type="text/javascript" src="files/bower_components/modernizr/js/modernizr.js"></script>
<script type="text/javascript" src="files/bower_components/i18next/js/i18next.min.js"></script>
<script type="text/javascript" src="files/bower_components/i18next-xhr-backend/js/i18nextXHRBackend.min.js"></script>
<script type="text/javascript" src="files/bower_components/jquery-i18next/js/jquery-i18next.min.js"></script>
<script type="text/javascript" src="files/assets/js/common-pages.js"></script>

</body>
</html>
