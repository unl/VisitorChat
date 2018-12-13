<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

//TODO: Convert to web view
//TODO: Make site selectable

$db = \UNL\VisitorChat\Controller::getDB();

echo "==== GENERAL METRICS ====" . PHP_EOL;

$sql = 'SELECT date_created 
        FROM conversations 
        WHERE method = "CHAT" 
            AND initial_url = "http://www.unl.edu/" 
        ORDER BY date_created LIMIT 1;';

if ($result = $db->query($sql)) {
    $row = $result->fetch_assoc();
    echo "First Conversation: " . $row['date_created'] . PHP_EOL;
}

$sql = 'SELECT count(*) as total
        FROM conversations 
        WHERE method ="CHAT";';

if ($result = $db->query($sql)) {
    $row = $result->fetch_assoc();
    echo "Total # of conversations since inception: " . $row['total'] . PHP_EOL;
}

$sql = 'SELECT round(count(*) / (datediff(max(date_created), min(date_created)) - (floor(datediff(max(date_created), min(date_created))/7)*2))) as average
        FROM conversations
        WHERE method = "CHAT"
              AND (hour(date_created) BETWEEN 8 AND 16)
              AND (DAYOFWEEK(date_created) BETWEEN 2 AND 6);';

if ($result = $db->query($sql)) {
    $row = $result->fetch_assoc();
    echo "Average # of conversations from 8:00 - 5:00 on weekdays: " . $row['average'] . PHP_EOL;
}

$sql = 'SELECT distinct hour(date_created) as peak_hour, count(*) as total_conversations
        FROM conversations
        WHERE method = "CHAT"
        GROUP BY hour(date_created)
        ORDER BY count(*) DESC;';

if ($result = $db->query($sql)) {
    echo "Peak hour(s) of chat: " . PHP_EOL;
    echo "\tHour\t# conversations" . PHP_EOL;
    while ($row = $result->fetch_assoc()) {
        echo "\t" . $row['peak_hour'] . "\t" . $row['total_conversations'] . PHP_EOL;
    }
}



echo "==== SITE METRICS ====" . PHP_EOL;

$sql = 'SELECT count(DISTINCT conversations_id) as total
    FROM assignments
    WHERE answering_site = "http://www.unl.edu/gradstudies/";';

if ($result = $db->query($sql)) {
    $row = $result->fetch_assoc();
    echo "Grad Studies: Chats started  " . $row['total'] . PHP_EOL;
}

$sql = 'SELECT count(DISTINCT conversations_id) as total
        FROM assignments
        WHERE answering_site = "http://www.unl.edu/gradstudies/"
              AND status = "COMPLETED";';

if ($result = $db->query($sql)) {
    $row = $result->fetch_assoc();
    echo "Grad Studies: Chats actually answered " . $row['total'] . PHP_EOL;
}

$sql = 'SELECT count(*) as total, SEC_TO_TIME(AVG(DATEDIFF(TIME_TO_SEC(conversations.date_closed), TIME_TO_SEC(conversations.date_created)))) as average
        FROM assignments
        JOIN conversations ON (assignments.conversations_id = conversations.id)
        WHERE answering_site = "http://www.unl.edu/gradstudies/";';

if ($result = $db->query($sql)) {
    $row = $result->fetch_assoc();
    echo "Average length of conversation " . $row['average'] . PHP_EOL;
}


//SQL to get the total amount of time that users are conversing
$sql = 'SELECT assignments.users_id as assignment_user, (sum(TIMEDIFF(assignments.date_finished, assignments.date_accepted))) as total
        FROM conversations
        JOIN assignments ON (assignments.conversations_id = conversations.id)
        WHERE answering_site = "http://www.unl.edu/gradstudies/"
              AND assignments.date_finished IS NOT NULL
              AND assignments.date_accepted IS NOT NULL
              AND assignments.date_accepted >= "2012-12-07 12:00:03"
        GROUP BY assignments.users_id;';


$statistics = new \UNL\VisitorChat\User\Status\Statistics();

//Figure specific user stats
$users = array();
if ($result = $db->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $users[$row['assignment_user']]['total_time_conversing'] = (int)$row['total'];
        $stats = $statistics->getStats(array($row['assignment_user']));
        $users[$row['assignment_user']]['total_time_available'] = $stats['total_time_online'];
        $users[$row['assignment_user']]['percent_conversing'] = ($users[$row['assignment_user']]['total_time_conversing'] / $users[$row['assignment_user']]['total_time_available']) * 100;
    }
}

//Figure average stats
$total_time_online = 0;
$total_time_conversing = 0;

foreach ($users as $user) {
    $total_time_online += $user['total_time_available'];
    $total_time_conversing += $user['total_time_conversing'];
}

$average_percent_conversing = round(($total_time_conversing/$total_time_online)*100, 2);
echo "Average Percent of Time Online Spent Conversing: " . $average_percent_conversing . "%" . PHP_EOL;