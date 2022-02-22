<?php
use \UNL\Templates\Templates;
$page = Templates::factory('Local', Templates::VERSION_5_3);

$savvy->addGlobal('page', $page);

/**
 * @var $page \UNL\Templates\Version5x2\Local
 */

$wdn_include_path = \UNL\VisitorChat\Controller::$applicationDir . '/www';
if (file_exists($wdn_include_path . '/wdn/templates_5.3')) {
    $page->setLocalIncludePath($wdn_include_path);
}

$url = \UNL\VisitorChat\Controller::$url;

// Cache bust
$cb = '20220110';

$page->addStyleSheet(\UNL\VisitorChat\Controller::$url . 'assets/css?for=operator&v=5.0&cb=' . $cb);

//load model-specific css.
if (isset($context->options['model']) && file_exists(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/" . str_replace("\\", "/", $context->options['model']) . ".css")) {
    $page->addStyleSheet(\UNL\VisitorChat\Controller::$url . 'css/' . str_replace("\\", "/", $context->options['model']) . '.css');
}

//Make sure that the client stuff is never initialized
$page->addScriptDeclaration('var VisitorChat = true;');

if (\UNL\VisitorChat\User\Service::getCurrentUser()) {
    $page->addScript(\UNL\VisitorChat\Controller::$url . 'assets/js?for=' . \UNL\VisitorChat\User\Service::getCurrentUser()->type . '&v=5.3&cb=' . $cb);
};

$page->jsbody .= \UNL\VisitorChat\Controller::$headerHTML;

//Titles
$siteTitle = 'Chat Administration';
$page->doctitle = '<title>' . $siteTitle . ' | University of Nebraska-Lincoln</title>';
$page->titlegraphic = '<a href=' . \UNL\VisitorChat\Controller::$url . ' class="dcf-txt-h5">' . $siteTitle . '</a>';
$page->pagetitle = '<h1>' . \UNL\VisitorChat\Controller::$pagetitle . '</h1>';
$page->affiliation = '';

//Navigation
$page->breadcrumbs  = $savvy->render(null, 'UNL/VisitorChat/breadcrumbs.tpl.php');

$page->navlinks = $savvy->render(null, 'UNL/VisitorChat/main-nav.tpl.php');

//Main content
$page->maincontentarea = $savvy->render($context, 'UNL/VisitorChat/main-content.tpl.php');

if (isset(\UNL\VisitorChat\Controller::$siteNotice) && \UNL\VisitorChat\Controller::$siteNotice->display) {
    $page->displayDCFNoticeMessage(
        \UNL\VisitorChat\Controller::$siteNotice->title,
        \UNL\VisitorChat\Controller::$siteNotice->message,
        \UNL\VisitorChat\Controller::$siteNotice->type,
        \UNL\VisitorChat\Controller::$siteNotice->noticePath,
        \UNL\VisitorChat\Controller::$siteNotice->containerID);
}

echo $page;
