<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

define( 'NOANSWER', 1 );

class ANSWER {
    protected static $instance;
    protected static $answer;
    protected static $ajax;
    protected static $own;

    public static function getInstance() { 
        if ( self::$instance === null) 
        { 
            self::$instance = new ANSWER;
        } 
        return self::$instance;
    }
    public  function __construct() {
        self::$ajax = true;
        self::$answer = array( 'success'=> true, 'err' => 1, 'result' => 0, 'temp' => '' );
    }
    private function __clone() {
    }
    private function __wakeup() {
    }
    public static function set( $name, $value )
    {
        self::$answer[ $name ] = $value;
    }
    public static function get( $name )
    {
        return self::$answer[ $name ];
    }
    public static function result( $value )
    {
        self::$answer['result'] = $value;
    }
    public static function isresult( $value )
    {
        return  isset( self::$answer['result'][ $value ] );
    }
    public static function unsetresult( $value )
    {
        unset( self::$answer['result'][ $value ] );
    }
    public static function resultset( $field, $value )
    {
        if ( !self::$answer['result'] )
            self::$answer['result'] = array();
        self::$answer['result'][ $field ] = $value;
    }
    public static function resultget( $field )
    {
        return self::$answer['result'][ $field ];
    }
    public static function is_success( $demo = false )
    {
        if ( $demo && defined( 'DEMO' ))
            api_error('This feature is disabled in the demo-version.');
        return self::$answer['success'];
    }
    public static function success( $state )
    {
        self::$answer['success'] = $state;
    }
    public static function answer()
    {
        if ( self::$ajax )
            print json_encode( self::$answer );
    }
    public static function isajax( $noajax = false )
    {
        if ( $noajax )
            self::$ajax = false;
        return self::$ajax;
    }
    public static function is_own()
    {
        return self::$own;
    }
    public static function is_access( $action = A_ROOT, $idtable = 0, $iditem = 0 )
    {
        $acc = GS::access( $action, $idtable, $iditem ); 
        if ( !$acc )
            return api_error( 'iarights' );
        self::$own = !( $acc & 1 );
        return true;
    }    
}

ANSWER::getInstance();
