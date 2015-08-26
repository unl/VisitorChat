<script type="text/javascript">
WDN.loadCSS('/wdn/templates_3.0/css/content/pagination.css');
</script>
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

<ul class="wdn_pagination">
    <?php if ($context->offset != 0) :?>
        <?php
        $url = addParam($base_url, 'limit='.$context->limit);
        $url = addParam($url, 'offset='.($context->offset-$context->limit));
        ?>
    <li class="arrow"><a href="<?php echo $url?>" title="Go to the previous page">&larr; prev</a></li>
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
    <li class="<?php echo $class; ?>">
        <?php
        if ($class !== 'selected') { ?>
            <a href="<?php echo $link; ?>" title="Go to page <?php echo $page; ?>"><?php echo $page; ?></a>
        <?php
        } else {
            echo $page;
        } ?>
    </li>
    <?php } ?>
    <?php if (($context->offset+$context->limit) < $context->total) :?>
        <?php
        $url = addParam($base_url, 'limit='.$context->limit);
        $url = addParam($url, 'offset='.($context->offset+$context->limit));
        ?>
    <li class="arrow"><a href="<?php echo $url ?>" title="Go to the next page">next &rarr;</a></li>
    <?php endif; ?>
</ul>