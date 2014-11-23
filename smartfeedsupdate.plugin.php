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

Plugin::addHook( "setting_post_synchronisation_options", "add_synchro_option" );


?>
