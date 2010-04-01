<?

/*
authenticate.php
Fuse Playout System Management
This file checks a user's cookies to ensure they are authorised to be logged in, and with what access level.
*/

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ('includes/functions.php');

// Global variable
$adminaccess = false;
$newsaccess = false;
$userid;

if(isset($_COOKIE['Fuse_Playout_User']) AND isset($_COOKIE['Fuse_Playout_Pass'])) {

  // Start checking cookie's authenticity
  $user = $_COOKIE['Fuse_Playout_User'];
  $md5pass = $_COOKIE['Fuse_Playout_Pass'];

  $pass = passwordsearch($user,$ldapconn,$ldapbasedn);
 
  if ($pass == "No Results") {
      // Cookies invalidated
      $hour = time() - 3600;
      setcookie(Fuse_Playout_User, "false", $hour);
      setcookie(Fuse_Playout_Pass, "false", $hour);
      // The uid entered doesn't have a corresponding password record.
      header ("Location: logon.php");
      die("Possible hacking attempt");
    } else if ($pass == "Too Many Results") {
      // Cookies invalidated
      $hour = time() - 3600;
      setcookie(Fuse_Playout_User, "false", $hour);
      setcookie(Fuse_Playout_Pass, "false", $hour);
      // The search returned multiple passwords and we don't know which one to use. They should be unique.
      header ("Location: logon.php");
      die("Possible hacking attempt");
    } else {
      // We should now have the correct password, but it needs to be verified.

      if (strcasecmp($md5pass,$pass) == 0) {

        // Password matches. Now to check permissions
        
        $authenticated = false;
        
        // Cycle through admin groups to locate the user
        for ($i = 0; $i < count($admingroups); $i++) {
          if (groupsearch($admingroups[$i],$user,$ldapconn,$ldapbasedn)) {
            $authenticated = true;
            $adminaccess = true;
          }
        }
        
        // Cycle through news groups to locate the user
        if ($authenticated == false) {
          for ($i = 0; $i < count($newsgroups); $i++) {
            if (groupsearch($newsgroups[$i],$user,$ldapconn,$ldapbasedn)) {
              $authenticated = true;
              $newsaccess = true;
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
          setcookie(Fuse_Playout_Pass, $pass, $hour);
          // Allows user's specific show info to be located.
          $userid = $user;
        } else {
          // User is not a member of the correct LDAP groups.
          header ("Location: logon.php");
          die("Possible hacking attempt");
        }
        
      } else {
        // Cookie password does not match the one in the database. Password may have been changed.
        // Cookies invalidated, user returned to logon form.
        $hour = time() - 3600;
        setcookie(Fuse_Playout_User, "false", $hour);
        setcookie(Fuse_Playout_Pass, "false", $hour);
        header ("Location: logon.php");
        die("Possible hacking attempt");
      }
    }
  // Finish checking cookie's authenticity

} else {
  header ("Location: logon.php");
  die("Possible hacking attempt");
}
?>
