<?php 
use UNL\VisitorChat;

class DBHelper
{
    function dropAllTables()
    {
        $sql = "SET FOREIGN_KEY_CHECKS=0;
                 DROP TABLE IF EXISTS users;
                 DROP TABLE IF EXISTS conversations;
                 DROP TABLE IF EXISTS invitations;
                 DROP TABLE IF EXISTS messages;
                 DROP TABLE IF EXISTS assignments;
                 DROP TABLE IF EXISTS emails;
                 SET FOREIGN_KEY_CHECKS=1;";
        
        return self::query($sql);
    }
    
    function installDB($filename)
    {
        self::dropAllTables();
        
        $sql = file_get_contents(dirname(dirname(__FILE__)) . "/Data/" . $filename);
        
        return self::query($sql);
    }
    
    function query($sql)
    {
        $db = \UNL\VisitorChat\Controller::getDB();

        if ($db->multi_query($sql)) {
            do {
                //nothing
            } while ($db->next_result());
        }

        //Check if there was an error
        if ($db->errno) {
            echo "DBHelper::query - ERROR: Stopped while retrieving result : ".$db->error;
        }
        
        return true;
    }
}