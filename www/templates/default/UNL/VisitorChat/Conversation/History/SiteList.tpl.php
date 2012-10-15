<ul>
<?php 
foreach ($context as $site) {
    echo "<li><a href='". \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history/sites/' . $site->getURL()) . "'>".$site->getTitle()."</a></li>";
}
?>
</ul>