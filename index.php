<?php

require_once("../../common.php");
require_once( __DIR__ . "/classes/SmartFeedsUpdate.php" );

$action = 'default';
if( isset( $_GET['action'] ) && $_GET['action'] != '' ) {
    $action = $_GET['action'];
}

if( isset( $argv[1] ) && $argv[1] != '' ) {
    $action = $argv[1];
}

switch( $action ) {
    case 'update-frequencies':
        require_once( __DIR__ . "/classes/SmartFeedsUpdateFrequencies.php" );
        $smart_update = new SmartFeedsUpdateFrequencies();
        $smart_update->updateFrequencies();
        break;
    case 'default':
        echo _t( 'SMARTFEEDSUPDATE_NO_ACTION_DETECTED' );
        break;
}

?>
