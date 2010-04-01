<?

/*
file.php
Fuse Playout System Management
Manages files within the database. Addition, editing and deletion.
*/

//include('dbinfo.php');
//include('authenticate.php');
include('menu.php');
echo "<div style=\"position: absolute; left: 220px; width: 100%-220px; padding: 10px\">";
require_once('getid3/getid3.php');
$getID3 = new getID3;

$option = $_GET["option"];
$filetype = $_POST["filetype"];
$activeshow = $_POST["show"];
$fileid = $_POST["file"];

$action = $_POST["action"];
$artist = $_POST["artist"];
$title = $_POST["title"];
$album = $_POST["album"];
$intro = $_POST["intro"];
$fadein = $_POST["fadein"];
$fadeout = $_POST["fadeout"];
$mbartistid = $_POST["mbartist"];
$mbtrackid = $_POST["mbtrack"];

$searchartist = stripslashes($_POST["searchartist"]);
$searchtitle = stripslashes($_POST["searchtitle"]);
$searchalbum = stripslashes($_POST["searchalbum"]);
$searchfiletype = $_POST["searchfiletype"];
$searchoffset = $_POST["searchoffset"];
$searchintros = $_POST["searchintros"];
$searchmusicbrainz = $_POST["searchmusicbrainz"];

if (($activeshow == "0") AND ($adminaccess === false) AND ($option != "edit")) {
  die("Possible hacking attempt.");
}

