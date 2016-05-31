<?php
require_once( 'api/HarvestAPI.php' );
$api = new HarvestAPI();
$api->setUser("motilalsoni@gmail.com");
$api->setPassword("motilal@soni");
$api->setAccount("motilalsoni");
spl_autoload_register(array('HarvestAPI', 'autoload'));
$api->setRetryMode(HarvestAPI::RETRY);
$api->setSSL(true);
?>