<div id='visitorChat_container'>
    <?php
    echo $savvy->render($context->actionable);
    ?>
</div>

<div id="chatRequest" title="Incoming Chat Request">
    You have an incoming chat request.
    This request will expire in <span id="chatRequestCountDown">10</span> seconds.
</div>

<div id="alert" title="Alert"></div>

<div id="shareChat" title="Share"></div>

<div id='visitorChat_sound_container'>
    <audio id='visitorChat_sound' src='<?php echo \UNL\VisitorChat\Controller::$url ?>audio/message.wav'></audio>
</div>
<div id="visitorChat_brightBox">
    <p>Hello all!</p>
</div>
