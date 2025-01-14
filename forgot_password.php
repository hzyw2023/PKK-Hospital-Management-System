<?php include('head.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="#">
    <meta name="keywords" content="Admin, Responsive">
    <meta name="author" content="Nikhil Bhalerao +919423979339.">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="files/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="files/assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="files/assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="files/assets/css/style.css">
</head>
<body class="fix-menu">
    <?php
    include('connect.php');
    if (isset($_POST['btn_forgot'])) {
        $otp = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 8)), 0, 8);
        $loginid = $_POST['loginid'];

        $sql = "SELECT * FROM admin WHERE loginid = '$loginid'";
        $ans = $conn->query($sql);
        $res = mysqli_fetch_array($ans);

        if ($res) {
            $person_fname = $res['fname'];
            $person_lname = $res['lname'];
            $personname = $person_fname . ' ' . $person_lname;
            $user_name = $res['username'];

            $msg = "Your Password is: '$otp'";
            $subject = 'Remind Password';

            $otp1 = hash('sha256', $otp);
            function createSalt() {
                return '2123293dsj2hu2nikhiljdsd';
            }
            $salt = createSalt();
            $otp_pass = hash('sha256', $salt . $otp1);

            $sql = "UPDATE admin SET password = '$otp_pass' WHERE loginid = '$loginid'";
            $ans1 = $conn->query($sql);

            $s = "SELECT * FROM tbl_email_config";
            $r = $conn->query($s);
            $rr = mysqli_fetch_array($r);

            if ($rr) {
                $mail_host = $rr['mail_driver_host'];
                $mail_name = $rr['name'];
                $mail_username = $rr['mail_username'];
                $mail_password = $rr['mail_password'];
                $mail_port = $rr['mail_port'];

                require 'PHPMailer/PHPMailer.php';
                require 'PHPMailer/SMTP.php';
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = $mail_host;
                $mail->SMTPAuth = true;
                $mail->Username = $mail_username;
                $mail->Password = $mail_password;
                $mail->SMTPSecure = 'tls';
                $mail->Port = $mail_port;
                $mail->setFrom($mail_username, $mail_name);
                $mail->addAddress($loginid, $personname);
                $mail->Subject = 'Forget Password';
                $mail->Body = "Hello, Your New Password is: '$otp'";

                if ($mail->send()) {
                  echo '<link rel="stylesheet" href="popup_style.css">
                        <div class="popup popup--icon -success js_success-popup popup--visible">
                            <div class="popup__background"></div>
                            <div class="popup__content">
                                <h3 class="popup__content__title">Success</h3>
                                <p>Email sent successfully. Please check your email.</p>
                                <p><a href="login.php"><button class="button button--success" data-for="js_success-popup">OK</button></a></p>
                        </div>
                    </div>';
              } else {
                  echo '<link rel="stylesheet" href="popup_style.css">
                        <div class="popup popup--icon -error js_error-popup popup--visible>
                            <div class="popup__background"></div>
                            <div class="popup__content">
                                <h3 class="popup__content__title">Error</h3>
                                <p>Something went wrong.</p>
                                <p><button class="button button--error" data-for="js_error-popup">Close</button></p>
                            </div>
                        </div>';
              }
          } else {
              echo '<link rel="stylesheet" href="popup_style.css">
                    <div class="popup popup--icon -error js_error-popup popup--visible>
                        <div class="popup__background"></div>
                        <div class="popup__content">
                            <h3 class="popup__content__title">Error</h3>
                            <p>Email configuration not found.</p>
                            <p><button class="button button--error" data-for="js_error-popup">Close</button></p>
                        </div>
                    </div>';
          }
      } else {
          echo '<link rel="stylesheet" href="popup_style.css">
                <div class="popup popup--icon -error js_error-popup popup--visible>
                    <div class="popup__background"></div>
                    <div class="popup__content">
                        <h3 class="popup__content__title">Error</h3>
                        <p>Invalid login ID.</p>
                        <p><button class="button button--error" data-for="js_error-popup">Close</button></p>
                    </div>
                </div>';
      }
  }
  ?>
  
    <section class="login-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <form method="POST" class="md-float-material form-material">
                        <div class="text-center">
                            <image class="profile-img" src="uploadImage/Logo/PKK.png" style="width: 20%"></image>
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-left">Recover your password</h3>
                                    </div>
                                </div>
                                <div class="form-group form-primary">
                                    <input type="text" name="loginid" class="form-control" required="" placeholder="Your Login ID">
                                    <span class="form-bar"></span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" name="btn_forgot" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">Reset Password</button>
                                    </div>
                                </div>
                                <p class="f-w-600 text-right">Back to <a href="login.php">Login.</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript" src="../files/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="../files/bower_components/modernizr/js/modernizr.js"></script>
    <script type="text/javascript" src="../files/bower_components/modernizr/js/css-scrollbars.js"></script>
    <script type="text/javascript" src="../files/bower_components/i18next/js/i18next.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/i18next-xhr-backend/js/i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/i18next-browser-languagedetector/js/i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src="../files/bower_components/jquery-i18next/js/jquery-i18next.min.js"></script>
    <script type="text/javascript" src="../files/assets/js/common-pages.js"></script>
</body>
</html>
