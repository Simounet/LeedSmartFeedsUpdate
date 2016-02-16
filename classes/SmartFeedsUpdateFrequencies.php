<?php

class SmartFeedsUpdateFrequencies extends SmartFeedsUpdate {

    protected $events_limit = 10;

    public function updateFrequencies() {
        $feeds = $this->getAllFeeds();
        $last_slot_id = end( $this->slots_default );

        foreach( $feeds as $feed ) {

            $feed_frequencies = $this->getLastFrequencies( $feed );

            if( $feed_frequencies ) {
                $feeds_frequencies[$feed->getId()] = $this->eventsIntervalAverage( $feed_frequencies );
            } else {
                $feeds_frequencies[$feed->getId()] = $last_slot_id;
            }

        }

        $frequencies = $this->sortFrequenciesArray( $feeds_frequencies );
        if( ! $this->saveAllFrequencies( $frequencies ) ) {
            echo _t( 'SMARTFEEDSUPDATE_NO_FREQUENCIES_SAVED' );
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

            $slots_last_i = count( $this->slots_default ) - 1;
            // Check for each slot
            foreach( $this->slots_default as $slot_i => $slot_limit ) {

                if(
                    (
                        $feed_frequency != 0
                        && $feed_frequency <=  $slot_limit
                    )
                    || $slot_i === $slots_last_i
                ) {
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
