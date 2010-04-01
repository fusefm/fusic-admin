<?

/*
logon.php
Fuse Playout System Management
This file provides a login form and sets session cookies once details have been authenticated.
*/

include ('dbinfo.php');
include ('includes/functions.php');
$user = $_POST["user"];
$pass = $_POST["pass"];

// Check if the user has cookies present
if(isset($_COOKIE['Fuse_Playout_User']) AND isset($_COOKIE['Fuse_Playout_Pass'])) {

  // Start checking cookie's authenticity
  $user = $_COOKIE['Fuse_Playout_User'];
  $md5pass = $_COOKIE['Fuse_Playout_Pass'];

  $pass = passwordsearch($user,$ldapconn,$ldapbasedn);
  
  if ($pass == "No Results") {
      // The uid entered doesn't have a corresponding password record.
      $errortext = "Invalid cookies. Hacking attempt recorded.";
      // Cookies invalidated
      $hour = time() - 3600;
      setcookie(Fuse_Playout_User, "false", $hour);
      setcookie(Fuse_Playout_Pass, "false", $hour);
    } else if ($pass == "Too Many Results") {
      // The search returned multiple passwords and we don't know which one to use. They should be unique.
      $errortext = "Invalid cookies. Hacking attempt recorded.";
      // Cookies invalidated
      $hour = time() - 3600;
      setcookie(Fuse_Playout_User, "false", $hour);
      setcookie(Fuse_Playout_Pass, "false", $hour);
    } else {
      // We should now have the correct password, but it needs to be verified.
      if (strcasecmp($md5pass,$pass) == 0) {
        // Password matches. Now to check permissions
        
        $authenticated = false;
        
        // Cycle through admin groups to locate the user
        for ($i = 0; $i < count($admingroups); $i++) {
          if (groupsearch($admingroups[$i],$user,$ldapconn,$ldapbasedn)) {
            $authenticated = true;
          }
        }
        
        // Cycle through news groups to locate the user
        if ($authenticated == false) {
          for ($i = 0; $i < count($newsgroups); $i++) {
            if (groupsearch($newsgroups[$i],$user,$ldapconn,$ldapbasedn)) {
              $authenticated = true;
            }
          }
        }
        
        // Cycle through user groups to locate the user
        if ($authenticated == false) {
          for ($i = 0; $i < count($usergroups); $i++) {
            if (groupsearch($usergroups[$i],$user,$ldapconn,$ldapbasedn)) {
              $authenticated = true;
            }
          }
        }
        
        if ($authenticated) {
          // Set the cookies, ensuring an allowed logon time of one hour.
          $hour = time() + 3600;
          setcookie(Fuse_Playout_User, $user, $hour);
          $cookiepass = "{MD5}" . base64_encode(mhash(MHASH_MD5,$pass));
	  //$cookiepass = str_replace("//","/",$cookiepass);
          setcookie(Fuse_Playout_Pass, $cookiepass, $hour);
          header ("Location: index.php");
        } else {
          // User is not a member of the correct LDAP groups.
          $errortext = "You do not have the required permissions to log in.";
        }
        
      } else {
        // Cookie password does not match the one in the database. Password may have been changed.
        // Cookies invalidated, user returned to logon form.
        $hour = time() - 3600;
        setcookie(Fuse_Playout_User, "false", $hour);
        setcookie(Fuse_Playout_Pass, "false", $hour);
      }
    }
  // Finish checking cookie's authenticity

} else {

  // Start default login
  
  if ($user == "" OR $pass == "") {
  // Default login box, shown initially, no failures (see bottom of script).
  } else {
    // Bind to ldap server with the user's uid and password
    $ldapbind = ldap_bind($ldapconn, "uid=" . $user . "," . $ldapbasedn, $pass);
  
    // Verify binding
    if ($ldapbind) {

      // Logged in, now to check permissions
        
      $authenticated = false;
      
      // Cycle through admin groups to locate the user
      for ($i = 0; $i < count($admingroups); $i++) {
        if (groupsearch($admingroups[$i],$user,$ldapconn,$ldapbasedn)) {
          $authenticated = true;
        }
      }
      
      // Cycle through news groups to locate the user
      if ($authenticated == false) {
        for ($i = 0; $i < count($newsgroups); $i++) {
          if (groupsearch($newsgroups[$i],$user,$ldapconn,$ldapbasedn)) {
            $authenticated = true;
          }
        }
      }
        
      // Cycle through user groups to locate the user
      if ($authenticated == false) {
        for ($i = 0; $i < count($usergroups); $i++) {
          if (groupsearch($usergroups[$i],$user,$ldapconn,$ldapbasedn)) {
            $authenticated = true;
          }
        }
      }
        
      if ($authenticated) {
        // Set the cookies, ensuring an allowed logon time of one hour.
        $hour = time() + 3600;
        setcookie(Fuse_Playout_User, $user, $hour);
        $cookiepass = "{MD5}" . base64_encode(mhash(MHASH_MD5,$pass));
	//$cookiepass = str_replace("//","/",$cookiepass);
        setcookie(Fuse_Playout_Pass, $cookiepass, $hour);
        header ("Location: index.php");
      } else {
        // User is not a member of the correct LDAP groups.
        $errortext = "You do not have the required permissions to log in.";
      }

    } else {
      // LDAP bind failed. Incorrect password or server error.
      $errortext = "Incorrect username or password";
    }
    
  }
  // End default login
}

// Print logon form if required. Error text included.
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" 
  \"http://www.w3.org/TR/html4/loose.dtd\">
<html><head><title>Playout System Management | Fuse FM</title><style type=\"text/css\">
  body{background-color: #D3D4FF;}
</style>";
echo "</head><body>";
echo "<div align=\"center\" style=\"text-align: center\"><br /><img src=\"images/main_logo.gif\" width=\"250\" height=\"156\"><br /><br /><font face=\"Arial, sans-serif\" style=\"font-size: 10pt;\"><font style=\"font-size: 12pt\"><b>Playout System Management | Fuse FM</b></font><br /><i>Authorised Users Only</i><br /><br /><b>Please Log In</b>";
if ($errortext) {
  echo " - <i><font color=\"red\">" . $errortext . "</font></i>";
} else {
  echo " - <i>Using Your Site Login Details</i>";
}
echo "<br /><form method=\"post\" name=\"logonform\">User: <input type=\"text\" name=\"user\" value=\"$user\"><br />Password: <input type=\"password\" name=\"pass\"><br /><br /><input type=\"submit\" value=\"Log In\"></form></font></div><script language=\"JavaScript\">document.logonform.user.focus();</script>
</body></html>";

?>
