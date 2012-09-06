<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

$answering_site = false;
if (isset($_SERVER['argv'], $_SERVER['argv'][1])) {
    $answering_site = $_SERVER['argv'][1];
}

if ($answering_site == 'help') {
    echo 'Usage: php conversationStats.php [http://baseurl.unl.edu/] [days]' . PHP_EOL;
    exit(1);
}

$days = 0;
$days_string = "Deployment";
if (isset($_SERVER['argv'], $_SERVER['argv'][2])) {
    $days = $_SERVER['argv'][2];
    $days_string = date("Y-m-d H:i:s", time() - ($days * 86400));
}

$startDate = time() - ($days * 86400);

$assignments = array();
$assignments['completed'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($answering_site, $days, 'COMPLETED');
$assignments['expired'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($answering_site, $days, 'EXPIRED');
$assignments['rejected'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($answering_site, $days, 'REJECTED');
$assignments['failed'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($answering_site, $days, 'FAILED');
$assignments['left'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($answering_site, $days, 'LEFT');

$conversations = array();
$conversations['answered']   = \UNL\VisitorChat\Conversation\RecordList::getCompletedConversationsForSite($answering_site, $days, 'ANSWERED');
$conversations['unanswered'] =  \UNL\VisitorChat\Conversation\RecordList::getCompletedConversationsForSite($answering_site, $days, 'UNANSWERED');

$totalAssignments = 0;
$totalConversations = 0;

foreach ($assignments as $type=>$list) {
    $totalAssignments += $list->count();
}

foreach ($conversations as $type=>$list) {
    $totalConversations += $list->count();
}

$site_title = "All Sites";
if ($answering_site) {
    $site_title = $answering_site;
}

echo "----------------------------------------------------------------------" . PHP_EOL;
echo "VisitorChat Report" . PHP_EOL;
echo "For site: " . $site_title . PHP_EOL;
echo "FROM " . $days_string . " TO " . date("Y-m-d H:i:s") . PHP_EOL; 
echo "-------  CONVERSATIONS  --------" . PHP_EOL;
echo "Total Conversations: " . $totalConversations . PHP_EOL;
foreach ($conversations as $type=>$list) {
    if ($list->count() == 0) {
        continue;
    }
    
    echo $type . ": " . $list->count() . "(" . round(($list->count()/$totalConversations)*100) . "%)" . PHP_EOL;
}

echo "-------  ASSIGNMENTS  --------" . PHP_EOL;
echo "Total Assignments: " . $totalAssignments . PHP_EOL;
foreach ($assignments as $type=>$list) {
    if ($list->count() == 0) {
        continue;
    }
    
    echo $type . ": " . $list->count() . "(" . round(($list->count()/$totalAssignments)*100) . "%)" . PHP_EOL;
}
echo "----------------------------------------------------------------------" . PHP_EOL;