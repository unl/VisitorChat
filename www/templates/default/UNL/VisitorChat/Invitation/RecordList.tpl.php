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
              <a class='name tooltip' title='This is the invited-server' href='#'>$name</a>
			  <span class='sub'>
			  <a class='source tooltip' title='Who called the invitation' href='#'>". \UNL\VisitorChat\User\Record::getByID($invitation->users_id)->name ."</a>
			  <a class='time tooltip' style='float:right;' title='Time the invitation was sent' href='#'>" .
			    date("g:i:s A", strtotime($invitation->date_created)) . "</a>" .
				
			"</span>";
	echo "<ul>";
              
    foreach ($invitation->getAssignments() as $assignment) {
  		$answeringSite = $assignment->answering_site;
		$site = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($answeringSite);
		$site = $site->current();
	
        $assignmentClass = strtolower($assignment->status);
        echo "<li class='$assignmentClass'>" .
                  "<a class='name tooltip' title='The person invited' href='#'>" . $assignment->getUser()->name . "</a>" .
                  "<span class='sub'><a class='source tooltip' title='The site they are from' href=#'>" . $site->getTitle() . "</a></span>" .
            "</li>";
    }
	echo "</ul>";
}
?>
</ul>