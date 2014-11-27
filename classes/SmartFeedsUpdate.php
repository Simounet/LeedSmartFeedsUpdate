<?php

class SmartFeedsUpdate extends MysqlEntity {

    public $sync_type_name = 'smartfeedsupdate';

    protected $TABLE_NAME = 'plugin_smartfeedsupdate';
    protected $slots_default = array( 5, 10, 20, 30, 60, 120, 480, 1440 );

    protected
        $slot,
        $feeds,
        $nextupdate;
    protected $object_fields =
        array(
            'slot'       => 'key',
            'feeds'      => 'string',
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

    protected function setFeedIdList( array $feed_ids ) {
        return json_encode( $feed_ids );
    }

    protected function getFeedIdList() {
        $feed_id_list = json_decode( $this->getFeeds() );

        if( ! empty( $feed_id_list ) ) {
            return implode( ', ', $feed_id_list );
        }

        return false;
    }

    protected function saveFrequencies( $frequencies ) {

        $query = 'UPDATE ' . MYSQL_PREFIX . $this->TABLE_NAME
                . ' SET `feeds`= CASE `slot` ';

        foreach( $this->slots_default as $slot_nb ) {
            $values =
                isset( $frequencies[$slot_nb] ) ?
                $frequencies[$slot_nb]
                :
                array();

            $query .=
                'WHEN ' . $slot_nb . ' ' .
                'THEN "' . $this->setFeedIdList( $values ) . '" ';
        }

        $query .= 'END;';

        $result = mysql_query( $query );
        if( $result === false ) {
            throw new Exception(mysql_error());
        }

        return $result;
    }

    protected function getAllFeeds() {
        $feeds_manager = new Feed();

        return $feeds_manager->loadAllOnlyColumn( 'id', null );
    }

    public function install() {

        mysql_query(
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

        mysql_query('
            INSERT INTO `' . MYSQL_PREFIX . $this->TABLE_NAME . '`
                ( slot )
            ' . $values . '
        ;');
    }

    public function uninstall() {
        mysql_query( 'DROP TABLE ' . MYSQL_PREFIX . $this->TABLE_NAME );
    }

}
