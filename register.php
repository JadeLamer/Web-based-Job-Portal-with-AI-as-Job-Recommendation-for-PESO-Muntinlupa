<?php

use PHPMailer\PHPMailer\PHPMailer;

session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require "config.php";

$user_type = $fname = $mname = $lname = $suffix = $email = $password = $confirm_password = "";
$user_type_err = $fname_err = $mname_err = $lname_err = $suffix_err = $email_err = $password_err = $confirm_password_err = "";

function isEmailExists($conn, $email)
{
    $sql = "SELECT user_id FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $result = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        return $result > 0;
    }
    return false;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["user_type"]))) {
        $user_type_err = "Please select registration type.";
    } else {
        $user_type = trim($_POST["user_type"]);
    }

    if (empty(trim($_POST["fname"]))) {
        $fname_err = "Please enter first name.";
    } else {
        $fname = trim($_POST["fname"]);
    }

        $mname = trim($_POST["mname"]);

    if (empty(trim($_POST["lname"]))) {
        $lname_err = "Please enter last name.";
    } else {
        $lname = trim($_POST["lname"]);
    }

    $suffix = trim($_POST["suffix"]);
    

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    if (empty($email_err) && isEmailExists($conn, $email)) {
        $email_err = "Email already exists.";
    }

    if (empty($user_type_err) && empty($fname_err) && empty($mname_err) && empty($lname_err) && empty($suffix_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (user_type, fname, mname, lname, suffix, email, user_password, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssss", $param_user_type, $param_fname, $param_mname, $param_lname, $param_suffix, $param_email, $param_password, $param_verification_code);

            $param_user_type = $user_type;
            $param_fname = $fname;
            $param_mname = $mname;
            $param_lname = $lname;
            $param_suffix = $suffix;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_verification_code = md5($email . time());

            if (mysqli_stmt_execute($stmt)) {
                // phpmailer
                require 'PHPMailer/src/Exception.php';
                require 'PHPMailer/src/PHPMailer.php';
                require 'PHPMailer/src/SMTP.php';
                loadEnv();

                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Mailer = "smtp";
                $mail->SMTPDebug  = 0;
                $mail->SMTPAuth   = TRUE;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $_ENV['SMTP_PORT'];
                $mail->Host       = $_ENV['SMTP_HOST'];
                $mail->Username   = $_ENV['SMTP_USER']; // email address
                $mail->Password   = $_ENV['SMTP_PASS']; // password
                $mail->IsHTML(true);
                $mail->AddAddress($email, $fname . " " . $lname);
                $mail->SetFrom($_ENV['SMTP_USER'], "PESO Muntinlupa");
                $mail->Subject = "PESO Muntinlupa - Email Verification";
                $content = "<b>Hi " . $fname . " " . $lname . ",</b><br><br>";
                $content .= "Please click the link below to verify your email address.<br><br>";
                $content .= "<a href='http://".$website."/verify.php?code=$param_verification_code'>Verify Email</a><br><br>";
                $content .= "Thank you!<br>";
                $content .= "PESO Muntinlupa";
                $mail->MsgHTML($content);
                if (!$mail->Send()) {
                    $warning = "Error while sending Email.";
                    // var_dump($mail);
                } else {
                    $alert = "Please check your email for the verification link.";
                }
                // end of phpmailer
                
                $alert = "Please check your email for the verification link.";
            } else {
                $warning = "Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}



                


?>

<html>

<head>
    <title>PESO Job Portal - Register</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="container">
        <a href="login.php" class="btn btn-secondary">Back to Login</a><br><br>
        <div class="row">
            <div class="col-md">
                <img src="img/peso_muntinlupa.png" alt="PESO Logo" class="img-fluid">
            </div>
            <div class="col-md">
                <br>
                <h1>Register</h1>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'utf-8'); ?>" method="post">
                    <?php
                    if (!empty($alert)) {
                        echo '<div class="alert alert-success">' . $alert . '</div>';
                    } elseif (!empty($warning)) {
                        echo '<div class="alert alert-danger">' . $warning . '</div>';
                    } elseif (!empty($user_type_err)) {
                        echo '<div class="alert alert-danger">' . $user_type_err . '</div>';
                    } elseif (!empty($fname_err)) {
                        echo '<div class="alert alert-danger">' . $fname_err . '</div>';
                    } elseif (!empty($mname_err)) {
                        echo '<div class="alert alert-danger">' . $mname_err . '</div>';
                    } elseif (!empty($lname_err)) {
                        echo '<div class="alert alert-danger">' . $lname_err . '</div>';
                    } elseif (!empty($suffix_err)) {
                        echo '<div class="alert alert-danger">' . $suffix_err . '</div>';
                    } elseif (!empty($email_err)) {
                        echo '<div class="alert alert-danger">' . $email_err . '</div>';
                    } elseif (!empty($password_err)) {
                        echo '<div class="alert alert-danger">' . $password_err . '</div>';
                    } elseif (!empty($confirm_password_err)) {
                        echo '<div class="alert alert-danger">' . $confirm_password_err . '</div>';
                    }
                    ?>
                    <div class="mb-4">
                        <div class="form-text">Already have an account? <a href="login.php">Login here</a>.</div>
                    </div>
                    <div class="mb-4">
                        <label for="user_type" class="form-label">Registration Type</label>
                        <select class="form-select" name="user_type" id="user_type">
                            <option value="applicant">As Applicant</option>
                            <option value="company">As Company</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="fname" class="form-label">First Name</label>
                        <input type="text" name="fname" class="form-control" id="fname" aria-describedby="fnameHelp">
                    </div>
                    <div class="mb-4">
                        <label for="mname" class="form-label">Middle Name</label>
                        <input type="text" name="mname" class="form-control" id="mname" aria-describedby="mnameHelp">
                    </div>
                    <div class="mb-4">
                        <label for="lname" class="form-label">Last Name</label>
                        <input type="text" name="lname" class="form-control" id="lname" aria-describedby="lnameHelp">
                    </div>
                    <div class="mb-4">
                        <label for="suffix" class="form-label">Suffix</label>
                        <input type="text" name="suffix" class="form-control" id="suffix" aria-describedby="suffixHelp">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" class="form-control" id="email" aria-describedby="emailHelp">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password"
                            aria-describedby="passwordHelp">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password"
                            aria-describedby="confirm_passwordHelp">
                    </div>
                    <div class="mb-4">
                        <div class="form-text">By Registering yourself in this website, you agree on <a
                                href="https://privacy.gov.ph/data-privacy-act/" target="_blank">Privacy Notice from
                                Republic Act 10173 or Data Privacy Act of 2012</a>.</div>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Register">
                </form>
            </div>
        </div>

    </div>
</body>

</html>