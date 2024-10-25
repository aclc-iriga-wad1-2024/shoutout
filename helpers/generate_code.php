<?php
/**
 * generate_code: Helper function to generate a random code.
 * @param $length
 * @return string
 * @throws Exception
 */
function generate_code($length = 16)
{
    return bin2hex(random_bytes($length / 2));
}