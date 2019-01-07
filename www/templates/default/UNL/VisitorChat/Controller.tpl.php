<?php
use \UNL\Templates\Templates;
$page = Templates::factory('Fixed', Templates::VERSION_5);

/**
 * @var $page \UNL\Templates\Version4x1\Fixed
 */

$wdn_include_path = \UNL\VisitorChat\Controller::$applicationDir . '/www';
if (file_exists($wdn_include_path . '/wdn/templates_5.0')) {
    $page->setLocalIncludePath($wdn_include_path);
}

$url = \UNL\VisitorChat\Controller::$url;

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
$page->titlegraphic = $siteTitle;
$page->pagetitle = '<h1>' . \UNL\VisitorChat\Controller::$pagetitle . '</h1>';
$page->affiliation = '';

//Navigation
$page->breadcrumbs  = $savvy->render(null, 'UNL/VisitorChat/breadcrumbs.tpl.php');

$page->navlinks = $savvy->render(null, 'UNL/VisitorChat/main-nav.tpl.php');

//Main content
$page->maincontentarea = $savvy->render($context, 'UNL/VisitorChat/main-content.tpl.php');

echo $page;
