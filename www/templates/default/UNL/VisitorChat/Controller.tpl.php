<?php
use \UNL\Templates\Templates;
$page = Templates::factory('Fixed', Templates::VERSION_4_1);

/**
 * @var $page \UNL\Templates\Version4x1\Fixed
 */

$wdn_include_path = \UNL\VisitorChat\Controller::$applicationDir . '/www';
if (file_exists($wdn_include_path . '/wdn/templates_4.1')) {
    $page->setLocalIncludePath($wdn_include_path);
}

$url = \UNL\VisitorChat\Controller::$url;

$page->addStyleSheet(\UNL\VisitorChat\Controller::$url . 'assets/css?for=operator&v=4.1');

//load model-specific css.
if (isset($context->options['model']) && file_exists(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/" . str_replace("\\", "/", $context->options['model']) . ".css")) {
    $page->addStyleSheet(\UNL\VisitorChat\Controller::$url . 'css/' . str_replace("\\", "/", $context->options['model']) . '.css');
}

if (\UNL\VisitorChat\User\Service::getCurrentUser()) {
    $page->addScript(\UNL\VisitorChat\Controller::$url . 'assets/js?for=' . \UNL\VisitorChat\User\Service::getCurrentUser()->type . '&v=4.1');
} else {
    //TODO: does this need a var?
    $page->addScriptDeclaration('VisitorChat = true;');
}

$page->head .= \UNL\VisitorChat\Controller::$headerHTML;

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
