<?

/*
settings.php
Fuse Playout System Management
Manages global settings within the database. PC specific settings such as sound cards cannot be altered.
*/

//include('dbinfo.php');
//include('authenticate.php');
include('menu.php');
echo "<div style=\"position: absolute; left: 220px; width: 100%-220px; padding: 10px\">";
$option = $_GET["option"];
$confmaint = $_GET["confmaint"];

if ($_POST["settings"] != "") {
  $settingsarray = $_POST["settings"];
  $valuesarray = $_POST["values"];
  $numofsettings = sizeof($settingsarray);
} else {
  $numofsettings = 0;
}

$idstoremove = $_POST["idstoremove"];
$filestoremove = $_POST["filestoremove"];
$cartidstoremove = $_POST["cartidstoremove"];
$playlistidstoremove = $_POST["playlistidstoremove"];
$filetypes = $_POST["filetypes"];
$filetypenames = $_POST["filetypenames"];
$newfiletype = $_POST["newfiletype"];
$newfiletypename = $_POST["newfiletypename"];
$filetypeissong = $_POST["filetypeissong"];
$filetypeorder = $_POST["filetypeorder"];
$filetyperemoval = $_POST["filetyperemoval"];

$showname = $_POST["showname"];
$showusers = $_POST["showusers"];
$shows = $_POST["shows"];
$userid = $_POST["userid"];
$removal = $_POST["removal"];
$confirm = $_POST["confirm"];

