<ul>
    <?php
    foreach ($context as $message) {
        echo "<li>" . \Epoch\Controller::$templater->render($message, 'UNL/VisitorChat/Message/View.tpl.php') . "</li>";
    }
    ?>
</ul>
