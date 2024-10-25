<?php
/**
 * sign-up: Sign up page
 */

// initialize session
session_start();

// remember data
require_once __DIR__ . '/helpers/remember_data.php';
remember_data();

// check for session and redirect to homepage if 'shoutout_username' and 'shoutout_password' session already exists
if(isset($_SESSION['shoutout_username']) && isset($_SESSION['shoutout_password']))
{
    header('location: index.php');
    exit();
}
// otherwise, continue with the rest of this page:

// app config
require_once __DIR__ . '/config/app.php';
if(!isset($app)) exit();

// database config
require_once __DIR__ . '/config/database.php';
if(!isset($conn)) exit();

// initialize global data
$view      = 'sign-up';
$title     = 'Sign up';
$email     = '';
$username  = '';
$firstname = '';
$lastname  = '';
$error     = '';
$success   = '';

// process email verification request from email link
if(isset($_GET['verify_email']) && isset($_GET['code']))
{
    // get passed data
    $email = trim($_GET['verify_email']);
    $code  = $_GET['code'];

    // validate email and code
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? AND `code` = ? AND `email_verified_at` IS NULL LIMIT 1");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows <= 0) {
        $error = 'Invalid email verification link.';
    }
    else {
        $row = $result->fetch_assoc();

        // validate code expiration
        $current_timestamp = time();
        if($current_timestamp > strtotime($row['code_expires_at'])) {
            $error = 'Expired email verification link.';
        }

        // if no more errors, proceed with email verification...
        else {
            // update user `email_verified_at`, clear `code` and `code_expires_at`
            $email_verified_at = date('Y-m-d H:i:s', $current_timestamp);
            $stmt = $conn->prepare("UPDATE `users` SET `email_verified_at` = ?, `code` = '', `code_expires_at` = NULL WHERE `id` = ?");
            $stmt->bind_param("si", $email_verified_at, $row['id']);
            $stmt->execute();

            // if successful, sign-in the user using username and hashed password (not remembered) and inform the user
            if($stmt->affected_rows > 0) {
                $_SESSION['shoutout_username'] = $row['username'];
                $_SESSION['shoutout_password'] = $row['password'];
                $success = '<b>Welcome to ' . $app['name'] . ', ' . $row['firstname'] . '!</b> Your email is verified, and you\'re all set!';
            }
            // otherwise, set unknown error
            else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}

// process sign-up request when the form is submitted
else if(isset($_POST['sign-up']))
{
    // get sign-up data
    $email     = trim($_POST['email']);     // trim() strips beginning and end whitespaces
    $username  = trim($_POST['username']);
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // validate sign-up data: no fields should be empty
    if(empty($email) || empty($username) || empty($firstname) || empty($lastname) || empty($password1)) {
        $error = 'All fields are required.';
    }
    // validate password: should be at least 6 characters
    else if(strlen($password1) < 6) {
        $error = 'Password should be at least 6 characters.';
    }
    // validate password: $password1 and $password2 should match
    else if($password1 != $password2) {
        $error = 'Passwords should match.';
    }
    // validate email
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    }
    // validate email and username: should be unique (query database)
    else {
        // validate email
        $stmt = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $error = 'Email address is already taken.';
        }
        else {
            // validate username
            $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $error = 'Username is already taken.';
            }
        }
    }

    // if there's no $error, insert new user
    if($error == '') {
        $password_hashed = password_hash($password1, PASSWORD_DEFAULT); // hash the entered password
        require_once __DIR__ . '/helpers/generate_code.php';
        $code = generate_code();
        $code_expiration = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        $stmt = $conn->prepare("INSERT INTO `users`(`email`, `username`, `firstname`, `lastname`, `password`, `code`, `code_expires_at`) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $email, $username, $firstname, $lastname, $password_hashed, $code, $code_expiration);
        $stmt->execute();
        // if insert is successful, send email verification
        if($stmt->affected_rows > 0) {
            // generate email verification link
            $link = $app['base_url'] . '/sign-up.php?verify_email=' . $email . '&code=' . $code;

            // generate subject and html body of email to be sent
            $subject = $app['name'] . ' - Verify your Email';
            $body  = '<p>Hello,</p>';
            $body .= '<p>';
            $body .= 'We noticed your email was used to sign up to <b>' . $app['name'] . '</b>. ';
            $body .= 'To activate your account, please verify your email by clicking this link:';
            $body .= '</p>';
            $body .= '<p><b><a href="' . $link . '">' . $link . '</a></b></p>';
            $body .= '<p><b>Important:</b> This link is valid until <b>' . $code_expiration . '</b></p>';
            $body .= '<p>If you didn\'t make this request, feel free to ignore this message.</p>';
            $body .= '<br>';
            $body .= '<p>Best regards,<br>The ' . $app['name'] . ' Team</p>';

            // send the email
            require_once __DIR__ . '/helpers/send_email.php';
            $response = send_email($email, $subject, $body);
            if($response['success'])
                $success = 'Almost there! We sent a verification email to <b><i>' . $email . '</i></b>. Check your inbox to activate your account.';
            else
                $error = $response['error'];
        }
        // otherwise, set unknown error
        else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>

<!-- html top -->
<?php require_once __DIR__ . '/partials/html-1-top.php'; ?>
<!-- navbar -->
<?php require_once __DIR__ . '/partials/navbar.php'; ?>


<!-- main content -->
<main class="container pt-3">
    <!-- content header -->
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="m-0"><?= $title ?></h2>
    </div>
    <p>
        Become part of our uplifting community!
        Sign up to share your shout-outs and celebrate remarkable individuals.
    </p>

    <!-- display success message (if available) -->
    <?php if(!empty($success)) { ?>
        <div class="alert alert-success">
            <i class="fas fa-fw fa-info-circle"></i> <?= $success ?>
        </div>
        <a href="index.php" class="text-decoration-none"><i class="fas fa-fw fa-arrow-left"></i> Home</a>
    <!-- otherwise, display the sign-up form -->
    <?php } else { ?>
        <div class="row pt-3">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="sign-up.php">
                            <div class="mb-3">
                                <div class="row">
                                    <!-- email -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                                    </div>

                                    <!-- username -->
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <!-- firstname -->
                                    <div class="col-md-6">
                                        <label for="firstname" class="form-label">First name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required>
                                    </div>

                                    <!-- lastname -->
                                    <div class="col-md-6">
                                        <label for="lastname" class="form-label">Last name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- password -->
                            <div class="mb-3">
                                <label for="password1" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password1" name="password1" required>
                            </div>

                            <!-- confirm password -->
                            <div class="mb-3">
                                <label for="password2" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password2" name="password2" required>
                            </div>

                            <!-- error message (if there's any) -->
                            <?php if($error != '') { ?>
                                <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error ?></p>
                            <?php } ?>

                            <!-- sign-up button -->
                            <button type="submit" name="sign-up" class="btn btn-dark">Sign up</button>

                            <!-- sign-in link -->
                            <div class="mt-2 d-flex justify-content-end">
                                <small>Already have an account? <a href="sign-in.php" class="text-decoration-none">Sign in</a></small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</main>


<!-- html bottom -->
<?php require_once __DIR__ . '/partials/html-2-bot.php'; ?>