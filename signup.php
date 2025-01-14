<?php 
session_start();
include('connect.php');

// Handle form submission
if(isset($_POST['btn_check'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $addr = mysqli_real_escape_string($conn, $_POST['addr']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Validate all required fields
    if(empty($fname) || empty($lname) || empty($email) || empty($contact) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
    }
    // Email format validation
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
    }
    // Check if email exists
    else {
        $stmt = $conn->prepare("SELECT loginid FROM admin WHERE loginid = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $_SESSION['error'] = "Email already registered";
        }
        // Password validation
        else if($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match";
        }
        else if(strlen($password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters long";
        }
        else if(!preg_match("/[A-Z]/", $password)) {
            $_SESSION['error'] = "Password must contain at least one uppercase letter";
        }
        else if(!preg_match("/[a-z]/", $password)) {
            $_SESSION['error'] = "Password must contain at least one lowercase letter";
        }
        else if(!preg_match("/[0-9]/", $password)) {
            $_SESSION['error'] = "Password must contain at least one number";
        }
        else if(!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
            $_SESSION['error'] = "Password must contain at least one special character";
        }
        else {
            // Hash password
            $passw = hash('sha256', $password);
            function createSalt() {
                return '2123293dsj2hu2nikhiljdsd';
            }
            $salt = createSalt();
            $pass = hash('sha256', $salt . $passw);

            // Get current date and time
            $created_on = date('Y-m-d');
            $delete_status = 0;

            // Insert into admin table
            $stmt = $conn->prepare("INSERT INTO admin (username, loginid, password, addr, mobileno, created_on, delete_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $username = $fname . ' ' . $lname;
            $stmt->bind_param("ssssssi", 
                $username,
                $email,
                $pass,
                $addr,
                $contact,
                $created_on,
                $delete_status
            );

            if($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error'] = "Registration failed: " . $stmt->error;
            }
        }
    }
}

// Get website details - keep your existing code
$que="select * from manage_website";
$query=$conn->query($que);
while($row=mysqli_fetch_array($query)) {
    extract($row);
    $business_name = $row['business_name'];
    $business_email = $row['business_email'];
    $business_web = $row['business_web'];
    $portal_addr = $row['portal_addr'];
    $addr = $row['addr'];
    $logo = $row['logo'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up - Clinic Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="stylesheet" href="popup_style.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="files/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="files/assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="files/assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="files/assets/css/style.css">
</head>

<body class="fix-menu">
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
            </div>
        </div>
    </div>

    <section class="login-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="auth-box card">
                        <div class="card-block">
                            <form method="post" action="">
                                <div class="row m-b-20">
                                    <div class="col-md-6 text-center">
                                        <image class="profile-img" src="uploadImage/Logo/PKK.png" style="width: 100%"></image>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 40px;">
                                        <h3 class="text-center txt-primary">Sign up</h3>
                                    </div>
                                </div>

                                <?php if(isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger">
                                        <?php 
                                            echo $_SESSION['error'];
                                            unset($_SESSION['error']);
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="text" class="form-control" name="fname" placeholder="First Name" required="">
                                            <span class="form-bar"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="text" class="form-control" name="lname" placeholder="Last Name" required="">
                                            <span class="form-bar"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="email" class="form-control" name="email" placeholder="Email" required="">
                                            <span class="form-bar"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="text" class="form-control" name="contact" placeholder="Phone Number" 
                                                   pattern="[0-9]{10}" title="Please enter valid 10-digit phone number" required="">
                                            <span class="form-bar"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group form-primary">
                                    <textarea name="addr" class="form-control" placeholder="Address" required=""></textarea>
                                    <span class="form-bar"></span>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="password" name="password" class="form-control" 
                                                   placeholder="Password" required=""
                                                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}"
                                                   title="Must contain at least one number, uppercase, lowercase letter, and special character, and at least 8 characters">
                                            <span class="form-bar"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="password" name="confirm-password" class="form-control" 
                                                   placeholder="Confirm Password" required="">
                                            <span class="form-bar"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row m-t-25 text-left">
                                    <div class="col-md-12">
                                        <div class="checkbox-fade fade-in-primary">
                                            <label>
                                                <input type="checkbox" required="">
                                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                <span class="text-inverse">I accept the terms and conditions</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row m-t-30">
                                    <div class="col-md-6">
                                        <button type="submit" name="btn_check" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">Sign up</button>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-inverse text-right m-b-0">Thank you.</p>
                                        <p class="text-inverse text-right"><a href="login.php"><b class="f-w-600">Back to Login</b></a></p>
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
    <script type="text/javascript" src="files/bower_components/modernizr/js/css-scrollbars.js"></script>
    <script type="text/javascript" src="files/bower_components/i18next/js/i18next.min.js"></script>
    <script type="text/javascript" src="files/bower_components/i18next-xhr-backend/js/i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src="files/bower_components/i18next-browser-languagedetector/js/i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src="files/bower_components/jquery-i18next/js/jquery-i18next.min.js"></script>
    <script type="text/javascript" src="files/assets/js/common-pages.js"></script>

    <!-- Password match validation -->
    <script>
        document.querySelector('input[name="confirm-password"]').addEventListener('input', function() {
            if(this.value !== document.querySelector('input[name="password"]').value) {
                this.setCustomValidity("Passwords do not match");
            } else {
                this.setCustomValidity("");
            }
        });
    </script>
</body>
</html>