if ($adminaccess) {
  if (($newfiletype <> "") AND ($newfiletypename <> "")) {
    if ((strlen($newfiletype) == 1) AND (ctype_alpha($newfiletype))) {
      $newfiletype = strtoupper($newfiletype);
      $newfiletypename = trim(ucwords($newfiletypename));
      $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
      $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
      $filetypenamesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_names'");
      $filetypenamesarray = unserialize(mysql_result($filetypenamesquery,0,"Setting_Value"));
      for ($i=0;$i<sizeof($filetypesarray);$i++) {
        if ($filetypesarray[$i] == $newfiletype) {
          $updatesuccess = "The file type letter you entered has already been used. Please try another.";
          break;
        }
      }
      if ($updatesuccess == "") {
        for ($i=0;$i<sizeof($filetypenamesarray);$i++) {
          if ($filetypenamesarray[$i] == $newfiletypename) {
            $updatesuccess = "The file type name you entered has already been used. Please try another.";
            break;
          }
        }
      }
      if ($updatesuccess == "") {
        $filetypesarray[] = $newfiletype;
        $filetypenamesarray[] = $newfiletypename;
        $filetypequery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($filetypesarray) . "' WHERE `Setting_Name` = 'file_type_values'");
        $filetypenamequery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($filetypenamesarray) . "' WHERE `Setting_Name` = 'file_type_names'");
        if (($filetypequery) AND ($filetypenamequery)) {
          $updatesuccess = "The file type $newfiletypename has been added to the database.";
        }
      }
    } else {
      $updatesuccess = "The file type letter you entered was more than one character, or is not in the range A to Z.";
    }
  }
  if (($filetypeorder <> "") OR ($filetyperemoval <> "") OR ($filetypeissong <> "")) {
    // Do file type removal
    $updatedfiletypes = $filetypes;
    $updatedfiletypenames = $filetypenames;
    for ($i=0;$i<sizeof($filetyperemoval);$i++) {
      $removalquery = mysql_query("SELECT * FROM tbl_files WHERE `File_Type` = '" . $filetyperemoval[$i] . "'");
      if (!(mysql_num_rows($removalquery) > 0)) {
        foreach($updatedfiletypes as $key => $value) {
          if($value == $filetyperemoval[$i]) {
            unset($updatedfiletypes[$key]);
            unset($updatedfiletypenames[$key]);
            unset($filetypeorder[$key]);
          }
        }
      }
    }
    $updatedfiletypes = array_values($updatedfiletypes); // Remove null values
    $updatedfiletypenames = array_values($updatedfiletypenames); // Remove null values
    $filetypeorder = array_values($filetypeorder); // Remove null values
    // Finished file type removal - now do ordering
    for ($i=0;$i<sizeof($filetypeorder);$i++) {
      if (!is_numeric($filetypeorder[$i])) {
        $filetypequery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($updatedfiletypes) . "' WHERE `Setting_Name` = 'file_type_values'");
        $filetypenamequery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($updatedfiletypenames) . "' WHERE `Setting_Name` = 'file_type_names'");
        if ((sizeof($filetyperemoval) > 0) AND ($filetypequery) AND ($filetypenamequery)) {
          $updatesuccess = "The selected file types (without any contained files) were successfully removed.";
        }
        $updatesuccess = trim($updatesuccess . " The ordering could not be changed as one or more of the values entered was not a number.");
        break;
      }
    }
    if ($updatesuccess == "") {
      array_multisort($filetypeorder,$updatedfiletypes,$updatedfiletypenames);
      $filetypequery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($updatedfiletypes) . "' WHERE `Setting_Name` = 'file_type_values'");
      $filetypenamequery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($updatedfiletypenames) . "' WHERE `Setting_Name` = 'file_type_names'");
      if ((sizeof($filetyperemoval) > 0) AND ($filetypequery) AND ($filetypenamequery)) {
        $updatesuccess = "The selected file types (without any contained files) were successfully removed.";
      }
      $updatesuccess = trim($updatesuccess . " The file type ordering and song types were successfully changed.");
    }
    // Ordering done, now do song type updating
    $filetypeissongarray = array();
    for ($i=0;$i<sizeof($updatedfiletypes);$i++) {
      for ($j=0;$j<sizeof($filetypeissong);$j++) {
        if ($updatedfiletypes[$i] == $filetypeissong[$j]) {
          $filetypeissongarray[] = "1";
          break;
        }
        if (($j + 1) == sizeof($filetypeissong)) {
          $filetypeissongarray[] = "0";
        }
      }
    }
    $filetypeissongquery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . serialize($filetypeissongarray) . "' WHERE `Setting_Name` = 'file_type_is_song'");
  }
  for($i=0;$i<$numofsettings;$i++) {
    if (($valuesarray[$i] <> "") AND is_numeric($valuesarray[$i])) {
      $settingsquery = mysql_query("UPDATE `tbl_settings` SET `Setting_Value` = '" . $valuesarray[$i] . "' WHERE `Setting_Name` = '" . $settingsarray[$i] . "'");
      if (($settingsquery) AND ($i == 0)) {
        $updatesuccess = "Settings were updated successfully";
      } else if ($i == 0) {
        $updatesuccess = "The settings update failed.";
      }
    }
  }
  if (sizeof($idstoremove) > 0) {
    for($i=0;$i<sizeof($idstoremove);$i++) {
      $databasequery = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `File_ID` = '" . $idstoremove[$i] . "'");
      $databasequery = mysql_query("UPDATE `tbl_carts` SET `File_ID` = '0', `Cart_Colour` = '255255255', `Cart_Title` = 'Empty', `Cart_Refresh` = '0' WHERE `File_ID` = '" . $idstoremove[$i] . "'");
      $databasequery = mysql_query("DELETE FROM `tbl_files` WHERE `File_ID` = '" . $idstoremove[$i] . "'");
      if (($databasequery) AND ($i == 0)) {
        $updatesuccess = "The missing files were successfully removed from the database.";
      } else {
        $updatesuccess = "The missing files could not be removed from the database.";
      }
    }
  }
  if (sizeof($cartidstoremove) > 0) {
    for($i=0;$i<sizeof($cartidstoremove);$i++) {
      $databasequery = mysql_query("UPDATE `tbl_carts` SET `File_ID` = '0', `Cart_Colour` = '255255255', `Cart_Title` = 'Empty', `Cart_Refresh` = '0' WHERE `File_ID` = '" . $cartidstoremove[$i] . "'");
      if (($databasequery) AND ($i == 0)) {
        $updatesuccess = "The missing files were successfully removed from the database.";
      } else {
        $updatesuccess = "The missing files could not be removed from the database.";
      }
    }
  }
  if (sizeof($playlistidstoremove) > 0) {
    for($i=0;$i<sizeof($playlistidstoremove);$i++) {
      $databasequery = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `File_ID` = '" . $playlistidstoremove[$i] . "'");
      if (($databasequery) AND ($i == 0)) {
        $updatesuccess = "The missing files were successfully removed from the database.";
      } else {
        $updatesuccess = "The missing files could not be removed from the database.";
      }
    }
  }
  if (sizeof($filestoremove) > 0) {
    for($i=0;$i<sizeof($filestoremove);$i++) {
      $filestoremove[$i] = str_replace("\\","",$filestoremove[$i]);
      if ((unlink($filestoremove[$i]) !== false) AND ($i == 0)) {
        $updatesuccess = "The files missing from the database were successfully removed.";
      } else if ($i == 0) {
        $updatesuccess = "The files missing from the database could not be removed.";
      }
    }
  }
  // Add a user to a show
  if (($shows > 0) AND ($userid <> "")) {
    if (is_numeric($userid) AND (strlen($userid) == 7)) {
      $checkquery = mysql_query("SELECT * FROM `tbl_show_users` WHERE `Show_ID` = '$shows' AND `User_ID` = '$userid'");
      if (mysql_num_rows($checkquery) > 0) {
        $updatesuccess = "The user $userid is already a member of the show " . shownamefromid($shows) . ".";
      } else {
        $updatequery = mysql_query("INSERT INTO `tbl_show_users` (`Show_ID`, `User_ID`) VALUES ('$shows', '$userid')");
        if ($updatequery) {
          $updatesuccess = "The user $userid was successfully added to the show " . shownamefromid($shows) . ".";
        } else {
          $updatesuccess = "The user $userid could not be added to the show " . shownamefromid($shows) . ".";
        }
      }
    } else {
      $updatesuccess = "The user ID entered was either the wrong length or not numeric.";
    }
  }
  // Add a new show by name
  if (($showname <> "") AND ($removal == "")) {
    $updatequery = mysql_query("INSERT INTO `tbl_show` (`Show_Name`) VALUES ('$showname')");
    if ($updatequery) {
      $updatesuccess = "The show $showname was successfully added to the database.";
    } else {
      $updatesuccess = "The show $showname could not be added to the database.";
    }
  }
  // Remove all shows other than Fuse
  if (($removal == "all") AND ($confirm == "YES")) {
    $playlistquery = mysql_query("SELECT * FROM `tbl_playlist` WHERE `Show_ID` != '0'");
    while ($row = mysql_fetch_array($playlistquery, MYSQL_ASSOC)) {
      if ($row["Show_ID"] != "0") {
        $playlistdelete  = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `Playlist_ID` = '" . $row["Playlist_ID"] . "'");
      } else {
        die("Attempted removal of Fuse files - Possible hacking attempt.");
      }
    }
    $cartdeletequery = mysql_query("DELETE FROM `tbl_carts` WHERE `Show_ID` != '0'");
    $filequery = mysql_query("SELECT * FROM `tbl_files` WHERE `Show_ID` != '0'");
    while ($row = mysql_fetch_array($filequery, MYSQL_ASSOC)) {
      if ($row["Show_ID"] != "0") {
        $filelocation = str_replace($accesspath,$filepath,$row["File_Location"]);
        $filelocation = str_replace("\\","/",$filelocation);
        if (strpos($filelocation,"/0/") === FALSE) {
          unlink($filelocation);
        } else {
          die("Attempted removal of Fuse files - Possible hacking attempt.");
        }
        $filedelete  = mysql_query("DELETE FROM `tbl_files` WHERE `File_ID` = '" . $row["File_ID"] . "'");
      } else {
        die("Attempted removal of Fuse files - Possible hacking attempt.");
      }
    }
    // Need to attempt removal of folders here
    $showdeletequery1 = mysql_query("DELETE FROM `tbl_show_users` WHERE `Show_ID` != '0'");
    $showdeletequery2 = mysql_query("DELETE FROM `tbl_show` WHERE `Show_ID` != '0'");
    if (($cartdeletequery) AND ($showdeletequery1) AND ($showdeletequery2)) {
      $updatesuccess = "All shows were successfully removed.";
    } else {
      $updatesuccess = "One or more elements could not be removed. Please perform database maintenance.";
    }
  }
  // Remove a particular show
  if (($removal == "one") AND ($shows > 0) AND ($confirm == "YES")) {
    $showname = shownamefromid($shows);
    $playlistquery = mysql_query("SELECT * FROM `tbl_playlist` WHERE `Show_ID` = '$shows'");
    while ($row = mysql_fetch_array($playlistquery, MYSQL_ASSOC)) {
      $playlistdelete  = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `Playlist_ID` = '" . $row["Playlist_ID"] . "'");
    }
    $cartdeletequery = mysql_query("DELETE FROM `tbl_carts` WHERE `Show_ID` = '$shows'");
    $filequery = mysql_query("SELECT * FROM `tbl_files` WHERE `Show_ID` = '$shows'");
    while ($row = mysql_fetch_array($filequery, MYSQL_ASSOC)) {
      $filelocation = str_replace($accesspath,$filepath,$row["File_Location"]);
      $filelocation = str_replace("\\","/",$filelocation);
      unlink($filelocation);
      $filedelete  = mysql_query("DELETE FROM `tbl_files` WHERE `File_ID` = '" . $row["File_ID"] . "'");
    }
    // Need to attempt removal of folders here
    $showdeletequery1 = mysql_query("DELETE FROM `tbl_show_users` WHERE `Show_ID` = '$shows'");
    $showdeletequery2 = mysql_query("DELETE FROM `tbl_show` WHERE `Show_ID` = '$shows'");
    if (($cartdeletequery) AND ($showdeletequery1) AND ($showdeletequery2)) {
      $updatesuccess = "The show $showname was successfully removed.";
    } else {
      $updatesuccess = "One or more elements of the show " . shownamefromid($shows) . " could not be removed.";
    }
  }
  // Remove a user from a show
  if ($removal == "user") {
    $shows = explode(",",$shows);
    $userid = $shows[1];
    $shows = $shows[0];
  }
  if (($removal == "user") AND ($shows > 0) AND ($userid <> "")) {
    $deletequery = mysql_query("DELETE FROM `tbl_show_users` WHERE `User_ID` = '$userid' AND `Show_ID` = '$shows'");
    if ($deletequery) {
      $updatesuccess = "The user $userid was successfully removed from the show " . shownamefromid($shows) . ".";
    } else {
      $updatesuccess = "The user $userid could not be removed.";
    }
  }
  // Something else below
  if ($option == "filetypes") {
    $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
    $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
    $filetypenamesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_names'");
    $filetypenamesarray = unserialize(mysql_result($filetypenamesquery,0,"Setting_Value"));
    $filetypeissongquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_is_song'");
    $filetypeissongarray = unserialize(mysql_result($filetypeissongquery,0,"Setting_Value"));
    echo "<b>Files Types Permitted</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br /><b>NOTE:</b> You cannot remove file types that contain one or more files.<br />Types of files allowed within the system and the order they appear in.<br /><br />";
    echo "<form action=\"settings.php?option=filetypes\" method=\"post\">";
    echo "<table style=\"border-collapse: collapse;\" border=\"1\" cellpadding=\"1\">";
    echo "<tr style=\"font-weight: bold\"><td>File Type</td><td>Letter</td><td>Is Song</td><td>Order</td><td>Remove</td></tr>";
    for ($i=0;$i<sizeof($filetypesarray);$i++) {
      echo "<tr><td>" . $filetypenamesarray[$i] . "<input type=\"hidden\" name=\"filetypenames[]\" value=\"" . $filetypenamesarray[$i] . "\"></td><td>" . $filetypesarray[$i] . "<input type=\"hidden\" name=\"filetypes[]\" value=\"" . $filetypesarray[$i] . "\"></td><td><input type=\"checkbox\" name=\"filetypeissong[]\" value=\"" . $filetypesarray[$i] . "\"";
      if ($filetypeissongarray[$i] == "1") {
        echo " checked";
      }
      echo "></td><td><input type=\"text\" name=\"filetypeorder[]\" size=\"2\" maxlength=\"2\" value=\"" . ($i+1) . "\"></td><td><input type=\"checkbox\" name=\"filetyperemoval[]\" value=\"" . $filetypesarray[$i] . "\"></td></tr>";
    }
    echo "<tr><td colspan=\"3\"></td><td colspan=\"2\"><input type=\"submit\" value=\"Update Table\"></td></tr>";
    echo "</table></form><br /><form action=\"settings.php?option=filetypes\" method=\"post\">Add new file type: <input type=\"text\" name=\"newfiletypename\" size=\"17\"> <input type=\"text\" name=\"newfiletype\" size=\"5\" maxlength=\"1\"> <input type=\"submit\" value=\"Save\"></form>";
  } else if ($option == "playout") {
    $settingsarray = array("autodj_repeat_artist_hours", "autodj_repeat_song_hours", "autodj_play_sweeper", "autodj_play_jingle");
    $settingnamesarray = array("Min Time Between Plays Of Same Artist (hours)", "Min Time Between Plays Of Same Track (hours)", "Auto DJ Frequency Of Sweepers (songs)", "Auto DJ Frequency Of Jingles (songs)");
    echo "<b>Playout Settings</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br />The fields below allow you to alter repetition rules, and plays of imaging during Auto DJ.<br /><br />";
    echo "<form action=\"settings.php?option=playout\" method=\"post\">";
    for ($i=0;$i<sizeof($settingsarray);$i++) {
      $playoutquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = '$settingsarray[$i]'");
      echo "$settingnamesarray[$i]: <input type=\"text\" name=\"values[]\" value=\"";
      echo mysql_result($playoutquery,0,"Setting_Value");
      echo "\" size=\"2\"><input type=\"hidden\" name=\"settings[]\" value=\"$settingsarray[$i]\"><br />";
    }
    echo "<br /><input type=\"submit\" value=\"Save\">";
    echo "</form>";
  } else if ($option == "crossfade") {
    $settingsarray = array("next_fade_out_time", "pause_fade_down_time", "pause_fade_up_time", "stop_fade_out_time", "sweeper_fade_down_time", "sweeper_fade_down_volume", "sweeper_fade_up_time", "cart_fade_out_time");
    $settingnamesarray = array("Next Button Fade Out Time (ms)", "Pause Button Fade Down Time (ms)", "Pause Button Fade Up Time (ms)", "Stop Button Fade Out Time (ms)", "Sweeper Fade Down Time (ms)", "Sweeper Fade Down Volume (%)", "Sweeper Fade Up Time (ms)", "Cart Fade Out Time (ms)");
    echo "<b>Crossfade Settings</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br />The fields below allow changes to the way crossfades between files happen.<br /><br />";
    echo "<form action=\"settings.php?option=crossfade\" method=\"post\">";
    for ($i=0;$i<sizeof($settingsarray);$i++) {
      $crossfadequery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = '$settingsarray[$i]'");
      echo "$settingnamesarray[$i]: <input type=\"text\" name=\"values[]\" value=\"";
      echo mysql_result($crossfadequery,0,"Setting_Value");
      echo "\" size=\"2\"><input type=\"hidden\" name=\"settings[]\" value=\"$settingsarray[$i]\"><br />";
    }
    echo "<br /><input type=\"submit\" value=\"Save\">";
    echo "</form>";
  } else if ($option == "storage") {
    $settingsarray = array("presenter_quota");
    $settingnamesarray = array("Presenter Disk Quota (MB)");
    echo "<b>Storage Settings</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br />The field below allows changes to the space presenters are allocated.<br /><br />";
    echo "<form action=\"settings.php?option=storage\" method=\"post\">";
    for ($i=0;$i<sizeof($settingsarray);$i++) {
      $storagequery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = '$settingsarray[$i]'");
      echo "$settingnamesarray[$i]: <input type=\"text\" name=\"values[]\" value=\"";
      echo mysql_result($storagequery,0,"Setting_Value");
      echo "\" size=\"2\"><input type=\"hidden\" name=\"settings[]\" value=\"$settingsarray[$i]\"><br />";
    }
    echo "<br /><input type=\"submit\" value=\"Save\">";
    echo "</form>";
  } else if ($option == "daemon") {
    $settingsarray = array("analysis_daemon_frequency_minutes", "fade_in_detection_level", "fade_out_detection_level");
    $settingnamesarray = array("New File Check Frequency (minutes)", "Fade In Detection Level (0 to 30000)", "Fade Out Detection Level (0 to 30000)");
    echo "<b>Analysis Daemon Settings</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br />The fields below allow changes to how the wave analysis daemon operates.<br /><br />";
    echo "<form action=\"settings.php?option=daemon\" method=\"post\">";
    for ($i=0;$i<sizeof($settingsarray);$i++) {
      $daemonquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = '$settingsarray[$i]'");
      echo "$settingnamesarray[$i]: <input type=\"text\" name=\"values[]\" value=\"";
      echo mysql_result($daemonquery,0,"Setting_Value");
      echo "\" size=\"2\"><input type=\"hidden\" name=\"settings[]\" value=\"$settingsarray[$i]\"><br />";
    }
    echo "<br /><input type=\"submit\" value=\"Save\">";
    echo "</form>";
  } else if ($option == "maintenance") {
    if ($confmaint != "true") {
      die("<b>Database Maintenance</b><br /><b>IMPORTANT:</b> With lots of shows, all having lots of files, this procedure becomes very time consuming. Please only run it INSIDE Fuse and if you're SURE you know what you're doing.<br /><br />After reading the above statement, you must add &confmaint=true to the URL to continue.");
    }
    echo "<b>Database Maintenance</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br /><b>WARNING:</b> Be careful when using this system. It is recommended that you take a full backup of the database and files before using it.<br />This section allows the removal of items from the database that link to non-existant files, or remove files without database entries.";
    $databasequery = mysql_query("SELECT File_ID,File_Location FROM `tbl_files`");
    $missingfilecount = 0;
    $locarray;
    echo "<form action=\"settings.php?option=maintenance&confmaint=true\" method=\"post\">";
    while ($row = mysql_fetch_array($databasequery, MYSQL_ASSOC)) {
      $filelocation  = $row["File_Location"];
      $filelocation = str_replace($accesspath,$filepath,$filelocation);
      $numtoreplace = 4;
      $filelocation = str_replace("\\","/",$filelocation,$numtoreplace);
      if (!file_exists($filelocation)) {
        $missingfilecount++;
        //echo $row["File_ID"] . $filelocation . "<br />";
        echo "<input type=\"hidden\" name=\"idstoremove[]\" value=\"" . $row["File_ID"] . "\">";
        $locarray[] = $row["File_Location"];
      }
    }
    echo "<br />There are currently $missingfilecount entries in the database which are missing their corresponding files.";
    if ($missingfilecount > 0) {
      echo "<br />";
      for ($i=0;$i < sizeof($locarray);$i++) {
        echo $locarray[$i] . "<br />";
      }
      echo "<input type=\"submit\" value=\"Remove These Database Entries\">";
    }
    echo "</form><br />";
    echo "<form action=\"settings.php?option=maintenance&confmaint=true\" method=\"post\">";
    $arrayoffiles = directorytoarray($filepath . "/shows",true);
    $missingfilecount = 0;
    $filearray;
    for ($i=0;$i<sizeof($arrayoffiles);$i++) {
      $pathinfo = pathinfo(strtolower($arrayoffiles[$i]));
      if (($pathinfo['extension'] == "mp3") OR ($pathinfo['extension'] == "m4a") OR ($pathinfo['extension'] == "wav") OR ($pathinfo['extension'] == "flac")) {
        $filelocation = str_replace($filepath,$accesspath,$arrayoffiles[$i]);
        $numtoreplace = 4;
        $filelocation = str_replace("/","\\",$filelocation,$numtoreplace);
        $filelocation = mysql_escape_string(utf8_encode($filelocation));
        $databasequery = mysql_query("SELECT File_ID FROM `tbl_files` WHERE `File_Location` = '$filelocation'");
        if ($databasequery) {
          // Due to encoding fault, ? can be shown when another character is meant. These will therefore be ignored to be safe
          if ((mysql_num_rows($databasequery) == 0) AND (!stristr($filelocation,"?"))) {
            echo "<input type=\"hidden\" name=\"filestoremove[]\" value=\"" . $arrayoffiles[$i] . "\">";
            //echo $filelocation . "<br />";
            $missingfilecount++;
            $filearray[] = $arrayoffiles[$i];
          }
        }
      }
    }
    echo "<br />There are currently $missingfilecount files on disk which are missing their corresponding database entries.";
    if ($missingfilecount > 0) {
      echo "<br />";
      for ($i=0;$i < sizeof($filearray);$i++) {
        echo $filearray[$i] . "<br />";
      }
      echo "<br /><input type=\"submit\" value=\"Remove These Files\">";
    }
    echo "</form><br />";
    echo "<form action=\"settings.php?option=maintenance&confmaint=true\" method=\"post\">";
    $missingfilecount = 0;
    $playlistquery = mysql_query("SELECT File_ID FROM `tbl_playlist_files`");
    if ($playlistquery) {
      while ($row = mysql_fetch_array($playlistquery, MYSQL_ASSOC)) {
        $filequery = mysql_query("SELECT File_ID FROM `tbl_files` WHERE `File_ID` = '" . $row["File_ID"] . "'");
        if (mysql_num_rows($filequery) == 0) {
          $missingfilecount++;
          echo "<input type=\"hidden\" name=\"playlistidstoremove[]\" value=\"" . $row["File_ID"] . "\">";
        }
      }
    }
    $cartquery = mysql_query("SELECT File_ID FROM `tbl_carts` WHERE `File_ID` != '0'");
    if ($cartquery) {
      while ($row = mysql_fetch_array($cartquery, MYSQL_ASSOC)) {
        $filequery = mysql_query("SELECT File_ID FROM `tbl_files` WHERE `File_ID` = '" . $row["File_ID"] . "'");
        if (mysql_num_rows($filequery) == 0) {
          $missingfilecount++;
          echo "<input type=\"hidden\" name=\"cartidstoremove[]\" value=\"" . $row["File_ID"] . "\">";
        }
      }
    }
    echo "<br />There are currently $missingfilecount files referenced in playlists or carts which don't have corresponding file entries.";
    if ($missingfilecount > 0) {
      echo "<br /><input type=\"submit\" value=\"Remove These Database Entries\">";
    }
    echo "</form>";
  } else if ($option == "users") {
    echo "<b>User Management</b>";
    if ($updatesuccess <> "") {
      echo " - <i>$updatesuccess</i>";
    }
    echo "<br /><b>NOTE:</b> Automated scripts may overwrite some changes made here at the start of each broadcast.<br />This section allows manual addition / removal of shows and users.<br /><br />";
    $showlistquery = mysql_query("SELECT * FROM `tbl_show` WHERE `Show_ID` != '0' ORDER BY `Show_Name`");
    echo "<form action=\"settings.php?option=users\" method=\"post\">";
    echo "Show Name: <select name=\"shows\">";
    while ($row = mysql_fetch_array($showlistquery, MYSQL_ASSOC)) {
      echo "<option value=\"" . $row["Show_ID"] . "\">" . $row["Show_Name"] . "</option>";
    }
    echo "</select><br />User ID: <input type=\"text\" name=\"userid\">";
    echo "<br /><br /><input type=\"submit\" value=\"Add User To Selected Show\">";
    echo "</form><br /><br />";
    $showlistquery = mysql_query("SELECT * FROM `tbl_show` WHERE `Show_ID` != '0' ORDER BY `Show_Name`");
    echo "<form action=\"settings.php?option=users\" method=\"post\">";
    echo "Show Name: <select name=\"shows\">";
    while ($row = mysql_fetch_array($showlistquery, MYSQL_ASSOC)) {
      $showname = $row["Show_Name"];
      $userquery = mysql_query("SELECT * FROM `tbl_show_users` WHERE `Show_ID` = '" . $row["Show_ID"] . "' ORDER BY `User_ID`");
      while ($row = mysql_fetch_array($userquery, MYSQL_ASSOC)) {
        echo "<option value=\"" . $row["Show_ID"] . "," . $row["User_ID"] . "\">" . $showname . " - " . $row["User_ID"] . "</option>";
      }
    }
    echo "</select>";
    echo "<br /><br /><input type=\"hidden\" name=\"removal\" value=\"user\"><input type=\"submit\" value=\"Remove Selected User From Show\">";
    echo "</form><br /><br />";
    echo "<form action=\"settings.php?option=users\" method=\"post\">";
    echo "Show Name: <input type=\"text\" name=\"showname\">";
    echo "<br /><br /><input type=\"submit\" value=\"Add Show\">";
    echo "</form><br /><br />";
    $showlistquery = mysql_query("SELECT * FROM `tbl_show` WHERE `Show_ID` != '0' ORDER BY `Show_Name`");
    echo "<form action=\"settings.php?option=users\" method=\"post\">";
    echo "Show Name: <select name=\"shows\">";
    while ($row = mysql_fetch_array($showlistquery, MYSQL_ASSOC)) {
      echo "<option value=\"" . $row["Show_ID"] . "\">" . $row["Show_Name"] . "</option>";
    }
    echo "</select>";
    echo "<br /><input type=\"hidden\" name=\"removal\" value=\"one\">This operation cannot be reversed. Please type 'YES' in the box below if you are sure you want to continue.<br />";
    echo "Confirmation: <input type=\"text\" name=\"confirm\"><br /><br /><input type=\"submit\" value=\"Remove Selected Show\">";
    echo "</form><br /><br /><form action=\"settings.php?option=users\" method=\"post\"><input type=\"hidden\" name=\"removal\" value=\"all\">This operation cannot be reversed. Please type 'YES' in the box below if you are sure you want to continue.<br />";
    echo "Confirmation: <input type=\"text\" name=\"confirm\"><br /><br /><input type=\"submit\" value=\"Remove All User Shows\"></form>";
  }
} else {
  die("Possible hacking attempt");
}
echo "</div></body></html>";
?>