<?php

class SmartFeedsUpdateFrequencies extends SmartFeedsUpdate {

    protected $events_limit = 10;

    public function updateFrequencies() {
        // Get all feeds
        $feeds = $this->getAllFeeds();

        foreach( $feeds as $feed ) {

            $feed_frequencies = $this->getLastFrequencies( $feed );

            if( $feed_frequencies ) {
                $feeds_frequencies[$feed->getId()] = $this->eventsIntervalAverage( $feed_frequencies );
            }

        }

        $frequencies = $this->sortFrequenciesArray( $feeds_frequencies );
        if( ! $this->saveFrequencies( $frequencies ) ) {
            echo _t( 'SMARTFEEDUPDATE_NO_FREQUENCIES_SAVED' );
        }
    }

    protected function getLastFrequencies( Feed $feed ) {
        $events = $feed->getEvents( 0, $this->events_limit, 'pubdate DESC', 'pubdate' );

        if( $this->events_limit <= count( $events ) ) {
            $dates = array();
            foreach( $events as $event ) {
                $dates[] = $event->getPubdate();
            }

            return $dates;
        }

        return false;
    }


    protected function sortFrequenciesArray( $feeds_frequencies ) {
        asort( $feeds_frequencies );

        // Loop on each feed
        foreach( $feeds_frequencies as $feed_id => $feed_frequency ) {

            // Check for each slot
            foreach( $this->slots_default as $slot_limit ) {

                if( $feed_frequency != 0
                AND $feed_frequency <=  $slot_limit ) {
                    $frequencies[$slot_limit][] = $feed_id;
                    break;
                }

            }

        }

        return $frequencies;

    }

    /**
     * Tools
     */

    protected function eventsIntervalAverage( array $dates ){
        $intervals = array();

        foreach( $dates as $key => $date ) {

            if( isset( $dates[$key+1] ) ) {
                $intervals[] = $date - $dates[$key+1];
            }
     
        }

        $average = array_sum( $intervals ) / count( $intervals );
        $min = min( $intervals );
        $coeff_min = 2;
        $coeff_average = 1;

        $result =
            $average === 0 ?
            60*60*24
            :
            ( $coeff_average*$average + $coeff_min*$min ) / ( $coeff_average + $coeff_min );

        return $result / 60;
    }

}
