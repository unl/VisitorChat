<?php
/**
 * IE8+ does not allow for cookies to be passed with its XDomainRequest.
 * So use a work around instead.
 */
if (isset($_GET['PHPSESSID']) && $_GET['PHPSESSID'] != "false") {
    session_id($_GET['PHPSESSID']);
}

session_start();

if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = "unknown";
}

//Do we have a key in the session? (session hijacking prevention)
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = md5($_SERVER['HTTP_USER_AGENT']);
}

//Check the key (session hijacking prevention)
if ($_SESSION['key'] != md5($_SERVER['HTTP_USER_AGENT'])) {
    session_write_close();
    session_start();
}

require_once(dirname(__FILE__) . "/securimage_show.php");