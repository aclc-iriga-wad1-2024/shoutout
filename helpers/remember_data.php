<?php
/**
 * remember_data: Helper function to remember data from cookies, if no session is set.
 * @return void
 */
function remember_data()
{
    if(!isset($_SESSION['shoutout_username']) || !isset($_SESSION['shoutout_password'])) {
        if(isset($_COOKIE['shoutout_username']) && isset($_COOKIE['shoutout_password'])) {
            $_SESSION['shoutout_username'] = $_COOKIE['shoutout_username'];
            $_SESSION['shoutout_password'] = $_COOKIE['shoutout_password'];
        }
    }
}