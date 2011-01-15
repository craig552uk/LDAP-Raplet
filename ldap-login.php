<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
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
        <form method="get" action="ldap-login.php">
            <label for="username">User Name</label>    <input type="text" id="username" name="username" value="<?php if (isset($_GET['username'])) echo htmlspecialchars($_GET['username']);?>" />
            <label for="password">Password</label>     <input type="password" id="password" name="password" />
            
            <input type="hidden" id="redirect_uri" name="redirect_uri" value="<?php echo htmlspecialchars($_GET['redirect_uri']);?>" />
            <input type="hidden" id="client_id" name="client_id" value="<?php echo htmlspecialchars($_GET['client_id']);?>" />
            <input type="hidden" id="response_type" name="response_type" value="<?php echo htmlspecialchars($_GET['response_type']);?>" />
            
            <input type="submit" id="submit" name="submit" value="Login" />
            <input type="button" id="cancel" name="cancel" value="Cancel" onClick="window.close();"/>
        </form>
</body>
</html>
