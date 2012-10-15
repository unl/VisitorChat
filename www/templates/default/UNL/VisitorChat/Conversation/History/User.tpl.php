<ul class="historyList">
    <?php 
    foreach ($context as $conversation) {
        ?>
        <li>
            <a href='<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history/' . $conversation->id);?>'> <?php echo $conversation->getClient()->name;?></a>
            (<?php echo $conversation->date_created;?>)
        </li>
        <?php 
    }
    ?>
</ul>

<?php
if (count($context) > $context->options['limit'] && $context->options['limit'] != -1) {
    $pager = new stdClass();
    $pager->total  = count($context);
    $pager->limit  = $context->options['limit'];
    $pager->offset = $context->options['offset'];
    $pager->url    = \UNL\VisitorChat\Controller::$url . 'history';
    echo $savvy->render($pager, 'UNL/VisitorChat/PaginationLinks.tpl.php');
}
?>