<?php

class SmartFeedsUpdate extends MysqlEntity {

    const SYNC_TYPE_NAME = 'smartfeedsupdate';

    protected $TABLE_NAME = 'plugin_smartfeedsupdate';
    protected $CLASS_NAME = 'SmartFeedsUpdate';
    public $slots_default = array( 5, 10, 20, 30, 60, 120, 480, 1440 );
    protected $frequencies = array();

    protected
        $slot,
        $feeds,
        $nextupdate;
    protected $object_fields =
        array(
            'slot'       => 'key',
            'feeds'      => 'longstring',
            'nextupdate' => 'integer'
        );

    public function setSlot( $slot ) {
        $this->slot = $slot;
    }

    public function getSlot() {
        return $this->slot;
    }

    public function setFeeds( $feeds ) {
        $this->feeds = $feeds;
    }

    public function getFeeds() {
        return $this->feeds;
    }

    public function setNextUpdate( $next_update ) {
        $this->nextupdate = $next_update;
    }

    public function getNextUpdate() {
        return $this->nextupdate;
    }

    protected function updateNextUpdate() {
        $this->setNextUpdate( $_SERVER['REQUEST_TIME'] + $this->slot * 60 );
    }

    public function save( $id_field = 'slot' ) {
        parent::save( $id_field );
    }

    protected function getFeedIdListJson( array $feed_ids ) {
        return json_encode( array_values( $feed_ids ) );
    }

    protected function getFeedIdListString() {
        $feed_id_list = json_decode( $this->getFeeds() );

        if( ! empty( $feed_id_list ) ) {
            return implode( ', ', $feed_id_list );
        }

        return false;
    }

    public function getFeedIdArray() {
        return json_decode( $this->getFeeds() );
    }

    protected function saveAllFrequencies() {

        $query = 'UPDATE ' . MYSQL_PREFIX . $this->TABLE_NAME
                . ' SET `feeds`= CASE `slot` ';

        foreach( $this->slots_default as $slot_nb ) {
            $values =
                isset( $this->frequencies[$slot_nb] ) ?
                $this->frequencies[$slot_nb]
                :
                array();

            $query .=
                'WHEN ' . $slot_nb . ' ' .
                'THEN "' . $this->getFeedIdListJson( $values ) . '" ';
        }

        $query .= 'END;';

        $result = $this->dbconnector->connection->query( $query );
        if( $result === false ) {
            throw new Exception(mysqli_error());
        }

        return $result;
    }

    protected function saveFrequency() {
        $query = 'UPDATE ' . MYSQL_PREFIX . $this->TABLE_NAME . ' ' .
                 'SET `feeds`="' . $this->getFeeds() . '" ' .
                 'WHERE `slot`=' . $this->getSlot() . ';';

        $result = $this->dbconnector->connection->query( $query );
        if( $result === false ) {
            throw new Exception(mysqli_error());
        }

    }

    protected function getAllFeeds() {
        $feeds_manager = new Feed();

        return $feeds_manager->loadAllOnlyColumn( 'id, name', null );
    }

    public function updateFeed( $feed_id, $previous_slot, $new_slot ) {
        $feed_id = (int)$feed_id;
        $slot = $this->load( array( 'slot' => $previous_slot ) );
        $slot->removeFeedFromSlot( $feed_id );

        $slot = $this->load( array( 'slot' => $new_slot ) );
        $slot->addFeedToSlot( $feed_id );
    }

    protected function addFeedToSlot( $feed_id ) {
        $feeds_ids = array();
        $feeds_ids = $this->getFeedIdArray();

        $feeds_ids[] = $feed_id;
        asort( $feeds_ids );
        
        $this->setFeeds( $this->getFeedIdListJson( $feeds_ids ) );
        $this->saveFrequency();
    }

    protected function removeFeedFromSlot( $feed_id ) {
        $feeds_ids = array();
        $feeds_ids = $this->getFeedIdArray();

        $feed_id_position = array_search( $feed_id, $feeds_ids );
        if( ! is_numeric( $feed_id_position ) ) {
            echo 'The feed ' . $feed_id . ' was not in the slot ' . $this->getSlot() . '.';
            return false;
        }

        unset( $feeds_ids[$feed_id_position] );

        $this->setFeeds( $this->getFeedIdListJson( $feeds_ids ) );
        $this->saveFrequency();
    }

    public function getNiceSlotIdString( $slot_id ) {
        $day = 60*24;
        $hour = 60;

        if( $slot_id < $hour ) {
            return $slot_id . ' ' . _t( $this->singularOrPluralTranslation( $slot_id, 'SMARTFEEDSUPDATE_MN' ) );
        } elseif( $slot_id < $day ) {
            $nb = $slot_id/60;
            return $nb . ' ' . _t( $this->singularOrPluralTranslation( $nb, 'SMARTFEEDSUPDATE_HOUR' ) );
        } else {
            $nb = $slot_id/60/24;
            return $nb . ' ' . _t( $this->singularOrPluralTranslation( $nb, 'SMARTFEEDSUPDATE_DAY' ) );
        }
    }

    protected function singularOrPluralTranslation( $nb, $str ) {
        if( $nb == 1 ) {
            return $str;
        }

        return $str . 'S';
    }

    public function install() {

        $this->dbconnector->connection->query(
            'CREATE TABLE IF NOT EXISTS `' . MYSQL_PREFIX . $this->TABLE_NAME . '` (
                `slot` int(11) NOT NULL,
                `feeds` varchar(255),
                `nextupdate` int(10) NOT NULL DEFAULT 0,
                PRIMARY KEY (`slot`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;'
        );

        $values = "VALUES";
        $limit = count( $this->slots_default ) - 1;
        foreach( $this->slots_default as $key => $slot ) {
            $values .= "($slot)";
            
            if( $key < $limit ) {
                $values .= ',';
            }
        }

        $this->dbconnector->connection->query('
            INSERT INTO `' . MYSQL_PREFIX . $this->TABLE_NAME . '`
                ( slot )
            ' . $values . '
        ;');

        $configurationManager = new Configuration();
        $configurationManager->put( 'synchronisationType', self::SYNC_TYPE_NAME );
        require_once( __DIR__ . "/SmartFeedsUpdateFrequencies.php" );
        $smart_update = new SmartFeedsUpdateFrequencies();
        $smart_update->updateFrequencies();
    }

    public function uninstall() {
        $this->destroy();

        $configurationManager = new Configuration();
        $configurationManager->put( 'synchronisationType', 'auto' );
    }

}
