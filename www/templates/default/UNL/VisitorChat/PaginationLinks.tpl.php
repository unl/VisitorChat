<?php
$page->addScriptDeclaration("WDN.initializePlugin('pagination');");
$base_url = $context->getRawObject()->url;

function addParam($url, $var, $value)
{
    $param = $var . '=' . $value;
	if (substr_count($url, '?')) {
		return $url .= '&' . $param;
	}
	return $url .= '?' . $param;
}

function addURLParams($url, $params) {
    foreach ($params as $var => $value) {
        $url = addParam($url, $var, $value);
    }
    return $url;
}

$cpFudge = $context->limit == 1 ? 2 : 1;
$currentPage = intval(ceil(($context->offset  - 1) / $context->limit) + $cpFudge);
$numberOfPages = intval(ceil($context->total / $context->limit));
$showFirstLast = $numberOfPages > 10;

?>
<nav class="dcf-pagination">
    <ol class="dcf-list-bare dcf-list-inline">
    <?php if ($context->offset != 0) :?>
	    <?php if ($showFirstLast): ?>
            <li><a class="dcf-pagination-first" href="<?php echo addURLParams($base_url, array('limit'=>$context->limit, 'offset'=>0)); ?>">First</a></li>
	    <?php endif; ?>
        <li><a class="dcf-pagination-prev" href="<?php echo addURLParams($base_url, array('limit'=>$context->limit, 'offset'=>($context->offset-$context->limit))); ?>">Prev</a></li>
    <?php endif; ?>
    <?php
    $before_ellipsis_shown = false;
    $after_ellipsis_shown = false;
    for ($page = 1; $page*$context->limit < $context->total+$context->limit; $page++ ) {
        $link = addURLParams($base_url, array('limit'=>$context->limit, 'offset'=>($page-1)*$context->limit));
    ?>

    <?php if ($page === $currentPage): ?>
        <li><span class="dcf-pagination-selected"><?php echo $page; ?></span></li>
    <?php elseif ($page <= 3 || $page >= $numberOfPages - 2 || $page == $currentPage - 1 ||
        $page == $currentPage - 2 || $page == $currentPage + 1 || $page == $currentPage + 2): ?>
        <li><a href="<?php echo $link; ?>"><?php echo $page; ?></a></li>
    <?php elseif ($page < $currentPage && !$before_ellipsis_shown): ?>
        <li><span class="dcf-pagination-ellipsis">&mldr;</span></li>
        <?php $before_ellipsis_shown = true; ?>
    <?php elseif ($page > $currentPage && !$after_ellipsis_shown): ?>
        <li><span class="dcf-pagination-ellipsis">&mldr;</span></li>
        <?php $after_ellipsis_shown = true; ?>
    <?php endif; ?>
    <?php } // end for ?>

    <?php if (($context->offset+$context->limit) < $context->total) :?>
        <li><a class="dcf-pagination-next" href="<?php echo addURLParams($base_url, array('limit'=>$context->limit, 'offset'=>($context->offset+$context->limit))); ?>">Next</a></li>
	    <?php if ($showFirstLast): ?>
            <li><a class="dcf-pagination-last" href="<?php echo addURLParams($base_url, array('limit'=>$context->limit, 'offset'=>($numberOfPages-1) * $context->limit)); ?>">Last</a></li>
	    <?php endif; ?>
    <?php endif; ?>
    </ol>
</nav>
