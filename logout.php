<?/*logout.phpFuse Playout System ManagementThis file removes the cookies associated with a user's session and returns them to the login page.*/header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past// Invalidate cookies using time in the pastsetcookie (Fuse_Playout_User, "", time()-3600);setcookie (Fuse_Playout_Pass, "", time()-3600);// Redirect to the logon pageheader ("Location: logon.php");echo "Successfully logged out...";?>