<?php
interface SimpleCAS_ProxyGranting_Storage
{
    function saveIOU($iou);
    function getProxyGrantingTicket($iou);
}
?>