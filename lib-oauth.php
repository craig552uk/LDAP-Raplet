<?php

/*
    Generate token
    
    @param  string  Uniquely identifying string
    Returns unique identifier string
    
*/
function gen_token($string){
    global $oauth_params;
    return $oauth_params['head_string'] . sha1($oauth_params['salt_string'] . $string);
}


/*
    Verifies if array contains a token with the correct head string
    
    @param  array   Array of data
    
    Returns     true if array contains matching identifier
    Otherwise   false
*/
function contains_token($data){
    global $oauth_params;
    $head_len = strlen($oauth_params['head_string']);
    foreach($data as $value){
        if($oauth_params['head_string'] == substr($value,0,$head_len)) return true;
    }
    return false;
}
?>
