<?

/*
index.php
Fuse Playout System Management
Initial page returned once a user has logged in. Contains brief instructions etc.
*/

//include('dbinfo.php');
//include('authenticate.php');
include('menu.php');
echo "<div style=\"position: absolute; left: 220px; width: 100%-220px; padding: 10px\">";
?>
<font style="font-family: Arial, sans-serif;font-size: 12pt"><b>Welcome to the Fuse FM playout system management interface.</b><br />
<font style="font-size: 10pt"><i>Please select an option from the left hand menu to continue...</i></font></font>
<br /><br />
<font style="font-family: Arial, sans-serif;font-size: 9pt">
<noscript><font color="red"><b>WARNING:</b></font> This site depends on JavaScript for many of its functions. Your browser appears to have JavaScript disabled. We recommend that you enable it now to avoid problems later.<br /><br /></noscript>
<font style="font-size: 16pt"><b>Note:</b> Please remember that you can only upload 100MB worth of files at a time. You have a 1GB limit overall.</font><br /><br />
<b>Managing Files</b><br />
Files are any item of audio you want the playout system to deal with. Whether it's a jingle, cart or a song, it must first be included in the file management system before it can be used anywhere else. <b>Note:</b> Hourly news should not be uploaded here, but instead through its specific uploader.<br /><br />
<b>Managing Carts</b><br />
All of the cart walls you have access to when logged in. You can assign any file to a cart, give it a title, and change its colour.<br /><br />
<?
if(($newsaccess) OR ($adminaccess)) {
echo "<b>Managing News</b><br />";
echo "Uploader for hourly news, always to be shown on the Fuse cart wall on screen 1. Requires news reader's name.<br /><br />";
}
?>
<b>Managing Playlists</b><br />
Allows the contents of Fuse playlists A, B and C to be edited, along with any additional lists you'd find it useful to have.
<br /><br />
<?
if($adminaccess) {
echo "<b>Managing Settings</b><br />
A few extra bits and pieces about how the system plays audio out.";
}
?>
</font>
</div></body></html>