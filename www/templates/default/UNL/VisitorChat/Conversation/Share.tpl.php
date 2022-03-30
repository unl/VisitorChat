<div id="shareFormErrorContainer" class="dcf-notice dcf-notice-warning dcf-d-grid dcf-w-max-xl dcf-ml-auto dcf-mr-auto dcf-mb-6 dcf-rounded dcf-notice-initialized" data-no-close-button="" style="display: none!important;" role="alertdialog" aria-labelledby="shareFormErrorContainer-heading"><div class="dcf-notice-icon"><svg class="dcf-h-100% dcf-w-100%" aria-hidden="true" focusable="false" height="24" width="24" viewBox="0 0 24 24"><path fill="#fefdfa" d="M22.9 22.3l-11-22c-.2-.3-.7-.3-.9 0l-11 22c-.1.3.1.7.5.7h22c.4 0 .6-.4.4-.7zM10.8 8.1c0-.4.3-.7.8-.7.2 0 .4.1.5.2.1.1.2.3.2.5v7.7c0 .2-.1.4-.2.5-.1.1-.3.2-.5.2-.4 0-.7-.3-.8-.7V8.1zm.7 12.2c-.7 0-1.2-.5-1.2-1.2s.5-1.2 1.2-1.2 1.2.5 1.2 1.2-.5 1.2-1.2 1.2z"></path></svg></div><div class="dcf-notice-body"><h2 class="dcf-notice-heading dcf-txt-h6 dcf-mb-0" id="shareFormErrorContainer-heading">Share Error</h2><div class="dcf-notice-message dcf-txt-sm"><p id="shareFormErrorMessage">Please select an operator to share with.</p></div></div></div>
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
    shareFormErrorContainer.style.display = 'none';
    var shareTo = document.getElementById('share_to');
    if (!shareTo.value || shareTo.value === 'default') {
      submitEvent.preventDefault();
      shareFormErrorMessage.innerText = 'Please select an operator to share with.';
      shareFormErrorContainer.style.display = 'block';
    }
  });
</script>
