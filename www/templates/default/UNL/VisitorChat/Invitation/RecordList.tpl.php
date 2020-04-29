<h2 class="dcf-txt-xs dcf-subhead dcf-bold unl-dark-gray">Invitations</h2>
<ul class="dcf-list-bare dcf-mb-0 dcf-txt-xs unl-font-sans" id="visitorChat_InvitationList">
<?php
foreach ($context as $invitation) {
    /**
     * @var $invitation \UNL\VisitorChat\Invitation\Record
     */
    $name  = $invitation->invitee;
    $class = strtolower($invitation->status);
    $name = $invitation->getInviteeTitle();

    echo "<li class='$class'>
              <span class='name' style='cursor:pointer;' title='This is who the invitation was sent to (can be either a site or a person)'>$name</span>
			  <span class='sub dcf-d-block dcf-txt-xs'>
			  <span class='source' style='cursor:pointer;' title='Who called the invitation'>". \UNL\VisitorChat\User\Record::getByID($invitation->users_id)->name ."</span>
			  <span class='time' style='cursor:pointer;' style='float:right;' title='Time the invitation was sent'>" .
			    date("g:i:s A", strtotime($invitation->date_created)) . "</span>" .
			"</span>";
	echo '<ul>';

    foreach ($invitation->getAssignments() as $assignment) {
        /**
         * @var $assignment \UNL\VisitorChat\Assignment\Record
         */
        $site = $assignment->getAnsweringSite();

        $siteTitle = ($site)?$site->getTitle():'unknown';

        $assignmentClass = strtolower($assignment->status);
        echo "<li class='$assignmentClass'>" .
                  "<span class='name' style='cursor:pointer;' title='The person invited'>" . $assignment->getUser()->name . "</span>" .
                  "<span class='sub force-wrap dcf-d-block dcf-txt-xs'><span class='source' style='cursor:pointer;' title='The site they are from'>" . $siteTitle . "</span></span>" .
            "</li>";
    }
	echo "</ul>";
}
?>
</ul>