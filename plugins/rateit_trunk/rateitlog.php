<?php
class RateItLog extends QueryRecord
{
    public static function default_fields()
    {
        return array(
            'id' => 0,
            'post_id' => 0,
            'rating' => 0,
            'ip' => 0,
            'timestamp' => date( 'Y-m-d H:i:s' ),
        );
    }

    /**
     * constructer
     *
     * @access public
     */
    public function __construct( $paramarray= array() )
    {
        if ( isset( $paramarray['ip']  ) ) {
            $paramarray['ip']= sprintf( '%u', ip2long( $paramarray['ip'] ) );
        }

        // Defaults
        $this->fields= array_merge( self::default_fields(), $this->fields );
        parent::__construct( $paramarray );
        $this->exclude_fields( 'id' );
    }

    /**
     * insert
     *
     * @access public
     * @return boolean
     */
    public function insert()
    {
        $result = parent::insertRecord( DB::table( 'rateit_log' ) );
        $this->newfields['id'] = DB::last_insert_id(); // Make sure the id is set in the comment object to match the row id
        $this->fields = array_merge($this->fields, $this->newfields);
        $this->newfields = array();

        return $result;
    }

    public function __set( $name, $value )
    {
        switch( $name ) {
        case 'ip':
            $this->ip= sprintf( '%u', ip2long( $value ) );
            break;
        }
        return parent::__set( $name, $value );
    }

    public function __get( $name )
    {
        switch($name) {
        case 'ip':
            $out = long2ip( parent::__get( $name ) );
            break;
        default:
            $out = parent::__get( $name );
            break;
        }
        return $out;
    }
}
?>