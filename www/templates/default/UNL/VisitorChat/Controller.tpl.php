<?php
use \UNL\Templates\Templates;
$page = Templates::factory('Local', Templates::VERSION_5_1);

$savvy->addGlobal('page', $page);

/**
 * @var $page \UNL\Templates\Version4x1\Fixed
 */

$wdn_include_path = \UNL\VisitorChat\Controller::$applicationDir . '/www';
if (file_exists($wdn_include_path . '/wdn/templates_5.1')) {
    $page->setLocalIncludePath($wdn_include_path);
}

$url = \UNL\VisitorChat\Controller::$url;

// Add WDN Deprecated Styles
$page->head .= '<link rel="preload" href="/wdn/templates_5.1/css/deprecated.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"> <noscript><link rel="stylesheet" href="/wdn/templates_5.1/css/deprecated.css"></noscript>';

$page->addStyleSheet(\UNL\VisitorChat\Controller::$url . 'assets/css?for=operator&v=5.0');

//load model-specific css.
if (isset($context->options['model']) && file_exists(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/" . str_replace("\\", "/", $context->options['model']) . ".css")) {
    $page->addStyleSheet(\UNL\VisitorChat\Controller::$url . 'css/' . str_replace("\\", "/", $context->options['model']) . '.css');
}

//Make sure that the client stuff is never initialized
$page->addScriptDeclaration('var VisitorChat = true;');

if (\UNL\VisitorChat\User\Service::getCurrentUser()) {
    $page->addScript(\UNL\VisitorChat\Controller::$url . 'assets/js?for=' . \UNL\VisitorChat\User\Service::getCurrentUser()->type . '&v=5.0');
};

$page->jsbody .= \UNL\VisitorChat\Controller::$headerHTML;

//Titles
$siteTitle = 'UNLchat';
$page->doctitle = '<title>' . $siteTitle . ' | University of Nebraska-Lincoln</title>';
$page->titlegraphic = '<a href=' . \UNL\VisitorChat\Controller::$url . ' class="dcf-txt-h5">' . $siteTitle . '</a>';
$page->pagetitle = '<h1>' . \UNL\VisitorChat\Controller::$pagetitle . '</h1>';
$page->affiliation = '';

//Navigation
$page->breadcrumbs  = $savvy->render(null, 'UNL/VisitorChat/breadcrumbs.tpl.php');

$page->navlinks = $savvy->render(null, 'UNL/VisitorChat/main-nav.tpl.php');

//Main content
$page->maincontentarea = $savvy->render($context, 'UNL/VisitorChat/main-content.tpl.php');

echo $page;
