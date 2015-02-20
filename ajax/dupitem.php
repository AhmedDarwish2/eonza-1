<?php

require_once 'ajax_common.php';

if ( $result['success'] )
{
    $pars = post( 'params' );
    $idi = (int)$pars['id'];
    $idtable = (int)$pars['idtable'];
    if ( $idi && $idtable );
    {
        $tables = CONF_PREFIX.'_tables';
        $dbt = $db->getrow("select * from ?n where id=?s", $tables, $idtable );
        if ( !$dbt )
            api_error( 'err_id', "idtable=$idtable" );
        else
        {
            $dbname = alias( $dbt, CONF_PREFIX.'_' );

            $fields = $db->getrow("select * from ?n where id=?s", $dbname, $idi );
            foreach ( array( 'id', '_uptime', '_owner') as $fi )
                unset( $fields[$fi] );
            $result['success'] = $db->insert( $dbname, $fields, GS::owner(), true ); 
            if ( $result['success'] )
            {
                api_log( $idtable, $result['success'], 'create' );
                $columns = $db->getall("select * from ?n where idtable=?s", 
                                          CONF_PREFIX.'_columns', $idtable );
                foreach ( $columns as &$icol )
                {
                    $icol['idalias'] = alias( $icol );
                }
                getitem( $dbt, $result['success'] );
            }
        }
    }
}
print json_encode( $result );
