<?php 
/**
 * This is an example web page.  Note that the only reason why this page is PHP is so that
 * the url to the server is linked with this current instance of the visitorchat.  In most cases
 * the url will be static and no php will be required.
 */
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/config.sample.php';
}


use \UNL\Templates\Templates;
$page = Templates::factory('Fixed', Templates::VERSION_4_1);

/**
 * @var $page \UNL\Templates\Version4x1\Fixed
 */

$wdn_include_path = \UNL\VisitorChat\Controller::$applicationDir . '/www';
if (file_exists($wdn_include_path . '/wdn/templates_4.1')) {
    $page->setLocalIncludePath($wdn_include_path);
}

//Titles


$page->doctitle = '<title>' . $title . ' | University of Nebraska-Lincoln</title>';
$page->titlegraphic = 'Chat Demo';
$page->pagetitle = '<h1>'. $title . '</h1>';
$page->affiliation = '';

//Navigation
$page->breadcrumbs = "";

$page->navlinks = '<ul><li><a href="index.php">Home</a></li><li><a href="page2.php">Page 2</a></li></ul>';

//Main content
$page->maincontentarea = '<div class="wdn-band">
  <div class="wdn-inner-wrapper">
    '. $main_content .'
  </div>
</div>';

echo $page;