if ($option == "add") {
  echo "<b><font style=\"font-size: 10pt\">File Uploader</font></b>";

  // Stage 1, move files (single or zip) to temp directory
  $arrayoffiles = array();
  for ($i=1;$i<6;$i++) {
    $filename = "file" . $i;
    if ($_FILES[$filename]['name']) {
      $pathinfo = pathinfo(strtolower($_FILES[$filename]['name']));
      if (($pathinfo['extension'] == "mp3") OR ($pathinfo['extension'] == "m4a") OR ($pathinfo['extension'] == "wav") OR ($pathinfo['extension'] == "flac") OR ($pathinfo['extension'] == "zip")) {
        $original = $_FILES[$filename]['name'];
        $new = $_FILES[$filename]['tmp_name'];
        $temppath = $filepath . "/temp/";
        if (!file_exists($temppath)) {
          mkdir($temppath,0777,true);
        }
        $unzippath = $filepath . "/temp/zip/";
        if (!file_exists($unzippath)) {
          mkdir($unzippath,0777,true);
        }
        $temppath = $temppath . stripslashes(basename($_FILES[$filename]['name'])); 
        if(move_uploaded_file($_FILES[$filename]['tmp_name'], $temppath)) {
          $arrayoffiles[] = $temppath;
        }
      }
    }
  }
  
  // Stage 2, unzip any zip archives
  for ($i=0;$i<sizeof($arrayoffiles);$i++) {
    $pathinfo = pathinfo(strtolower($arrayoffiles[$i]));
    if ($pathinfo['extension'] == "zip") {
      $temparray = unzipfiles($arrayoffiles[$i],$filepath);
      if ($temparray !== false) {
        for ($j=0;$j<sizeof($temparray);$j++) {
          $arrayoffiles[] = $temparray[$j];
        }
      }
    }
  }
  
  // Must now remove any duplicates or non-existant files
  $arrayoffiles = array_unique($arrayoffiles);
  /*echo "<br />";
  print_r($arrayoffiles);
  echo $activeshow . "<br />";*/
  
  // Stage 3, move files to show's directory and add them to the database
  for ($i=0;$i<sizeof($arrayoffiles);$i++) {
    $targetpath = $arrayoffiles[$i];
    $movefilesto = $filepath . "/shows/" . $activeshow . "/" . $filetype . "/";
    $pathinfo = pathinfo(strtolower($targetpath));
    if (($pathinfo['extension'] == "mp3") OR ($pathinfo['extension'] == "m4a") OR ($pathinfo['extension'] == "wav") OR ($pathinfo['extension'] == "flac")) {
      if (!file_exists($movefilesto . basename($targetpath))) {
        copy($arrayoffiles[$i], $movefilesto . basename($targetpath));
        $copiedok = true;
      } else {
        $filelocation = str_replace($filepath,$accesspath,$movefilesto . basename($targetpath));
        $numtoreplace = 4;
        $filelocation = str_replace("/","\\",$filelocation,$numtoreplace);
        $filelocation = mysql_escape_string(utf8_encode($filelocation));
        if (!mysql_ping($mysqllink)) {
          $mysqllink = dbreconnect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
        }
        $databasequery = mysql_query("SELECT File_ID FROM `tbl_files` WHERE `File_Location` = '$filelocation'");
        if (mysql_num_rows($databasequery) > 0) {
          $copiedok = false;
        } else {
          copy($arrayoffiles[$i], $movefilesto . basename($targetpath));
          $copiedok = true;
        }
      }
      if ($copiedok) {
        $tag = $getID3->analyze($targetpath);
        getid3_lib::CopyTagsToComments($tag);
        $artist = $tag['comments_html']['artist'][0];
        $title = $tag['comments_html']['title'][0];
        $album = $tag['comments_html']['album'][0];
        $duration = $tag['playtime_seconds'];              
        $filelocation = $accesspath . "\\shows\\" . $activeshow . "\\" . $filetype . "\\" . stripslashes(basename($targetpath));
        if ((($artist == "") AND ($title == "")) OR ($pathinfo['extension'] == "wav")) {
          $title = stripslashes(basename($targetpath, '.'.$pathinfo['extension']));
        }
        $artist = htmlspecialchars_decode(str_replace("'","''",$artist));
        $title = htmlspecialchars_decode(str_replace("'","''",$title));
        $album = htmlspecialchars_decode(str_replace("'","''",$album));
        if (!mysql_ping($mysqllink)) {
          $mysqllink = dbreconnect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
        }
        if (addfiletodb($artist,$title,$album,mysql_real_escape_string($filelocation),$filetype,$duration,$activeshow)) {
          echo " - <i>The file ".  stripslashes(basename($targetpath)). " has been added to the database.</i>";
        } else {
          echo " - <i>There was an error adding the file ".  stripslashes(basename($targetpath)). " to the database, please try again!</i>";
        }
      } else {
        echo " - <i>The file ".  stripslashes(basename($targetpath)). " already exists and could not be re-added.</i>";
      }
    }
  }
  
  // Stage 4, zip files already deleted, remove all other temp files
  for ($i=0;$i<sizeof($arrayoffiles);$i++) {
    if (file_exists($arrayoffiles[$i])) {
      unlink($arrayoffiles[$i]);
    }
  }
  
  echo "<br />";
  // Find out what shows a user can manage and show them in a combo box
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
      $targetpath = $filepath . "/shows/" . $row['Show_ID'] . "/";
      if (!file_exists($targetpath)) {
        mkdir($targetpath,0777,true);
      }
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
        $targetpath = $filepath . "/shows/" . $row['Show_ID'] . "/";
        if (!file_exists($targetpath)) {
          mkdir($targetpath,0777,true);
        }
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
  echo '<form action="file.php?option=add" method="post">Please select a show: <select name="show">' . $showoptionset . '</select>&nbsp;<input type="submit" value="Go / Refresh"></form><br />';
  
  if (($activeshow <> "") AND ($activeshow <> "X")) {
    $targetpath = $filepath . "/shows/" . $activeshow . "/";
    if (!file_exists($targetpath)) {
      mkdir($targetpath,0777,true);
    }
    if (!mysql_ping($mysqllink)) {
      $mysqllink = dbreconnect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
    }
    $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
    $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
    for ($i=0;$i<sizeof($filetypesarray);$i++) {
      if (!file_exists($targetpath . $filetypesarray[$i] . "/")) {
        mkdir($targetpath . $filetypesarray[$i] . "/",0777,true);
      }
    }
    if (!file_exists($filepath . "/temp/")) {
      mkdir($filepath . "/temp/",0777,true);
    }
    if (!file_exists($filepath . "/temp/zip/")) {
      mkdir($filepath . "/temp/zip/",0777,true);
    }
    $spaceused = shell_exec("du -s -k $targetpath");
    $spaceused = round(($spaceused / 1024),2);
    if ($activeshow == 0) {
      $spacetotal = round(((disk_free_space($targetpath) / 1024) / 1024),2);
    } else {
      if (!mysql_ping($mysqllink)) {
        $mysqllink = dbreconnect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
      }
      $quotaquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'presenter_quota'");
      $spacetotal = mysql_result($quotaquery,0,'Setting_Value');
    }
    echo "File extensions permitted: .mp3 .m4a .wav .flac .zip<br /><b>Note:</b> Directories and non-audio files within zip archives will be ignored.<br /><br />Quota: ";
    if ($spaceused > (0.95 * $spacetotal)) {
      echo "<font color=\"red\">";
    } else if ($spaceused > (0.75 * $spacetotal)) {
      echo "<font color=\"orange\">";
    } else {
      echo "<font color=\"green\">";
    }
    $spaceavailable = round(($spacetotal - $spaceused),2);
    echo "$spaceused MB of $spacetotal MB used</font> ($spaceavailable MB available)<br /><b>Note:</b> Max upload size (total): " . ini_get('upload_max_filesize') . "B - We advise uploading zip files within Fuse, not from home.<br /><br />";
    if ($spaceused < $spacetotal) {
      echo "<script type=\"text/javascript\">function disable() {document.getElementById(\"upload\").disabled = true; document.getElementById(\"upload\").value = \"Please Wait...\";}</script>";
      echo "<form enctype=\"multipart/form-data\" onsubmit=\"disable();\" action=\"file.php?option=add\" method=\"post\">";
      echo "File 1: <input name=\"file1\" type=\"file\" size=\"40\" /><br />";
      echo "File 2: <input name=\"file2\" type=\"file\" size=\"40\" /><br />";
      echo "File 3: <input name=\"file3\" type=\"file\" size=\"40\" /><br />";
      echo "File 4: <input name=\"file4\" type=\"file\" size=\"40\" /><br />";
      echo "File 5: <input name=\"file5\" type=\"file\" size=\"40\" /><br /><br />";
      echo "<input type=\"hidden\" name=\"show\" value=\"$activeshow\">";
      echo "File Types: <select name=\"filetype\">";
      $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
      $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
      $filetypenamesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_names'");
      $filetypenamesarray = unserialize(mysql_result($filetypenamesquery,0,"Setting_Value"));
      for ($i=0;$i<sizeof($filetypesarray);$i++) {
        echo "<option value=\"" . $filetypesarray[$i] . "\"";
        if ($searchfiletype == $filetypesarray[$i]) {
          echo " selected";
        }
        echo ">" . $filetypenamesarray[$i] . "</option>";
      }
      echo "</select> <a href=\"javascript:void(0)\" onclick=\"window.open('help.php?option=filetypes', 'helpwindow1', 'status = 1, height = 370, width = 700, resizable = 0');return false\" target=\"_blank\"><i>What are these?</i></a>";
      echo "<br /><br /><input type=\"submit\" id=\"upload\" value=\"Upload\"></form>";
    } else {
      echo "Error: Your disk usage quota has been exceeded.<br /><br />You will have to remove some old files before you can upload any more. If you believe you are receiving this message in error, please contact support.";
    }
  }
} else if ($option == "edit") {
  $getID3->setOption(array('encoding'=>$TaggingFormat));
  require_once('getid3/write.php');
  
  echo "<b><font style=\"font-size: 10pt\">File Manager</font></b>";
  
  if (($fileid <> "") AND ($action == "edit")) {
    if (($artist == "") AND ($title == "")) {
      echo " - <i>Error: At least one of 'Artist' or 'Title' must be complete. Changes not saved.</i>";
    } else {
      if (!is_numeric($fadein)) {
        $fadein = "NULL";
      }
      if (!is_numeric($fadeout)) {
        $fadeout = "NULL";
      }
      if (!is_numeric($intro)) {
        $intro = "NULL";
      }
      $query = mysql_query("UPDATE `tbl_files` SET `File_Artist` = '$artist', `File_Title` = '$title', `File_Album` = '$album', `File_Intro` = $intro, `File_Fadein` = $fadein, `File_Fadeout` = $fadeout, `File_MusicBrainzTrack` = '$mbtrackid', `File_MusicBrainzArtist` = '$mbartistid' WHERE `File_ID` = '$fileid'");
      if ($query) {
        echo " - <i>The file " . stripslashes($title) . " by " . stripslashes($artist) . " was successfully edited.";
        $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid' LIMIT 1");
        $location = mysql_result($query,0,'File_Location');
        $location = str_replace($accesspath,$filepath,$location);
        $location = str_replace("\\","/",$location);
        $pathinfo = pathinfo(strtolower($location));
        if ($pathinfo['extension'] == "mp3") {
          if (file_exists($location)) {
            $tag = $getID3->analyze($location);
            getid3_lib::CopyTagsToComments($tag);
            $getID3->setOption(array('encoding'=>$TaggingFormat));
            $tagwriter = new getid3_writetags;
            $tagwriter->filename       = $location;
            $tagwriter->overwrite_tags = true;
            $tagwriter->tagformats     = array('id3v1', 'id3v2.3');
            $tagwriter->tag_encoding   = "UTF-8"; //$TaggingFormat;
            $tagwriter->remove_other_tags = false;
            $TagData['title'][]   = utf8_encode(stripslashes($title));
            $TagData['artist'][]  = utf8_encode(stripslashes($artist));
            $TagData['album'][]   = utf8_encode(stripslashes($album));
            $TagData['year'][]    = $tag['comments']['year'][0];
            $TagData['genre'][]   = $tag['comments']['genre'][0];
            $TagData['track'][]   = $tag['comments']['track'][0];
            $tagwriter->tag_data = $TagData;
            if ($tagwriter->WriteTags()) {
              echo ' Successfully wrote file tags';
              if (!empty($tagwriter->warnings)) {
                echo ' although there were some warnings';
              }
              echo '.';
            } else {
              echo ' Failed to write file tags.';
            }
          }
        }
        echo "</i>";
      } else {
        echo " - <i>Error: The file could not be edited due to a database error.</i>";
      }
      // Don't use the below as it doesn't take into account song types which aren't S
      //$query = mysql_query("UPDATE `tbl_files` SET `File_MusicBrainzArtist` = '$mbartistid' WHERE `File_Artist` = '$artist' AND `File_Type` = 'S' AND `File_MusicBrainzArtist` = ''");      
    }
  }
  
  if (($fileid <> "") AND ($action == "del")) {
    $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid' LIMIT 1");
    if ($query) {
      $artist = mysql_result($query,0,'File_Artist');
      $title = mysql_result($query,0,'File_Title');
      $location = mysql_result($query,0,'File_Location');
      $location = str_replace($accesspath,$filepath,$location);
      $location = str_replace("\\","/",$location);
      if (file_exists($location)) {
        if (unlink($location)) {
          $query1 = mysql_query("DELETE FROM `tbl_files` WHERE `File_ID` = '$fileid'");
          $query2 = mysql_query("DELETE FROM `tbl_playlist_files` WHERE `File_ID` = '$fileid'");
          $query3 = mysql_query("UPDATE `tbl_carts` SET `File_ID` = '0', `Cart_Colour` = '255255255', `Cart_Title` = 'Empty' WHERE `File_ID` = '$fileid'");
          if (($query1) AND ($query2) AND ($query3)) {
            echo " - <i>The file $title by $artist has been deleted from the system.</i>";
          } else {
            echo " - <i>Database Error: Some traces of the file may still exist, please contact support.</i>";
          }
        } else {
          echo " - <i>The file is currently in use and cannot be deleted.</i>";
        }
      } else {
        echo " - <i>Database Error: Some traces of the file may still exist, please contact support.</i>";
      }
      
    } else {
      echo " - <i>Error: The file may already have been deleted.</i>";
    }
  }
  
  echo "<br />";
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
  echo '<form action="file.php?option=edit" method="post">Please select a show: <select name="show">' . $showoptionset . '</select>&nbsp;<input type="submit" value="Go / Refresh"></form><br />';
  if (($activeshow <> "") AND ($activeshow <> "X")) {
    echo "<script type=\"text/javascript\" src=\"scripts/search.js\"></script>";
    echo "<form name=\"search\" id=\"search\"><input type=\"hidden\" name=\"referrer\" id=\"referrer\" value=\"file\"><input type=\"hidden\" name=\"show\" id=\"show\" value=\"$activeshow\">Artist: <input type=\"text\" name=\"artist\" id=\"artist\" value=\"$searchartist\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - Title: <input type=\"text\" name=\"title\" id=\"title\" value=\"$searchtitle\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - Album: <input type=\"text\" name=\"album\" id=\"album\" value=\"$searchalbum\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - <select name=\"filetype\" id=\"filetype\" onchange=\"get(document.getElementById('search'),'','search');\">";
    $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
    $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
    $filetypenamesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_names'");
    $filetypenamesarray = unserialize(mysql_result($filetypenamesquery,0,"Setting_Value"));
    for ($i=0;$i<sizeof($filetypesarray);$i++) {
      echo "<option value=\"" . $filetypesarray[$i] . "\"";
      if ($searchfiletype == $filetypesarray[$i]) {
        echo " selected";
      }
      echo ">" . $filetypenamesarray[$i] . "</option>";
    }
    echo "</select><br /><br />Filters: <input type=\"checkbox\" name=\"introcheck\" id=\"introcheck\" onchange=\"get(document.getElementById('search'),'','search');\"";
    if ($searchintros == "1") {
      echo " checked";
    }
    echo "> Only Show Missing Intro Times - <input type=\"checkbox\" name=\"musicbrainzcheck\" id=\"musicbrainzcheck\" onchange=\"get(document.getElementById('search'),'','search');\"";
    if ($searchmusicbrainz == "1") {
      echo " checked";
    }
    echo "> Only Show Missing MusicBrainz Info</form>";
    echo "<br /><br /><div id=\"results\">Loading...</div>";
    echo "<script type=\"text/javascript\">get(document.getElementById('search'),'','search','$searchoffset');</script>";
  }
}
echo "</div></body></html>";
?>
