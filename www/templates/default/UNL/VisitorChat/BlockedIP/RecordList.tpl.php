View a list of blocked IP addresses

<ul>
    <?php
    foreach ($context as $block) {
        ?>
        <li><a href="<?php echo $block->getEditURL() ?>"><?php echo $block->ip_address ?></a></li>
        <?php
    }
    ?>
    
</ul>