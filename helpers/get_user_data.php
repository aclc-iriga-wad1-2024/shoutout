<?php
/**
 * get_user_data: Helper function to get signed-in user data
 * @return array|void
 */
function get_user_data()
{
    // prepare signed-in user data
    $user = [
        'id'        => 0,
        'firstname' => '',
        'lastname'  => '',
        'email'     => '',
        'username'  => '',
        'password'  => ''
    ];

    // process signed-in user data
    if(isset($_SESSION['shoutout_username']) && isset($_SESSION['shoutout_password']) && isset($GLOBALS['conn'])) {
        // query for the user with the stored username and hashed password from session
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ? AND `email_verified_at` IS NOT NULL");
        $stmt->bind_param("ss", $_SESSION['shoutout_username'], $_SESSION['shoutout_password']);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            // populate signed-in user data
            $row = $result->fetch_assoc();
            $user['id']        = intval($row['id']);
            $user['firstname'] = $row['firstname'];
            $user['lastname']  = $row['lastname'];
            $user['email']     = $row['email'];
            $user['username']  = $row['username'];
            $user['password']  = $row['password'];
            $user['avatar']    = file_exists('uploads/avatars/' . $row['avatar']) ? $row['avatar'] : 'no-avatar.jpg';
        }
        else {
            // force sign-out
            session_destroy();
            $cookie_expiration = time() - 3600; // 1 hour ago
            setcookie('shoutout_username', '', $cookie_expiration);
            setcookie('shoutout_password', '', $cookie_expiration);
            header('location: sign-in.php');
            exit();
        }
    }

    // return signed-in user data
    return $user;
}