<?php
/****************************************
Author: Craig Russell
Date:   11 Jan 2011

Plugin for Raportive http://rapportive.com/
API development docs and discussion at http://groups.google.com/group/raplet-dev

Searches for user in LDAP Directory based upon email address.
Returns various attributes for display alongside GMail thread.

Requires LDAP and JSON modules for PHP
http://php.net/manual/en/book.ldap.php
http://php.net/manual/en/book.json.php

****************************************/

include_once('settings.php');

// Get data from query String
$get_data['email']              = (isset($_GET['email']))               ? $_GET['email'] : "";
$get_data['name']               = (isset($_GET['name']))                ? $_GET['name'] : "";
$get_data['twitter_username']   = (isset($_GET['twitter_username']))    ? $_GET['twitter_username'] : "";
$get_data['callback']           = (isset($_GET['callback']))            ? $_GET['callback'] : "";
$get_data['show']               = (isset($_GET['show']))                ? $_GET['show'] : "";

if ($get_data['show'] == "metadata"){

    // Set required metadata values
    $json_data['name']          = $rapplet_meta['name'];
    $json_data['description']   = $rapplet_meta['description'];
    $json_data['welcome_text']  = $rapplet_meta['welcome_text'];
    $json_data['icon_url']      = $rapplet_meta['icon_url'];
    $json_data['preview_url']   = $rapplet_meta['preview_url'];
    $json_data['provider_name'] = $rapplet_meta['provider_name'];
    $json_data['provider_url']  = $rapplet_meta['provider_url'];

    // Set optional metadata values
    if ($rapplet_meta['data_provider_name'] != "")  $json_data['data_provider_name'] = $rapplet_meta['data_provider_name'];
    if ($rapplet_meta['data_provider_url'] != "")   $json_data['dat_provider_url']   = $rapplet_meta['data_provider_url'];
    if ($rapplet_meta['configuration'] != "")       $json_data['configuration']      = $rapplet_meta['configuration'];

}else{  // ($get_data['show'] != "metadata")

    // Found_info flag defaults to true
    $found_info = true;

    // Connect to LDAP server
    $conn = ldap_connect($ldap_server['hostname'], $ldap_server['port']);
    if ($conn) {
        // Connection to LDAP server successful
        
        // Set Protocl Version
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $ldap_server['protocol_version']);
        
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

} // End if($get_data['show'] == "metadata")

// Repair escape slash bug in json_encode()
// http://bugs.php.net/bug.php?id=49366
$json_return = str_replace('\\/', '/', json_encode($json_data));


// Set Content-type
header('Content-type: text/javascript');

// Return Callback and JSON for Rapportive
echo $get_data['callback']."(".$json_return.")";

?>
