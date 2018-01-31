<?php

/*
@name LeedSmartFeedsUpdate
@author Simounet <contact@simounet.net>
@link http://www.simounet.net
@licence MIT
@version 1.0.0
@description Better feeds update handler
*/

require_once( __DIR__ . "/classes/SmartFeedsUpdate.php" );

function add_synchro_option( $synchronisationType ) {
    $identifier = SmartFeedsUpdate::SYNC_TYPE_NAME;

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
                '<strong>' . _t('SMARTFEEDSUPDATE_SMART') . ' :</strong>' .
            '</label> ' . _t('SMARTFEEDSUPDATE_SMART_DESC') .
        '</p>';
}

function get_feeds( &$synchronisation_custom, &$synchronisationType, &$commandLine, $configurationManager, $start ) {
    require_once( __DIR__ . "/classes/SmartFeedsUpdateFeeds.php" );
    $update_feeds = new SmartFeedsUpdateFeeds();

    if( $synchronisationType == $update_feeds::SYNC_TYPE_NAME ) {
        $synchronisation_custom['no_normal_synchronize'] = true;

        require_once( __DIR__ . "/classes/ExtendedFeed.php" );
        $update_feeds->updateFeeds( $commandLine, $configurationManager, $start );
    }
}

function setSmartFeedsUpdateSettingsLink( &$my_user ) {
    echo '<li><a class="toggle" href="#smartFeedsUpdateSettingsBlock">'._t('SMARTFEEDSUPDATE_SETTINGS').'</a></li>';
}

function setSmartFeedsUpdateSettingsBlock( &$my_user, &$feeds ) {
    Plugin::addJs( '/js/script.js' );
    $update_feeds = new SmartFeedsUpdate();
    $slots = $update_feeds->populate();

    echo '<section class="smartFeedsUpdateSettingsBlock" style="display:none;">';
    echo '<form id="smartupdate-form" action="/action.php" method="post">';
    echo '<h2>' . _t( 'SMARTFEEDSUPDATE_SETTINGS' ) . '</h2>';
    echo '<p>' . _t( 'SMARTFEEDSUPDATE_FREQUENCY_NOTIFICATION' ) . '</p>';
    foreach( $slots as $slot ) {
        $slot_id = $slot->getSlot();
        $slot_id_nice = $update_feeds->getNiceSlotIdString( $slot_id );
        echo '<section class="addBloc">';
        echo '<h3>' . $slot_id_nice . ' (' . date('H:i m/d/Y', $slot->getNextUpdate()) . ')</h3>';
        echo '<ul class="nochip">';
        // [facto] - Not very efficient...
        // I have to build a request with feeds' names and id
        // Then merge infos avoiding me the feeds foreachs
        $feed_id_array = $slot->getFeedIdArray();
        if( is_array( $feed_id_array ) ) {
            foreach( $feed_id_array as $id ) {
                foreach( $feeds as $feed ) {
                    $feed_id = $feed->getId();
                    if( $id == $feed_id ) {
                        echo '<li>';
                        echo '<span class="feedTitle">' . Functions::truncate( $feed->getName(), 30 ) . '</span>';
                        echo '<select class="js-smart-feeds-update-select" name="smartupdates" data-feed-id="' . $feed_id . '">';

                        foreach( $update_feeds->slots_default as $slot ) {
                            $slot_nice = $update_feeds->getNiceSlotIdString( $slot );
                            $selected = $slot == $slot_id ? ' selected' : false;
                            echo '<option value="' . $slot . '"' . $selected . '>' . $slot_nice . '</option>';
                        }
                        echo '</select>';
                        echo '</li>';
                    }
                }
            }
        }
        echo '</ul>';
        echo '</section>';
    }
    echo '</form>';
    echo '</section>';
}

function setSmartFeedsUpdateFeedSlot( &$_, $myUser ) {
    if(
           ! isset( $_['feed-id'] )
        || ! isset( $_['previous-slot'] )
        || ! isset( $_['new-slot'] )
    ) {
        return false;
    }

    $update_feeds = new SmartFeedsUpdate();
    $update_feeds->updateFeed( $_['feed-id'], $_['previous-slot'], $_['new-slot'] );

    //header('location: ./settings.php');
    header('Location: '.$_SERVER['PHP_SELF']);
    die;
}

function smartFeedsUpdateAddFeed( &$newFeed ) {
    require_once( __DIR__ . "/classes/SmartFeedsUpdateFrequencies.php" );
    $smart_update = new SmartFeedsUpdateFrequencies();
    $smart_update->addFeedFrequency( $newFeed );
}

function smartFeedsUpdateRemoveFeed( $feedId ) {
    require_once( __DIR__ . "/classes/SmartFeedsUpdateFrequencies.php" );
    $smart_update = new SmartFeedsUpdateFrequencies();
    $smart_update->removeFeedFrequency( $feedId );
}

Plugin::addHook( "setting_post_synchronisation_options", "add_synchro_option" );
Plugin::addHook( "action_before_synchronisationtype", "get_feeds" );
Plugin::addHook( "setting_post_link", "setSmartFeedsUpdateSettingsLink" );
Plugin::addHook( "setting_post_section", "setSmartFeedsUpdateSettingsBlock" );
Plugin::addHook( "action_post_case", "setSmartFeedsUpdateFeedSlot" );
Plugin::addHook( "action_after_addFeed", "smartFeedsUpdateAddFeed" );
Plugin::addHook( "action_after_removeFeed", "smartFeedsUpdateRemoveFeed" );

?>
