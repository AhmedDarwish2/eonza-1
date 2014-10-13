<?php

require_once 'ajax_common.php';

$pars = post( 'params' );
$idi = $pars['id'];

if ( $result['success'] )
{
    if ( !$idi )
    {
        $result['success'] = $db->insert( CONF_PREFIX.'_sets', pars_list( 'title', $pars ), 
              array( "_owner=$USER[id]"), true ); 
    }
    else
    {
        $result['result'] = array();
        if ( $db->update( CONF_PREFIX.'_sets', 
                    pars_list( 'title', $pars ), '', $idi ))
        {

            $result['result']['title'] =  $pars['title'];
        }
    }
}
print json_encode( $result );
