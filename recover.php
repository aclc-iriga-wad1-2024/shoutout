<?php
/**
 * recover: Recover account page
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
$view      = 'recover';
$title     = 'Recover Account';
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
        Get back to spreading positivity! Regain access to your account in just a few steps.
    </p>
</main>


<!-- html bottom -->
<?php require_once __DIR__ . '/partials/html-2-bot.php'; ?>