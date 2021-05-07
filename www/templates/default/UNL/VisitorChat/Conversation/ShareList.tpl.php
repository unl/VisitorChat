<div class="dcf-form-group">
    <label for="share_to">Select operator to share with</label>
    <select id='share_to' name='to' data-placeholder='Select a team or group' class='chzn-select dcf-input-select'>
        <option value='default'></option>
        <?php
        foreach ($context as $site) {
            $disabled = "disabled='disabled'";
            if ($site->getAvailableCount()) {
                $disabled = "";
            }
            ?>
            <optgroup class='share_option' label='<?php echo $site->getTitle();?>' <?php echo $disabled?>>
                <option class='share_option' value='<?php echo urlencode($site->getURL());?>'>All Operators for <?php echo $site->getTitle();?></option>
                <?php
                foreach ($site->getMembers() as $member) {
                    if (!$account = $member->getAccount()) {
                        continue;
                    }

                    //Do not display yourself.
                    if ($account->id == \UNL\VisitorChat\User\Service::getCurrentUser()->id) {
                        //continue;
                    }

                    $disabled = "disabled='disabled'";
                    if ($account->getStatus()->status == "AVAILABLE") {
                        $disabled = "";
                    }

                    echo "<option class='share_option' value='" . urlencode($site->getURL()) . "::" . $account->uid . "' " . $disabled . ">" . $account->name . "</option>";
                }
                ?>
                </ul>
            </optgroup>
            <?php
        }?>
    </select>
</div>