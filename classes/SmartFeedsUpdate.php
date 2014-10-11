<?php

class SmartFeedsUpdate {

    protected $table_name = 'plugin_smartfeedsupdate';
    protected $slots = array( 5, 30, 60, 480, 9999 );

    public function install() {

        mysql_query(
            'CREATE TABLE IF NOT EXISTS `' . MYSQL_PREFIX . $this->table_name . '` (
                `id` int(11) NOT NULL,
                `feeds` varchar(255),
                `nextupdate` int(10) NOT NULL DEFAULT 0
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;'
        );

        $values = "VALUES";
        $limit = count( $this->slots ) - 1;
        foreach( $this->slots as $key => $slot ) {
            $values .= "($slot)";
            
            if( $key < $limit ) {
                $values .= ',';
            }
        }

        mysql_query('
            INSERT INTO `' . MYSQL_PREFIX . $this->table_name . '`
                ( id )
            ' . $values . '
        ;');
    }

    public function uninstall() {
        mysql_query( 'DROP TABLE ' . MYSQL_PREFIX . $this->table_name );
    }

}
