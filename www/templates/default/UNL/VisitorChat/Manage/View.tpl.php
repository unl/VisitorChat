<div class="dcf-wrapper">
  <div class="dcf-d-flex dcf-jc-flex-end dcf-mb-6">
    <button class="dcf-d-block dcf-btn dcf-btn-primary" id="toggleOperatorStatus"><span id="currentOperatorStatus"></span></button>
  </div>
  <div class="dcf-grid dcf-col-gap-vw dcf-row-gap-6">
    <div class="dcf-col-100% dcf-col-25%-end@sm dcf-2nd@sm" id="clientChat_Invitations">
    </div>
    <div class="dcf-grid dcf-col-gap-vw dcf-row-gap-6 dcf-col-100% dcf-col-75%-start@sm">
      <div class="dcf-col-100% dcf-col-33%-start@sm" id="visitorChat_clients">
        <h2 class="dcf-txt-xs dcf-subhead dcf-bold unl-dark-gray">Conversations</h2>
        <div class="unl-font-sans" id="clientList">
        </div>
      </div>
      <div class="dcf-col-100% dcf-col-67%-end@sm chat-op-chat unl-font-sans" id="clientChat">
      </div>
    </div>
  </div>
</div>
<button class="dcf-btn dcf-btn-primary dcf-btn-toggle-modal share-conversation-modal-toggle-btn dcf-invisible" data-toggles-modal="share-conversation-modal" type="button" disabled>Share Conversation</button>
<div class="dcf-modal" id="share-conversation-modal" hidden>
    <div class="dcf-modal-wrapper">
        <div class="dcf-modal-header">
            <h3>Share This Conversation</h3>
            <button class="dcf-btn-close-modal">Close</button>
        </div>
        <div class="dcf-modal-content" id="share-conversation-modal-content"></div>
    </div>
</div>
</div>
<button class="dcf-btn dcf-btn-primary dcf-btn-toggle-modal operator-chat-request-modal-toggle-btn dcf-invisible" data-toggles-modal="operator-chat-request-modal" type="button" disabled>Operator Chat Request Modal Toggle</button>
<div class="dcf-modal" id="operator-chat-request-modal" hidden>
    <div class="dcf-modal-wrapper">
        <div class="dcf-modal-header">
            <h3>Incoming Chat Request</h3>
            <button id="operator-chat-request-modal-close-btn" class="dcf-btn-close-modal">Close</button>
        </div>
        <div class="dcf-modal-content" id="operator-chat-request-modal-content">
            <p>You have an incoming chat request. This request will expire in <span id="chatRequestCountDown">10</span> seconds.</p>
            <ul class="dcf-list-bare dcf-list-inline dcf-p-1 dcf-mt-3 dcf-mb-3">
                <li><button class="dcf-btn dcf-btn-primary" id="operator-assignment-reject">Reject</button></li>
                <li><button class="dcf-btn dcf-btn-secondary" id="operator-assignment-accept">Accept</button></li>
            </ul>
        </div>
    </div>
</div>
<button class="dcf-btn dcf-btn-primary dcf-btn-toggle-modal operator-alert-modal-toggle-btn dcf-invisible" data-toggles-modal="operator-alert-modal" type="button" disabled>Operator Alert Modal Toggle</button>
<div class="dcf-modal" id="operator-alert-modal" hidden>
    <div class="dcf-modal-wrapper">
        <div class="dcf-modal-header">
            <h3>Alert</h3>
            <button id="operator-alert-modal-close-btn" class="dcf-btn-close-modal">Close</button>
        </div>
        <div class="dcf-modal-content" id="operator-alert-modal-content"></div>
    </div>
</div>
