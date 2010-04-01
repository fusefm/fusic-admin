<?

/*
menu.php
Fuse Playout System Management
This file defines the menu on the left hand side of the system.
*/

include('dbinfo.php');
include('authenticate.php');

$authuser = cnsearch($userid,$ldapconn,$ldapbasedn);

if (($authuser == "Too Many Results") OR ($authuser == "No Results")) {
  $authuser = "Unknown User";
}

if ($adminaccess) {
  $userlevel = "(Administrator)";
} elseif ($newsaccess) {
  $userlevel = "(News Team)";
} else {
  $userlevel = "(Presenter)";
}
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" 
  \"http://www.w3.org/TR/html4/loose.dtd\">
<html><head><title>Playout System Management | Fuse FM</title>";
?>
<style type="text/css">
  A:link {text-decoration: none; color: #000000;}
  A:visited {text-decoration: none; color: #000000;}
  A:active {text-decoration: none; color: #000000;}
  A:hover {text-decoration: none; color: #000000;}
  body {font-family: Arial, sans-serif;font-size: 9pt; padding: 0px; margin: 0px}
  td {font-family: Arial, sans-serif;font-size: 9pt}
  table {
    border-width: 1px 1px 1px 1px;
    border-spacing: 0px;
    border-style: solid solid solid solid;
    border-color: black black black black;
    border-collapse: collapse;
  }
  table th {
    border-width: 1px 1px 1px 1px;
    padding: 1px 1px 1px 1px;
    border-style: solid solid solid solid;
    border-color: black black black black;
    -moz-border-radius: 0px 0px 0px 0px;
  }
  table td {
    border-width: 1px 1px 1px 1px;
    padding: 1px 1px 1px 1px;
    border-style: solid solid solid solid;
    border-color: black black black black;
    background-color: white;
    -moz-border-radius: 0px 0px 0px 0px;
  }
  </style>
<?
echo "</head><body>";
echo "<div style=\"position: absolute; overflow: auto; align: center; width: 220px; top: 0px; border-right: 1px solid black; border-bottom: 1px solid black; background-color: #D3D4FF; text-align: center; font-family: Arial, sans-serif; font-size: 10pt\">";
?>
<img src="images/menu_top.gif" width="180">
<br /><b><font style="font-size: 11pt">Menu</font></b><br /><a href="index.php">Instructions</a><br /><br /><i>File Operations</i><br />
<a href="file.php?option=add">Add File(s)</a><br /><a href="file.php?option=edit">Search / Edit File(s)</a><br /><br />
<i>Cart Operations</i><br />
<a href="carts.php?option=edit">Edit Carts</a><br /><br />
<?
if(($adminaccess) OR ($newsaccess)) {
echo "<i>News Operations</i><br />";
echo '<a href="news.php?option=edit">Edit News</a><br /><br />';
}
echo "<i>Playlist Operations</i><br />";
echo '<a href="playlist.php?option=modify">Edit Playlists</a><br /><br />';
if ($adminaccess) {
echo '<i>Settings Operations</i><br /><a href="settings.php?option=filetypes">File Types Permitted</a><br /><a href="settings.php?option=playout">Playout Configuration</a><br /><a href="settings.php?option=crossfade">Crossfade Settings</a><br /><a href="settings.php?option=storage">Storage Settings</a><br /><a href="settings.php?option=daemon">Analysis Daemon Settings</a><br /><a href="settings.php?option=maintenance">Database Maintenance</a><br /><a href="settings.php?option=users">User Management</a><br />
<br />';
}
?>
<br />Logged in as <? echo ucwords($authuser) . " " . $userlevel; ?>.<br /><a href="logout.php" target="_top">Logout</a><br /><br />
<?
echo "</div>";
?>
