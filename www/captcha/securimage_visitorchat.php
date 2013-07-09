<?php
//Set the session cookie name.
session_name("UNL_Visitorchat_Session");

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

if (!isset($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = "unknown";
}

//Do we have a key in the session? (session hijacking prevention)
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
}

//Check the key (session hijacking prevention)
if ($_SESSION['key'] != md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'])) {
    session_write_close();
    session_start();
}

require_once(dirname(__FILE__) . "/securimage_show.php");