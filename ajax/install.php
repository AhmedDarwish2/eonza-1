<?php
/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/
define( 'CONF_QUOTES', get_magic_quotes_gpc());
$epath = dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ));

if ( empty( $_POST['path'] ))
    return;
define( 'APP_ENTER', $_POST['path'] );

require_once $epath.'/app.inc.php';
require_once $epath.'/lib/lib.php';

$form = post( 'form' );

$wspath = $_SERVER['DOCUMENT_ROOT'].APP_ENTER;
$filename = $wspath.'conf.inc.php';
$htaccess = $wspath.'.htaccess';

$result = array( 'success'=> false, 'err' => 1, 'result' => 0 );
if ( !file_exists( $filename ))
{
//$test = json_decode(trim(file_get_contents('php://input')), true);
//header('Content-Type: application/json');
    require_once $epath.'/lib/extmysql.class.php';
    require_once $epath.'/update/init-update.php';

/*    $dir = dirname( $_SERVER['SCRIPT_NAME'] );
    if ( $dir[ strlen( $dir ) - 1] != '/' )
        $dir .= '/';*/
//    define( 'CONF_DIR', $dir );
    
    $step = 'err_connect';
    $sqlname = $wspath.'db.sql';
//    print "$wspath=".chmod( $wspath, 0777 );
    $sql ='';
    try
    {
        if ( $offdot = strpos( $form['dbhost'], ':' ) )
        {
            $form['port'] = susbtr( $form['dbhost'], $offdot + 1 );
            $form['dbhost'] = susbtr( $form['dbhost'], 0, $offdot );
        }
        $options = array( 'errmode' => 'exception',
            'host' => $form['dbhost'] ? $form['dbhost'] : 'localhost' );
        foreach ( array( 'db', 'user', 'pass', 'port' ) as $iv )
            if ( !empty( $form[ $iv ]))
                $options[ $iv ] = $form[ $iv ];

        $db = DB::getInstance( $options );
        $step = 'err_create';
        define( 'CONF_DB', $form['db'] );
        $tables = $db->tables();
        if ( in_array( ENZ_DB, $tables ))
        {
            $step = 'err_dbbusy';
            throw new Exception( 'busy' );
        }
        elseif ( file_exists( $sqlname ) )
            $step = 'err_system';
        else
        {
            $sql = str_replace( array( 'xxx_', 'app_db' ), array( ENZ_PREFIX, ENZ_DB ), 
                         file_get_contents( "$epath/lib/db.sql" ));
            foreach ( explode( '##', $sql ) as $isql )
            {
                if ( trim( $isql ))
                    $db->query( $isql );
            }
        }

        $form['salt'] = pass_generate();
        define( 'CONF_SALT', $form['salt'] );
        
        $ipass = $form['psw'];

        $passmd = pass_md5( $form['psw'], true );
        $form['psw'] = pass_generate();
        if ( empty( $form['storage']))
            $form['storage'] = '/storage';
        $storage = addfname( $_SERVER['DOCUMENT_ROOT'], $form['storage'] );
        if ( !is_dir( $storage ))
        {
        	mkdir( $storage, 0777 );
//            chmod( $storage, 0666 );
        }
        $lang = post( 'lang' );
        $settings = '{ "title": "'.$conf['appname'].'",
            "isalias": 0,
            "perpage": 25,
            "dblang": "'.$lang.'",
            "loginshort": 1,
            "apitoken": "",
            "keeplog": 0,
            "showhelp": 1,
            "version": "'.APP_VERSION.'"
            }';
        $db->query("insert into ?n set pass=?s, ctime=NOW(), settings=?s", ENZ_DB,
                    pass_md5( $form['psw'], true ), $settings );
        $form['dbid'] = 1;//$db->insertid();
        define( 'CONF_DBID', $form['dbid'] );
        define( 'CONF_PREFIX', ENZ_PREFIX );

        $db->query("insert into ?n set login='admin', pass=X'?p', lang=?s,  
                    uptime=CURRENT_TIMESTAMP", ENZ_USERS, $passmd, $lang );
        $iduser = $db->insertid();
        cookie_set( 'iduser', $iduser, 120 );
        cookie_set( 'pass', md5( $ipass ), 120 );
//        $form['dir'] = $dir;
        $form['quotes'] = CONF_QUOTES;
        $form['prefix'] = ENZ_PREFIX;
        if ( empty( $form['dbhost'] ))
            $form['dbhost'] = 'localhost';
        foreach ( $form as $kp => $ip )
            $lines[] = "define( 'CONF_".strtoupper($kp)."', '$ip' );";
//                $lines[] = '$CONF['."'$kp'] = '$ip';";
        $result['user'] = $db->getrow( "select id, login,lang from ?n where id=?s", 
                            ENZ_USERS, $iduser );
        $result['success'] = isset( $lines ) && file_put_contents( $filename, 
            "<?php \r\n".implode( "\r\n", $lines )."\r\n" ) ? 1 : 0;
        @unlink( $htaccess );
        file_put_contents( $htaccess, str_replace( '/eonza/', APP_ENTER, 
        	       file_get_contents( $htaccess.'-i' )));
        $confupd = json_decode( $settings, true );
        GS::set( 'conf', $confupd );
        GS::set( 'confupd', $confupd );
        init_update();
        $db->query( "update ?n set settings=?s where id=1", 
                     ENZ_DB, json_encode( GS::get( 'confupd' )) );
    }
    catch ( Exception $e )
    {
//        print '='.$e->getMessage();
        $result['err'] = $step;
        if ( $step == 'err_dbbusy' )
            $result['temp'] = $form['db'];
        if ( $step == 'err_create' )
        {
            $result['temp'] = $_SERVER['HTTP_HOST'].APP_ENTER.'db.sql';
            file_put_contents( $sqlname, $sql );
        }
    }
}
print json_encode( $result );

