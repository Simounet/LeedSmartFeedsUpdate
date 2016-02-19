<?php

class SmartFeedsUpdateFrequencies extends SmartFeedsUpdate {

    const EVENTS_LIMIT = 10;
    protected $feeds = array();
    protected $feeds_frequency = array();

    public function updateFrequencies() {
        $this->feeds = $this->getAllFeeds();
        $this->setFrequencies();
        $total_saved_eq_total_feeds = $this->isTotalSavedEqualseTotalFeedsNumber();
        if( $total_saved_eq_total_feeds !== true ) {
            echo _t( 'SMARTFEEDSUPDATE_ERROR_FEEDS_NUMBER_SAVED' ) . ' ' . $total_saved_eq_total_feeds;
        } elseif( ! $this->saveAllFrequencies() ) {
            echo _t( 'SMARTFEEDSUPDATE_NO_FREQUENCIES_SAVED' );
        }
    }

    public function addFeedFrequency( $feed ) {
        $this->feeds = array( $feed );
        $this->setFrequencies();
        $slot_id = key( $this->frequencies );
        $slot = $this->load( array( 'slot' => $slot_id ) );
        $slot->addFeedToSlot( reset( $this->frequencies[$slot_id] ) );
    }

    public function removeFeedFrequency( $feed_id ) {
        $smart = new SmartFeedsUpdate();
        $slots = $smart->populate();
        foreach( $slots as $slot_i => $slot ) {
            if( in_array( $feed_id, $slot->getFeedIdArray() ) ) {
                break;
            }
        }
        $slots[$slot_i]->removeFeedFromSlot( $feed_id );
    }

    protected function isTotalSavedEqualseTotalFeedsNumber() {
        $feeds_total_count = count( $this->feeds );
        $smart_feeds_to_save_count = count( $this->frequencies, COUNT_RECURSIVE ) - count( $this->frequencies );
        return $feeds_total_count === $smart_feeds_to_save_count ?
            true
            :
            "db: $feeds_total_count - smart: $smart_feeds_to_save_count";
    }

    protected function getFeedLastPubdates( Feed $feed ) {
        $events = $feed->getEvents( 0, self::EVENTS_LIMIT, 'pubdate DESC', 'pubdate' );

        if( self::EVENTS_LIMIT <= count( $events ) ) {
            $dates = array();
            foreach( $events as $event ) {
                $dates[] = $event->getPubdate();
            }

            return $dates;
        }

        return false;
    }

    protected function setFeedsFrequency( $feeds ) {
        foreach( $feeds as $feed ) {
            if( $feed_last_pubdates = $this->getFeedLastPubdates( $feed ) ) {
                $this->feeds_frequency[$feed->getId()] = $this->getPubdatesAverage( $feed_last_pubdates );
            } else {
                $this->feeds_frequency[$feed->getId()] = end( $this->slots_default );
            }
        }
    }

    protected function setFrequencies() {
        $this->setFeedsFrequency( $this->feeds );
        asort( $this->feeds_frequency );

        // Loop on each feed
        foreach( $this->feeds_frequency as $feed_id => $feed_frequency ) {

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
                    $this->frequencies[$slot_limit][] = $feed_id;
                    break;
                }

            }

        }
    }

    /**
     * Tools
     */

    protected function getPubdatesAverage( array $dates ){
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
