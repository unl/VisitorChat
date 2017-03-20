<?php
$data = array();
$data['result'] = 'error';
$data['message'] = $context->getMessage();
$data['code']    = $context->getCode();
echo json_encode($data);
