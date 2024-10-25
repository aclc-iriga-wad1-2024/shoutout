<?php
/**
 * sign-out: Sign out handler
 */

// initialize session
session_start();

// destroy session and remembered data when the sign-out form is submitted
if(isset($_POST['sign-out']))
{
    session_destroy();
    $cookie_expiration = time() - 3600; // 1 hour ago
    setcookie('shoutout_username', '', $cookie_expiration);
    setcookie('shoutout_password', '', $cookie_expiration);
}

// redirect to homepage
header('location: sign-in.php');
exit();
?>