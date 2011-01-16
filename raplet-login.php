<?php

include_once('settings.php');
include_once('lib-ldap.php');
include_once('lib-oauth.php');

// Get parameters
$param['redirect_uri']  = (isset($_GET['redirect_uri'])) ? htmlspecialchars($_GET['redirect_uri']) : "";
$param['client_id']     = (isset($_GET['client_id'])) ? htmlspecialchars($_GET['client_id']) : "";
$param['response_type'] = (isset($_GET['response_type'])) ? htmlspecialchars($_GET['response_type']) : "";
$param['username']      = (isset($_GET['username'])) ? htmlspecialchars($_GET['username']) : "";
$param['password']      = (isset($_GET['password'])) ? htmlspecialchars($_GET['password']) : "";

// Check parameters meet expected values
$param_ok['redirect_uri']   = ("https://rapportive.com/raplets" == substr($param['redirect_uri'],0,30)) ? true : false;
$param_ok['client_id']      = ("rapportive" == $param['client_id']) ? true : false;
$param_ok['response_type']  = ("token" == $param['response_type']) ? true : false;

// Allow form if 
$params_ok = ($param_ok['redirect_uri'] && $param_ok['client_id'] && $param_ok['response_type']) ? true : false;

if (isset($_GET['submit'])){
    // Attempt to authenticate
    $conn = my_ldap_connect();
    $data = my_ldap_authenticate($conn, $param['username'], $param['password']);
    $authenticated = (isset($data['dn'])) ? true : false;

    if ($authenticated){
        // Re-bind as privileged user
        my_ldap_bind($conn);
        
        // Get token from user data
        $token = contains_token($data['tokens']);

        if (!is_string($token)){
            // Create new token if needed
            $token = gen_token($data['dn']);
            // Save new token in LDAP directory
            my_ldap_add_token($conn, $data['dn'], $token);
        }
    }    
}

?>


<?php if($params_ok):           /* Safe to show form */ ?>
    <?php if (!$authenticated): /* Not authenticated */ ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>Authenticate</title>
            <meta charset="utf-8">
            <style>
                body {font-family: ariel, helvetica, sans-serif;}
                legend {font-weight: bold;}
                input, label {float: left; padding: 3px; margin: 5px 0;}
                label {clear: left; width: 200px;}
                input[type=submit] {clear: left; margin-left: 205px; margin-right: 10px;}
            </style>
        <head>
        <body>

            <fieldset>
                <legend>Authenticate</legend>
                <form method="get" action="raplet-login.php">
                    <label for="username">User Name</label>    <input type="text" id="username" name="username" value="<?php echo $param['username'];?>" />
                    <label for="password">Password</label>     <input type="password" id="password" name="password" />
                    
                    <input type="hidden" id="redirect_uri" name="redirect_uri" value="<?php echo $param['redirect_uri'];?>" />
                    <input type="hidden" id="client_id" name="client_id" value="<?php echo $param['client_id'];?>" />
                    <input type="hidden" id="response_type" name="response_type" value="<?php echo $param['response_type'];?>" />
                    
                    <input type="submit" id="submit" name="submit" value="Login" />
                    <input type="button" id="cancel" name="cancel" value="Cancel" onClick="window.close();"/>
                </form>
            </fieldset>
        </body>
        </html>
    <?php else:             /* Redirect back to Rapportive */ ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>Authenticate</title>
            <meta charset="utf-8">
            <meta http-equiv="refresh" content="0; url=<?php echo urlencode($param['redirect_uri']) . '#' . urlencode($token);?>">
        <head>
        <body>
            <p>If you are not redirected <a href="<?php echo urlencode($param['redirect_uri']) . '#' . urlencode($token);?>">click here</a>.<p>
        </body>
        </html>    
    <?php endif; ?>
<?php else:                 /* Invalid query string data */ ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Authenticate</title>
        <meta charset="utf-8">
    <head>
    <body>
        <h1>Invalid Request</h1>
    </body>
    </html>
<?php endif; ?>

