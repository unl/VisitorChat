<ul class='visitorChat_shareList'>
   <?php 
   foreach ($context as $site) {
       $class = "offline";
       if ($site->getAvailableCount()) {
           $class = "online";
       }
       ?>
       <li class=<?php echo $class?>>
           <?php echo $site->getTitle();?>
           <span class="dropArrow"></span>
           <ul class='visitorChat_shareList_userList'>
               <li>
                   <input type='radio' name='to' value='<?php echo $site->getURL();?>' />All Operators for <?php echo $site->getTitle();?>
               </li>
               <?php
               foreach ($site->getMembers() as $member) {
                   if (!$account = $member->getAccount()) {
                       continue;
                   }
                   
                   //Do not display yourself.
                   if ($account->id == \UNL\VisitorChat\User\Service::getCurrentUser()->id) {
                       continue;
                   }
                   
                   $class = "offline";
                   if ($account->status == "AVAILABLE") {
                       $class = "online";
                   }
                   
                   echo "<li class='" . $class . "'><input type='radio' name='to' value='" .  $site->getURL() . "::" . $account->uid . "' />" . $account->name . "</li>";
               }    
               ?>
           </ul>
       </li>
       <?php
   }?>
</ul>