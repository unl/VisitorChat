<?php
if (file_exists(dirname(__FILE__) . "/config.inc.php")) {
    require_once dirname(__FILE__) . "/config.inc.php";
} else {
    require_once dirname(__FILE__) . "/config.sample.php";
}