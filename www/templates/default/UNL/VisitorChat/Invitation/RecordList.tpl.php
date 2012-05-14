<ul id="visitorChat_InvitationList">
<?php
foreach ($context as $invitation) {
    $name  = $invitation->invitee;
    $class = strtolower($invitation->status);
    
    if ($invitation->isForSite()) {
        $sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($invitation->invitee);
        
        if ($site = $sites->current()) {
            $name = $site->getTitle();
        }
    } else if ($account = \UNL\VisitorChat\User\Record::getByUID($invitation->invitee)) {
        $name = $account->name;
    }
    
    echo "<li><ul class='$class'>";
    foreach ($invitation->getAssignments() as $assignment) {
        $class = strtolower($assignment->status);
        echo "<li>" . $assignment->getUser()->name . "<span class='userAddress'>" . $name . "</span>"
        . "<span class='timestamp'>" . date("g:i:s A", strtotime($invitation->date_created)) . "</span></li>";
    }
    echo "</li></ul>";
}
?>
</ul>