<?php

$base_url = $context->getRawObject()->url;
function addParam($url, $param)
{
    if (substr_count($url, '?')) {
        return $url .= '&' . $param;
    }
    
    return $url .= '?' . $param;
}
?>

<ul class="dcf-txt-sm dcf-list-bare dcf-list-inline">
    <?php if ($context->offset != 0) :?>
        <?php
        $url = addParam($base_url, 'limit='.$context->limit);
        $url = addParam($url, 'offset='.($context->offset-$context->limit));
        ?>
    <li class="dcf-mr-1 dcf-mb-1"><a class="dcf-btn dcf-btn-secondary dcf-p-1" href="<?php echo $url?>" title="Go to the previous page">&larr; prev</a></li>
    <?php endif; ?>
    <?php for ($page = 1; $page*$context->limit < $context->total+$context->limit; $page++ ) {
        $url = addParam($base_url, 'limit='.$context->limit);
        $url = addParam($url, 'offset='.(($page-1)*$context->limit));
        
        $link = $url;
        $class = '';
        if (($page-1)*$context->limit == $context->offset) {
            $class = 'selected';
        }
    ?>
    <li class="dcf-mr-1 <?php echo $class; ?>">
        <?php
        if ($class !== 'selected') { ?>
            <a class="dcf-btn dcf-btn-secondary dcf-p-1 dcf-mr-1" href="<?php echo $link; ?>" title="Go to page <?php echo $page; ?>"><?php echo $page; ?></a>
        <?php } else { ?>
            <span class="dcf-bold dcf-txt-lg dcf-p-0"><?php echo $page; ?></span>
        <?php } ?>
    </li>
    <?php } ?>
    <?php if (($context->offset+$context->limit) < $context->total) :?>
        <?php
        $url = addParam($base_url, 'limit='.$context->limit);
        $url = addParam($url, 'offset='.($context->offset+$context->limit));
        ?>
    <li class="dcf-mr-1"><a class="dcf-btn dcf-btn-secondary dcf-p-1" href="<?php echo $url ?>" title="Go to the next page">next &rarr;</a></li>
    <?php endif; ?>
</ul>