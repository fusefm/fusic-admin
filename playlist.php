<?

/*
playlist.php
Fuse Playout System Management
Manages playlists within the database. Addition, editing and deletion.
*/

//include('dbinfo.php');
//include('authenticate.php');
include('menu.php');
echo "<div style=\"position: absolute; left: 220px; width: 100%-220px; padding: 10px\">";
$option = $_GET["option"];
$activeshow = $_POST["show"];
$newplaylistname = $_POST["plname"];
$activeplaylist = $_POST["playlist"];
$action = $_POST["action"];
$fileid = $_POST["fileid"];

if ($option == "modify") {
  if (($activeshow == "0") AND ($adminaccess === false) AND (($action == "new") OR ($action == "remove") OR (($action == "edit") AND ($newplaylistname <> "")))) {
    die("Possible hacking attempt.");
  }
  echo "<b><font style=\"font-size: 10pt\">Playlist Editor</font></b>";
  
  // Add new playlist if requested in last submission.
  if (($newplaylistname <> "") AND ($action == "new")) {
    $query = mysql_query("INSERT INTO `tbl_playlist` (Show_ID, Playlist_Name) VALUES ('$activeshow', '" . $newplaylistname . "')");
    if ($query) {
      echo " - <i>Successfully added new playlist '" . stripslashes($newplaylistname) . "'</i>";
    } else {
      echo " - <i>Error: New playlist addition failed.</i>";
    }
  }
  
  // Remove playlist if requested in last submission.
  if ($action == "remove") {
    $playlistname = playlistnamefromid($activeplaylist);
    $query = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `Playlist_ID` = '$activeplaylist'");
    $query = mysql_query("DELETE FROM `tbl_playlist` WHERE `Playlist_ID` = '$activeplaylist'");
    if ($query) {
      echo " - <i>Successfully removed playlist '$playlistname'</i>";
    } else {
      echo " - <i>Error: Playlist deletion failed. It may already have been removed.</i>";
    }
  }
  
  if (($action == "edit") AND ($newplaylistname <> "")) {
    $query = mysql_query("UPDATE `tbl_playlist` SET `Playlist_Name` = '" . $newplaylistname . "' WHERE `Playlist_ID` = '$activeplaylist'");
    if ($query) {
      echo " - <i>Successfully renamed playlist to '" . stripslashes($newplaylistname) . "'</i>";
    } else {
      echo " - <i>Error: Playlist renaming failed.</i>";
    }
  }
  
  echo "<br />";
  /*
  // Find out what shows a user can manage and show them in a combo box
  // May also show the Fuse playlists but not allow editing by normal users... ######################## <<<<<<<<<<<<<<<
  $query = showidsfromuid($userid);
  $numberofrows = 0;
  if ($query) {
    $numberofrows = mysql_num_rows($query);
    while ($row = mysql_fetch_assoc($query)) {
      if (($numberofrows == 1) AND (!$adminaccess)) {
        $activeshow = $row['Show_ID'];
      }
      if ($activeshow == $row['Show_ID']) {
        $selected = ' selected';
      } else {
        $selected = "";
      }
      $showoptionset = $showoptionset . '<option value="' . $row['Show_ID'] . '"' . $selected . '>' . shownamefromid($row['Show_ID']) . '</option>';
    }
  }
  if ($adminaccess) {
    $query = notshowidsfromuid($userid);
    if ($query) {
      while ($row = mysql_fetch_assoc($query)) {
        if ($activeshow == $row['Show_ID']) {
          $selected = ' selected';
        } else {
          $selected = "";
        }
        $showoptionset = $showoptionset . '<option style="color: rgb(128,128,128)" value="' . $row['Show_ID'] . '"' . $selected . '>' . shownamefromid($row['Show_ID']) . '</option>';
      }
    }
  }  
  // Check if the user can manage the main Fuse playlists and if so add to the combo box
  if ($adminaccess) {
    if ($numberofrows == 0) {
      if ($activeshow == "") {
        $activeshow = "0";
      }
    }
    if ($activeshow == "0") {
      $selected = ' selected'; 
    } else {
      $selected = "";
    }
    $showoptionset = '<option value="0"' . $selected . '>' . shownamefromid(0) . '</option>' . $showoptionset;
  }
  if ($showoptionset == "") {
    $showoptionset = '<option value="X">No Shows Listed</option>';
  }

  // Print out the combo box selection form
  echo '<form method="post">Please select a show: <select name="show">' . $showoptionset . '</select>&nbsp;<input type="submit" value="Go / Refresh"></form><br />';
  */
  
  // Find out what shows a user can manage and show them in a combo box
  $query = showidsfromuid($userid);
  $numberofrows;
  if ($query) {
    $numberofrows = mysql_num_rows($query);
    while ($row = mysql_fetch_assoc($query)) {
      if (($numberofrows == 1) AND (!$adminaccess) AND ($activeshow == "")) {
        $activeshow = $row['Show_ID'];
      }
      if ($activeshow == $row['Show_ID']) {
        $selected = ' selected';
      } else {
        $selected = "";
      }
      $showoptionset = $showoptionset . '<option value="' . $row['Show_ID'] . '"' . $selected . '>' . shownamefromid($row['Show_ID']) . '</option>';
    }
  }
  if ($adminaccess) {
    $query = notshowidsfromuid($userid);
    if ($query) {
      while ($row = mysql_fetch_assoc($query)) {
        if ($activeshow == $row['Show_ID']) {
          $selected = ' selected';
        } else {
          $selected = "";
        }
        $showoptionset = $showoptionset . '<option style="color: rgb(128,128,128)" value="' . $row['Show_ID'] . '"' . $selected . '>' . shownamefromid($row['Show_ID']) . '</option>';
      }
    }
  }  
  // Check if the user can manage the main Fuse files and if so add to the combo box
  if ($adminaccess) {
    if ($numberofrows == 0) {
      if ($activeshow == "") {
        $activeshow = "0";
      }
    }
  }
  if ($activeshow == "0") {
    $selected = ' selected'; 
  } else {
    $selected = "";
  }
  $showoptionset = '<option value="0"' . $selected . '>' . shownamefromid(0) . '</option>' . $showoptionset;
  
  if ($showoptionset == "") {
    $showoptionset = '<option value="X">No Shows Listed</option>';
  }
    
  // Print out the combo box selection form
  echo '<form action="playlist.php?option=modify" method="post">Please select a show: <select name="show">' . $showoptionset . '</select>&nbsp;<input type="submit" value="Go / Refresh"></form><br />';
  
  if (($activeshow <> "") AND ($activeshow <> "X")) {
    $playlists = getplaylists($activeshow);
    if (($playlists) AND (mysql_num_rows($playlists) > 0)) {
      echo "<table callpadding=\"1\" cellspacing=\"0\" border=\"1\" style=\"border-collapse: collapse\">";
      echo "<tr valign=\"top\" style=\"font-weight: bold\"><td valign=\"top\" style=\"text-align: center;\" width=\"300\">Name</td><td valign=\"top\" style=\"text-align: center;\" width=\"70\">Files</td><td valign=\"top\" style=\"text-align: center;\" width=\"120\">";
      if (($activeshow == "0") AND ($adminaccess === false)) {
        echo "View";
      } else {
        echo "Edit";
      }
      echo "</td><td valign=\"top\" style=\"text-align: center;\" width=\"120\">Remove</td></tr>";
        while ($row = mysql_fetch_assoc($playlists)) {
          $filesinplaylist = mysql_num_rows(getplaylistfiles($row['Playlist_ID']));
          echo "<tr valign=\"top\"><td valign=\"top\" style=\"text-align: center;\">" . $row['Playlist_Name'] . "</td><td valign=\"top\" style=\"text-align: center;\">$filesinplaylist</td><td valign=\"top\" style=\"text-align: center;\"><form action=\"playlist.php?option=edit\" method=\"post\" style=\"margin-bottom: 0px\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"playlist\" value=\"" . $row['Playlist_ID'] . "\"><input type=\"submit\" value=\"";
          if (($activeshow == "0") AND ($adminaccess === false)) {
            echo "View";
          } else {
            echo "Edit";
          }
          echo "\" style=\"font-size: 8pt\"></form></td><td valign=\"top\" style=\"text-align: center;\"><form action=\"playlist.php?option=del\" method=\"post\" style=\"margin-bottom: 0px\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"playlist\" value=\"" . $row['Playlist_ID'] . "\"><input type=\"submit\" value=\"Remove\" style=\"font-size: 8pt\"";
          if (($activeshow == "0") AND ($adminaccess === false)) {
            echo " disabled";
          }
          echo "></form></td></tr>";
        }
      echo "</table>";
    } else {
      echo "No playlists found<br />";
    }
    echo "<br /><form method=\"post\">Add new playlist: <input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"action\" value=\"new\"><input type=\"text\" name=\"plname\">&nbsp;<input type=\"submit\" value=\"Save\"";
    if (($activeshow == "0") AND ($adminaccess === false)) {
      echo " disabled";
    }
    echo "></form>";
  }
  
} else if ($option == "edit") {
  echo "<b><font style=\"font-size: 10pt\">Playlist Editor</font></b>";

  // Remove file from playlist.
  if ($action == "remove") {
    if (($activeshow == "0") AND ($adminaccess === false)) {
      die("Possible hacking attempt.");
    }
    $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid'");
    $artist = mysql_result($query,0,'File_Artist');
    $title = mysql_result($query,0,'File_Title');
    $query = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `File_ID` = '$fileid' AND Playlist_ID = '$activeplaylist' LIMIT 1");
    if ($query) {
      echo " - <i>Successfully removed file $title by $artist from the playlist.</i>";
    } else {
      echo " - <i>Error: Could not remove file from the playlist.</i>";
    }
  }

  // Add file to the playlist.
  if ($action == "add") {
    if (($activeshow == "0") AND ($adminaccess === false)) {
      die("Possible hacking attempt.");
    }
    $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid'");
    $artist = mysql_result($query,0,'File_Artist');
    $title = mysql_result($query,0,'File_Title');
    $query = mysql_query("SELECT * FROM `tbl_playlist_files` WHERE `File_ID` = '$fileid' AND `Playlist_ID` = '$activeplaylist'");
    if (mysql_num_rows($query) > 0) {
      echo " - <i>Error: This file is already in the playlist.</i>";
    } else {
      $query = mysql_query("INSERT INTO `tbl_playlist_files` (`File_ID`, `Playlist_ID`) VALUES('$fileid', '$activeplaylist')"); 
      if ($query) {
        echo " - <i>Successfully added file $title by $artist to the playlist.</i>";
      } else {
        echo " - <i>Error: Could not add file to the playlist.</i>";
      }
    }
  }
 
  echo "<br />";

  echo "<script type=\"text/javascript\" src=\"scripts/search.js\"></script>";
  echo "Currently editing playlist '" . playlistnamefromid($activeplaylist) . "' from show '" . shownamefromid($activeshow) . "'.<br /><br />";
  echo "<form method=\"post\" action=\"playlist.php?option=modify\"><input type=\"hidden\" name=\"action\" value=\"edit\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"playlist\" value=\"$activeplaylist\"><input type=\"text\" name=\"plname\" value=\"" . playlistnamefromid($activeplaylist) . "\">&nbsp;<input type=\"submit\" value=\"Save\"";
  if (($activeshow == "0") AND ($adminaccess === false)) {
    echo " disabled";
  }
  echo "></form><br />";
  $files = getplaylistfiledetails($activeplaylist);
  if (($files) AND (mysql_num_rows($files) > 0)) {
    echo "<table width=\"100%\" cellpadding=\"1\" border=\"1\" style=\"border-collapse: collapse\">";
    echo "<tr style=\"font-weight: bold\"><td>Artist</td><td>Title</td><td>Album</td><td>Duration</td><td>Intro</td><td>Fade In</td><td>Fade Out</td><td>Last Played</td><td colspan=\"2\"></td></tr>";
    while($row = mysql_fetch_array($files, MYSQL_ASSOC)) {
      echo "<tr>";
      echo "<td>" . $row['File_Artist'] . "</td><td>" . $row['File_Title'] . "</td><td>" . $row['File_Album'] . "</td>";
      $durationmins = floor($row['File_Duration'] / 60);
      $durationsecs = round(($row['File_Duration'] % 60),2);
      if (strlen($durationsecs) == 1) {
        echo "<td>$durationmins:0$durationsecs</td>";
      } else {
        echo "<td>$durationmins:$durationsecs</td>";
      }
      if ($row['File_Intro'] <> null) {
        $durationmins = floor($row['File_Intro'] / 60);
        $durationsecs = round(($row['File_Intro'] % 60),2);
        if (strlen($durationsecs) == 1) {
          echo "<td>$durationmins:0$durationsecs</td>";
        } else {
          echo "<td>$durationmins:$durationsecs</td>";
        }
      } else {
        echo "<td>Not Set</td>";
      }
      if ($row['File_Fadein'] <> null) {
        $durationmins = floor($row['File_Fadein'] / 60);
        $durationsecs = round(($row['File_Fadein'] % 60),2);
        if (strlen($durationsecs) == 1) {
          echo "<td>$durationmins:0$durationsecs</td>";
        } else {
          echo "<td>$durationmins:$durationsecs</td>";
        }
      } else {
        echo "<td>Not Set</td>";
      }
      if ($row['File_Fadeout'] <> null) {
        $durationmins = floor($row['File_Fadeout'] / 60);
        $durationsecs = round(($row['File_Fadeout'] % 60),2);
        if (strlen($durationsecs) == 1) {
          echo "<td>$durationmins:0$durationsecs</td>";
        } else {
          echo "<td>$durationmins:$durationsecs</td>";
        }
      } else {
        echo "<td>Not Set</td>";
      }
      echo "<td>" . $row['File_PlayedLast'] . "</td>";
      echo "<td><form action=\"playlist.php?option=edit\" method=\"post\" style=\"margin-bottom: 0px\"><input type=\"hidden\" name=\"playlist\" value=\"$activeplaylist\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\">";
      echo "<input type=\"hidden\" name=\"action\" value=\"remove\"><input type=\"hidden\" name=\"fileid\" value=\"" . $row['File_ID'] . "\"><input type=\"submit\" value=\"Remove\" style=\"font-size: 8pt\"";
      if (($activeshow == "0") AND ($adminaccess === false)) {
        echo " disabled";
      }
      echo "></form></td>";
      $pathinfo = pathinfo($row['File_Location']);
      $extension = $pathinfo['extension'];
      echo "<td width=\"10\"><form style=\"margin-bottom: 0px\" method=\"post\" onsubmit=\"javascript: window.open('listen.php?option=play&file=" . $row['File_ID'] . "','playoutplayer','height=150,width=350'); return false\"><input type=\"submit\" value=\"Listen\" style=\"font-size: 8pt\"";
      if (strtolower($extension) != "mp3") {
        echo " disabled";
      }
      echo "></form></td></tr>";
    } 
    echo "</table>";
  } else {
    echo "No files in the playlist.";
  }
  echo "<div id=\"searchdiv\" style=\"display: none\">";
  echo "<br /><br />Please select a file for this playlist:<br /><br />";
  echo "<form name=\"search\" id=\"search\"><input type=\"hidden\" name=\"referrer\" id=\"referrer\" value=\"playlist\"><input type=\"hidden\" name=\"show\" id=\"show\" value=\"$activeshow\">Artist: <input type=\"text\" name=\"artist\" id=\"artist\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - Title: <input type=\"text\" name=\"title\" id=\"title\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - Album: <input type=\"text\" name=\"album\" id=\"album\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - <select name=\"filetype\" id=\"filetype\" onchange=\"get(document.getElementById('search'),'','search');\">";
  $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
  $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
  $filetypenamesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_names'");
  $filetypenamesarray = unserialize(mysql_result($filetypenamesquery,0,"Setting_Value"));
  for ($i=0;$i<sizeof($filetypesarray);$i++) {
    echo "<option value=\"" . $filetypesarray[$i] . "\">" . $filetypenamesarray[$i] . "</option>";
  }
  echo "</select></form><br />";
  echo "</div>";
  echo "<div id=\"results\">";
  echo "</div><br /><br />";
  echo "<form onsubmit=\"get(document.getElementById('search'),'','search'); document.getElementById('searchdiv').style.display = 'block'; return false;\" style=\"display: inline\"><input type=\"submit\" value=\"Add File\"";
  if (($activeshow == "0") AND ($adminaccess === false)) {
    echo " disabled";
  }
  echo "></form>&nbsp;<form method=\"post\" id=\"back\" action=\"playlist.php?option=modify\" style=\"display: inline\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"playlist\" value=\"$activeplaylist\"><input type=\"submit\" value=\"Back To Playlists\"></form>";

} else if ($option == "del") {
  if (($activeshow == "0") AND ($adminaccess === false)) {
    die("Possible hacking attempt.");
  }
  echo "<b><font style=\"font-size: 10pt\">Playlist Editor</font></b><br />";
  echo "You have chosen to remove playlist '" . playlistnamefromid($activeplaylist) . "' from show '" . shownamefromid($activeshow) . "'. Files from the playlist will remain in the database but the list itself will be removed.<br /><br />Are you sure you want to continue?<br /><br />";
  echo "<form method=\"post\" action=\"playlist.php?option=modify\" style=\"display: inline\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"playlist\" value=\"$activeplaylist\"><input type=\"hidden\" name=\"action\" value=\"remove\"><input type=\"submit\" value=\"Yes\"></form>&nbsp;<form method=\"post\" style=\"display: inline\" action=\"playlist.php?option=modify\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"submit\" value=\"No\"></form>";
}
echo "</div></body></html>";
?>
