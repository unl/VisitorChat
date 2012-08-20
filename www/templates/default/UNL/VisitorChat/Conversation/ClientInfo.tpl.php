<?php
$ua = $context->parseUserAgent();
?>
<table class='zentable neutral'>
    <thead>
        <tr>
            <th colspan="2">Client Information</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Browser</td>
            <td><?php echo $ua->browser; ?></td>
        </tr>
        <tr>
            <td>OS</td>
            <td><?php echo $ua->os; ?></td>
        </tr>
        <tr>
            <td>IP address</td>
            <td><?php echo $context->ip_address; ?></td>
        </tr>
    </tbody>
</table>