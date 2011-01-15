<?php


/*
    Attempt to connect to LDAP server
    If successful   return connection string
    Otherwise       return false
*/
function my_ldap_connect(){
    global $ldap_server;
    
    // Connect to LDAP server
    $conn = ldap_connect($ldap_server['hostname'], $ldap_server['port']);
    if ($conn) {
        // Set Protocol Version
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $ldap_server['protocol_version']);
        
        // Bind to LDAP server
        $bind = ldap_bind($conn, $ldap_server['bind_rdn'], $ldap_server['bind_pass']);
        
        if ($bind) {
            return $conn;
        }     
    }
    return false;
}

/*
    Search LDAP directory
    
    @param  resource    LDAP connection resource
    @param  string      LDAP search filter
    
    If successful   return array of data for first matching object
    Otherwise       return false
*/
function my_ldap_search($conn, $search_filter){
    global $ldap_server, $ldap_attributes;

    $search_result = ldap_get_entries($conn, ldap_search($conn, $ldap_server['base_dn'], $search_filter, array_keys($ldap_attributes)));    
    $user_info = array();
    
    if ($search_result['count'] > 0){
        // Put info for first matching user in to array
        foreach ($ldap_attributes as $k => $v){                
            if (isset($search_result[0][strtolower($k)])) { $user_info[$v] = $search_result[0][$k][0]; }
        }    
    }
            
    return $user_info;
}

?>
