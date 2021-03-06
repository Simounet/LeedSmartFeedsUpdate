<?php

class SmartFeedsUpdateFeeds extends SmartFeedsUpdate {

    public function updateFeeds( $commandLine, $configurationManager, $start ) {
        $frequencies = $this->getUpdateNeededFrequencies();
        if( ! $frequencies ) {
            if( ! $commandLine ) {
                echo _t( 'SMARTFEEDSUPDATE_NO_FEED_TO_UPDATE' );
            }
            return false;
        }

        $feedManager = new ExtendedFeed();

        $feeds = array();
        foreach( $frequencies as $frequency ) {

            if( $feed_id_list = $frequency->getFeedIdListString() ) {

                $feeds = $feedManager->getFeedsByIdList( $feed_id_list );
                $feedManager->synchronize($feeds, self::SYNC_TYPE_NAME, $commandLine, $configurationManager, $start);

            }

            $frequency->updateNextUpdate();
            $frequency->save();

        }
    }

    protected function getUpdateNeededFrequencies() {
        $frequencies = array();

        // [todo] - Add last feed sync on error on this update
        $get_frequencies_query = 
            'SELECT * ' .
            'FROM `' . MYSQL_PREFIX . $this->TABLE_NAME . '` ' .
            'WHERE `nextupdate` < ' . $_SERVER['REQUEST_TIME'] . ' ' .
            'OR `nextupdate` = 0' .
            ';';

        $frequencies_result = $this->dbconnector->connection->query( $get_frequencies_query );
        while( $row = $frequencies_result->fetch_assoc() ) {

            $frequency = new self;
            $frequency->setSlot( $row['slot'] );
            $frequency->setFeeds( $row['feeds'] );
            $frequency->setNextUpdate( $row['nextupdate'] );

            $frequencies[] = $frequency;
            unset( $frequency );
        }

        return $frequencies;
    }
}
