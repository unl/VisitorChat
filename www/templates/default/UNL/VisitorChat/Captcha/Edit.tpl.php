<form id='visitorChat_captchaForm' method="post" action="verify.php">
    <?php
    require_once('recaptchalib.php');
    echo recaptcha_get_html(\UNL\VisitorChat\Controller::$captchaPublicKey);
    ?>
    <input type="submit" />
</form>
