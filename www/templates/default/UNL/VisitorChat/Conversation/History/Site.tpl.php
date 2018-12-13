<?php if ($context->user): ?>
    <p>This list will only show conversations in which the user has participated.</p>
<?php else: ?>
    <p>This list contains all chat conversations, regardless of their status (accepted, failed, pending, chatting).</p>
<?php endif; ?>

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
    $pager->url    = $context->getPagerURL();
    echo $savvy->render($pager, 'UNL/VisitorChat/PaginationLinks.tpl.php');
}
?>