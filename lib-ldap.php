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

?>
