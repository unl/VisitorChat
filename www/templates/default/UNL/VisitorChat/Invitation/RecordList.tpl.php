<h2>Invitations</h2>
<ul id="visitorChat_InvitationList">
<?php
foreach ($context as $invitation) {
    /**
     * @var $invitation \UNL\VisitorChat\Invitation\Record
     */
    $name  = $invitation->invitee;
    $class = strtolower($invitation->status);
    $name = $invitation->getInviteeTitle();
    
    echo "<li class='$class'>
              <span class='name tooltip' title='This is who the invitation was sent to (can be either a site or a person)'>$name</span>
			  <span class='sub'>
			  <span class='source tooltip' title='Who called the invitation'>". \UNL\VisitorChat\User\Record::getByID($invitation->users_id)->name ."</span>
			  <span class='time tooltip' style='float:right;' title='Time the invitation was sent'>" .
			    date("g:i:s A", strtotime($invitation->date_created)) . "</span>" .
				
			"</span>";
	echo "<ul>";
              
    foreach ($invitation->getAssignments() as $assignment) {
        /**
         * @var $assignment \UNL\VisitorChat\Assignment\Record
         */
        $site = $assignment->getAnsweringSite();
        
        $siteTitle = ($site)?$site->getTitle():'unknown';
        
        $assignmentClass = strtolower($assignment->status);
        echo "<li class='$assignmentClass'>" .
                  "<span class='name tooltip' title='The person invited'>" . $assignment->getUser()->name . "</span>" .
                  "<span class='sub'><span class='source tooltip' title='The site they are from'>" . $siteTitle . "</span></span>" .
            "</li>";
    }
	echo "</ul>";
}
?>
</ul>