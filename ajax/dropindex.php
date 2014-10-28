
<?php

require_once 'ajax_common.php';
require_once 'index_common.php';

$pars = post( 'params' );

if ( $result['success'] )
{
    $table = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $pars['id'] );
    if ( !$table )
        api_error( 'err_id', "id=$pars[id]" );
    else
    {
        $result['success'] = $db->query( "alter table ?n drop index ?n", 
                  $table['alias'] ? $table['alias'] : CONF_PREFIX."_$idtable", $pars['field'] );
        if ( $result['success'] )
            $result['index'] = index_list_table( $table );
    }
}

print json_encode( $result );
