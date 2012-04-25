<ul>
   <?php 
   foreach ($context as $site) {
       $class = "offline";
       if ($site->getAvailableCount()) {
           $class = "online";
       }
       ?>
       <li class=<?php echo $class?>>
           <input type='radio' name='to' value='<?php echo $site->getURL();?>' /><?php echo $site->getTitle();?>
           <ul>
               <?php
               foreach ($site->getMembers() as $member) {
                   if (!$account = $member->getAccount()) {
                       continue;
                   }
                   
                   //Do not display yourself.
                   if ($account->id == \UNL\VisitorChat\User\Record::getCurrentUser()->id) {
                       continue;
                   }
                   
                   $class = "offline";
                   if ($account->status == "AVAILABLE") {
                       $class = "online";
                   }
                   
                   echo "<li class='" . $class . "'><input type='radio' name='to' value='" . $account->uid . "' />" . $account->name . "</li>";
               }    
               ?>
           </ul>
       </li>
       <?php
   }?>
</ul>