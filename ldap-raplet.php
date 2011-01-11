<?php
/****************************************
Author: Craig Russell
Date:   11 Jan 2011

Plugin for Raporative http://rapportive.com/
API development docs and discussion at http://groups.google.com/group/raplet-dev

Searches for user in LDAP Directory based upon email address.
Returns various attributes for display alongside GMail thread.

Requires LDAP and JSON modules for PHP
http://php.net/manual/en/book.ldap.php
http://php.net/manual/en/book.json.php

****************************************/

/********************************
           SETTINGS
********************************/

// LDAP Server Details
$ldap_server['hostname']    = "localhost";
$ldap_server['port']        = "389";
$ldap_server['bind_rdn']    = "cn=binduser,o=org";
$ldap_server['bind_pass']   = "b1ndpa55";
$ldap_server['base_dn']     = "ou=people,o=org";

// Required attributes (lower case) and label strings in display order
$ldap_attributes = array (
        'uid'               => 'User Name',
        'cn'                => 'Name',
        'title'             => 'Job Title',
        'ou'                => 'Department',
        'mail'              => 'Email',
        'telephonenumber'   => 'Tel',
        'l'                 => 'Office'
    );

// JSON Response CSS and JavaScript
$css = "
        ul {list-style-type: none; margin: 0; padding: 0;}
        li {font-size: 1.1em; margin: 0 0 0.5em 0; padding: 0;}
        li span {font-weight: bold; margin-right: 0.5em;}
    ";
$js = "";


/********************************
           THE GUTS
********************************/

// Get data from query String
$get_data['email']              = (isset($_GET['email']))               ? $_GET['email'] : "";
$get_data['name']               = (isset($_GET['name']))                ? $_GET['name'] : "";
$get_data['twitter_username']   = (isset($_GET['twitter_username']))    ? $_GET['twitter_username'] : "";
$get_data['callback']           = (isset($_GET['callback']))            ? $_GET['callback'] : "";
$get_data['show']               = (isset($_GET['show']))                ? $_GET['show'] : "";


// Found_info flag defaults to true
$found_info = true;

// Connect to LDAP server
$conn = ldap_connect($ldap_server['hostname'], $ldap_server['port']);
if ($conn) {
    // Connection to LDAP server successful
    
    // Bind to LDAP server
    $bind = ldap_bind($conn, $ldap_server['bind_rdn'], $ldap_server['bind_pass']);
    
    if ($bind) {
        // Bind to LDAP server successful
        
        // Search for users with matching email address
        $search_filter = "mail=".$get_data['email'];        
        $search_result = ldap_get_entries($conn, ldap_search($conn, $ldap_server['base_dn'], $search_filter, array_keys($ldap_attributes)));
        
        if ($search_result['count'] > 0){
            // Found results
            
            // Put info for first matching user in to array
            $user_info = array();
            foreach ($ldap_attributes as $k => $v){                
                if (isset($search_result[0][strtolower($k)])) { $user_info[$v] = $search_result[0][$k][0]; }
            }
        
        }else{
            // No matching users found
            $found_info = false;
        }        
    }else{
        // Bind to LDAP server failed
        $found_info = false;
    }
}else{
    // Connection to LDAP server failed
    $found_info = false;
}


// If user information has been found...
if ($found_info){

    // Build HTML list of user information
    $html = "<ul>";
    foreach ($user_info as $k => $v){
        $html = $html."<li><span>".htmlspecialchars($k)."</span>".htmlspecialchars($v)."</li>";
    }
    $html = $html."</ul>";
    // Set return status
    $status = 200;
    
}else{

    // No user info to return
    $html = "";
    $status = 404;
    
}
$json_data = array('html'=>$html, 'css'=>$css, 'js'=>$js, 'status'=>$status);

// Repair escape slash bug in json_encode()
// http://bugs.php.net/bug.php?id=49366
$json_return = str_replace('\\/', '/', json_encode($json_data));


// Set Content-type
header('Content-type: text/javascript');

// Return Callback and JSON for Rapportive
echo $get_data['callback']."(".$json_return.")";

?>
