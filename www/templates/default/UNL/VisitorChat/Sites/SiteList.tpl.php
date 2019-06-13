<table class="dcf-table dcf-txt-sm">
  <thead>
    <tr>
      <th>Site</th>
      <th>Chat Available</th>
      <th>Your Role</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($context->sites as $site): ?>
    <?php
        $role = $site->getCurrentUserRole();
        $editMembersLink = $site->getEditSiteMembersLink();
    ?>
    <tr>
      <td class="dcf-txt-middle"><a href="<?php echo $site->getURL(); ?>"><?php echo truncate($site->getTitle(), 60); ?></a></td>
      <td class="dcf-txt-middle dcf-txt-center"><?php echo getChatAvailbility($site); ?></td>
      <td class="dcf-txt-middle"><?php echo $role; ?></td>
      <td class="dcf-txt-middle">
        <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/site?url=' . urlencode($site->getRawObject()->getURL())) ?>" class="dcf-btn dcf-btn-secondary">View Details</a>
        <?php if (!empty($editMembersLink)) { ?>
        <a href="<?php echo $editMembersLink; ?>" class="dcf-btn dcf-btn-secondary">Edit Roles</a>
        <?php } ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php

function getChatAvailbility($site) {
    if ($site->getAvailableCount() > 0) {
       return '<span class="user-status available"></span><span class="dcf-sr-only">(available)</span>';
    }
    return '<span class="user-status busy"></span><span class="dcf-sr-only">(busy)</span>';
}

function truncate($string, $length, $html = true)
{
    if (strlen($string) > $length) {
        if ($html) {
            // Grabs the original and escapes any quotes
            $original = str_replace('"', '\"', $string);
        }

        // Truncates the string
        $string = substr($string, 0, $length);

        // Appends ellipses and optionally wraps in a hoverable span
        if ($html) {
            $string = '<span title="' . $original . '">' . $string . '&hellip;</span>';
        } else {
            $string .= '...';
        }
    }

    return $string;
}

?>