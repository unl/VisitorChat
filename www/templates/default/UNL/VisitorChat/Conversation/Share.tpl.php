<div id="shareFormErrorContainer" class="wdn_notice alert" hidden>
    <div class="message">
        <h4>Share Error</h4>
        <p id="shareFormErrorMessage"></p>
    </div>
</div>
<form class="dcf-form" id="shareForm" name="share" action="<?php echo UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->id . '/share', false, false)?>" method="POST">
    <?php // only one method for here, so use hidden input ?>
    <input type="hidden" name="method" value="invite">
    <?php
        //Display a list of all sites next.
        $list = \UNL\VisitorChat\Controller::$registryService->getAllSites();
        echo $savvy->render($list, 'UNL/VisitorChat/Conversation/ShareList.tpl.php');
    ?>
    <div class="dcf-input-checkbox">
        <input id="include-inactive-share-options" type="checkbox" value="0">
        <label for="include-inactive-share-options">Include inactive operators <span class="dcf-form-help">(Display Only)</span></label>
    </div>
    <input class="dcf-mt-4 dcf-btn dcf-btn-primary submit" type="submit" value="Submit">
</form>
<script>
  var showAllCheckbox = document.getElementById('include-inactive-share-options');
  if (showAllCheckbox) {
    showAllCheckbox.addEventListener('change', function() {
      showShareOptions();
    });
  }

  function showShareOptions() {
    var showAllOptions = document.getElementById('include-inactive-share-options').checked;
    var shareOptions = document.getElementsByClassName('share_option');
    var shareOptionsCount = shareOptions.length;
    for (var i = 0; i < shareOptionsCount; i++) {
      if (!showAllOptions && shareOptions[i].disabled) {
        shareOptions[i].style = 'display: none';
      } else {
        shareOptions[i].style = 'display: initial';
      }
      //Do something
    }
  }
  showShareOptions();

  var shareForm = document.getElementById('shareForm');
  shareForm.addEventListener('submit', function(submitEvent) {
    var shareFormErrorContainer = document.getElementById('shareFormErrorContainer');
    var shareFormErrorMessage = document.getElementById('shareFormErrorMessage');
    shareFormErrorContainer.setAttribute('hidden', '');
    var shareTo = document.getElementById('share_to');
    if (!shareTo.value || shareTo.value === 'default') {
      WDN.initializePlugin('notice');
      submitEvent.preventDefault();
      shareFormErrorMessage.innerText = 'Please select an operator to share with.';
      shareFormErrorContainer.removeAttribute('hidden');
    }

  });
</script>