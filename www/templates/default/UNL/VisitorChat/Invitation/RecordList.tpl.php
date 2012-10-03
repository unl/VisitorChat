<h2>Invitations</h2>
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
    
    echo "<li class='$class'>
              <span style='font-weight:bold;'>$name</span>
			  <span style='display: block;font-size: 0.8em;'>". \UNL\VisitorChat\User\Record::getByID($invitation->users_id)->name ."
			  <span style='float:right;'>" .
			    date("g:i:s A", strtotime($invitation->date_created)) . "</span>" .
				
			"</span>";
              
    $answeringSite = "";
    foreach ($invitation->getAssignments() as $assignment) {
        if ($answeringSite != $assignment->answering_site){
            if (!empty($answeringSite)) {
                echo "</ul></li></ul>";
            }
            
            $answeringSite = $assignment->answering_site;
            $site = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($answeringSite);
            $site = $site->current();
            echo "<ul class='answeringSites'>";
            echo "<li class='answeringSite $class'> <strong>Answering Site</strong>: " . $site->getTitle();
            echo "<ul class='assignments'>";
            
        }

        $assignmentClass = strtolower($assignment->status);
        echo "<li class='$assignmentClass'>
                  " . $assignment->getUser()->name .
                  //"<span class='timestamp'>" . date("g:i:s A", strtotime($assignment->date_created)) . "</span>" .
                  
            "</li>";
    }
    
    if (!empty($answeringSite)) {
        echo "</ul></li></ul>";
    }
    
    echo "</li>";
}
?>
</ul>