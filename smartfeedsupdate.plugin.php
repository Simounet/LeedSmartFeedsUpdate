<?php

/*
@name LeedSmartFeedUpdate
@author Simounet <contact@simounet.net>
@link http://www.simounet.net
@licence MIT
@version 1.0.0
@description Better feeds update handler
*/

require_once( __DIR__ . "/classes/SmartFeedsUpdate.php" );

function add_synchro_option( $synchronisationType ) {
    $identifier = 'smartupdate';

    $checked = false;
    if( $synchronisationType == $identifier ) {
        $checked = 'checked="checked"';
    }

    echo
        '<p>' .
            '<input
                type="radio"
                id="synchronisationType' . $identifier . '"
                name="synchronisationType"
                value="' . $identifier . '" ' .
                $checked .
            '> '.
            '<label
                for="synchronisationType' . $identifier .'">' .
                '<strong>' . _t('SMARTFEEDUPDATE_SMART') . ' :</strong>' .
            '</label> ' . _t('SMARTFEEDUPDATE_SMART_DESC') .
        '</p>';
}

function get_feeds( &$synchronisation_custom, &$commandLine, $configurationManager, $start ) {
    $synchronisation_custom['no_normal_synchronize'] = true;

    require_once( __DIR__ . "/classes/SmartFeedsUpdateFeeds.php" );
    require_once( __DIR__ . "/classes/ExtendedFeed.php" );

    $update_feeds = new SmartFeedsUpdateFeeds();
    $update_feeds->updateFeeds( $commandLine, $configurationManager, $start );
}

Plugin::addHook( "setting_post_synchronisation_options", "add_synchro_option" );
Plugin::addHook( "action_before_synchronisationtype", "get_feeds" );


?>
