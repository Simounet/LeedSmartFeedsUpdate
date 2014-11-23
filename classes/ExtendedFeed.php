<?php

class ExtendedFeed extends Feed {

    public function getFeedsByIdList( $id_list ){
        $columns = '*';
        $whereClause = 'WHERE `id`  IN (' . $id_list . ')';
        $query = 
            'SELECT '.$columns.' '.
            'FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` '.
            $whereClause.';';

        $results = $this->customQuery( $query );

        $objects = array();
        if( $results != false ) {
            while( $item = mysql_fetch_assoc( $results ) ) {
                $object = new self;

                foreach( $object->getObject_fields() as $field => $type ) {
                    $setter = 'set'.ucFirst($field);
                    if( isset( $item[$field] ) ) {
                        $object->$setter( $item[$field] );
                    }
                }

                $objects[] = $object;
                unset($object);
            }
        }

        return $objects;
    }

    protected function setLastSyncInError( $last_sync_in_error ) {
        $this->lastSyncInError = $last_sync_in_error;
    }

}
