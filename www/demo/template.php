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
$page = Templates::factory('Local', Templates::VERSION_5_1);

/**
 * @var $page \UNL\Templates\Version5x1\Local
 */

$wdn_include_path = \UNL\VisitorChat\Controller::$applicationDir . '/www';
if (file_exists($wdn_include_path . '/wdn/templates_5.1')) {
    $page->setLocalIncludePath($wdn_include_path);
}

//Titles
$page->doctitle = '<title>' . $title . ' | University of Nebraska-Lincoln</title>';
$page->titlegraphic = '<a href=' . \UNL\VisitorChat\Controller::$url . ' class="dcf-txt-h5">Chat Demo</a>';
$page->pagetitle = '<h1>'. $title . '</h1>';
$page->affiliation = '';

// Add WDN Deprecated Styles
$page->head .= '<link rel="preload" href="/wdn/templates_5.0/css/deprecated.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"> <noscript><link rel="stylesheet" href=/wdn/templates_5.0/css/deprecated.css"></noscript>';

//Navigation
$page->breadcrumbs = "";

$page->navlinks = '<ul><li><a href="index.php">Home</a></li><li><a href="page2.php">Page 2</a></li></ul>';

//Main content
$page->maincontentarea = '<div class="dcf-bleed">
  <div class="dcf-wrapper">
    '. $main_content .'
  </div>
</div>';

echo $page;
