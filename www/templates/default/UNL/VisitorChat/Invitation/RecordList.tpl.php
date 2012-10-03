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
              <span class='name'>$name</span>
			  <span class='sub'>". \UNL\VisitorChat\User\Record::getByID($invitation->users_id)->name ."
			  <span style='float:right;'>" .
			    date("g:i:s A", strtotime($invitation->date_created)) . "</span>" .
				
			"</span>";
	echo "<ul>";
              
    foreach ($invitation->getAssignments() as $assignment) {
  		$answeringSite = $assignment->answering_site;
		$site = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($answeringSite);
		$site = $site->current();
	
        $assignmentClass = strtolower($assignment->status);
        echo "<li class='$assignmentClass'>" .
                  "<span class='name'>" . $assignment->getUser()->name . "</span>" .
                  "<span class='sub'>" . $site->getTitle() . "</span>" .
            "</li>";
    }
	echo "</ul>";
}
?>
</ul>