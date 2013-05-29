<?php
namespace UNL\VisitorChat\BlockedIP;

class Edit extends Record
{
    public function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::requireOperatorLogin();

        if (isset($options['id']) && $object = self::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        } else if (isset($options['ip_address'])) {
            $this->ip_address = $options['ip_address'];
        }
        
        if (!$this->id) {
            $this->initializeDefaultDates();
        }
    }

    function handlePost($post = array())
    {
        if (!isset($post['ip_address'])) {
            throw new \Exception('You must specify an IP address', 400);
        }
        
        if (!filter_var($post['ip_address'], FILTER_VALIDATE_IP)) {
            throw new \Exception('You must enter a valid IP address', 400);
        }
        
        if (!isset($post['time'])) {
            throw new \Exception('You must enter a valid amount of time', 400);
        }
        
        if (!isset($post['time_units'])) {
            throw new \Exception('You must enter a valid time unit', 400);
        }

        if (!isset($post['status'])) {
            throw new \Exception('You must enter a status', 400);
        }

        $blocks = RecordList::getAllForIP($this->ip_address);
        
        if (!$this->id && $count = $blocks->count()) {
            throw new \Exception('This IP address is already blocked.', 400);
        }
        
        $post['users_id'] = \UNL\VisitorChat\User\Service::getCurrentUser()->id;

        if ($post['time_units'] == 'hours') {
            $end = strtotime($this->block_start) + ($post['time'] * 3600);
        } else {
            $end = strtotime($this->block_start) + ($post['time'] * 86400);
        }

        $post['block_end'] = \UNL\VisitorChat\Controller::epochToDateTime($end);
        
        $this->synchronizeWithArray($post);
        $this->save();

        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("success", true));
    }
}