<form id='visitorChat_captchaForm' class='unl_visitorchat_form' method="post"
      action="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("captcha/edit", true, true);?>">
    
    <div id='VisitorChat_captcha_container'>
        <img id="VisitorChat_captcha_img"
             src="<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/securimage_visitorchat.php?sid=<?php echo md5(uniqid()) ?>"
             alt="CAPTCHA Image" align="left"/>
        <object class='VisitorChat_captcha_supportImg' type="application/x-shockwave-flash"
                data="<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/securimage_play.swf?bgcol=#ffffff&amp;icon_file=./images/audio_icon.png&amp;audio_file=<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/securimage_play.php"
                height="15" width="15">
        <param name="movie"
               value="<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/securimage_play.swf?bgcol=#ffffff&amp;icon_file=<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/images/audio_icon.png&amp;audio_file=<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/securimage_play.php"/>
    </object>
    
    <a tabindex="-1" style="border-style: none;" href="#" title="Refresh Image"
       onclick="document.getElementById('VisitorChat_captcha_img').src = '<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/securimage_show.php?sid=' + Math.random(); this.blur(); return false"><img
        class='VisitorChat_captcha_supportImg' height="15" width="15"
        src="<?php echo \UNL\VisitorChat\Controller::$url?>/captcha/images/refresh.png" alt="Reload Image"
        onclick="this.blur()" align="bottom" border="0"></a><br/>
    <strong>Please enter the code:</strong><br/>
    <input type="text" name="code" size="12" maxlength="8"/>
    <input type="submit" value='submit'/>
  </div>
</form>
