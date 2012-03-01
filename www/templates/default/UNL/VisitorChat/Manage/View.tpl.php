<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url?>js/chat.php?for=operator"></script>

<!--
<div id="visitorChat_header">
    Status: <span id="currentOperatorStatus"></span>
</div>
-->

<div id="operatorOptions">
    <a href="#" id="toggleOperatorStatus"><span id="currentOperatorStatus"></span></a>
</div>

<div id="clientList">
</div>

<div id="clientChat">
</div>

<div id="chatRequest" title="Incoming Chat Request">
    You have an incoming chat request.
    This request will expire in <span id="chatRequestCountDown">10</span> seconds.
</div>

<div id='visitorChat_sound_container'>
    <audio id='visitorChat_sound' src='<?php echo \UNL\VisitorChat\Controller::$url?>audio/message.wav'></audio>
</div>

<div id="notificationOptions">
    <a href="#" id="requestNotifications">Show Desktop Notifications</a>
</div>