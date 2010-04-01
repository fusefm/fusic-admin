<?

/*
search.php
Fuse Playout System Management
AJAX search API. Returns results for use throughout the system.
*/

include('dbinfo.php');
include('authenticate.php');

$artist = $_POST["artist"];
$title = $_POST["title"];
$album = $_POST["album"];
$filetype = $_POST["filetype"];
$show = $_POST["show"];

$referrer = $_POST["referrer"];
$action = $_POST["action"];
$fileid = $_POST["fileid"];
$intros = $_POST["intros"];
$musicbrainz = $_POST["musicbrainz"];

$pages = 1;
$offset = $_POST["offset"];
if (($offset != "") AND ($offset > 0)) {
  $currentpage = $offset;
} else {
  $currentpage = 1;
}

/*
echo '<style type="text/css">
body {font-family: Arial, sans-serif;font-size: 9pt}
td {font-family: Arial, sans-serif;font-size: 9pt}
</style>';
*/
/*if (($show == "0") AND ($adminaccess === false)) {
  die("Possible hacking attempt.");
}*/

if ($action == "search") {
  if ($referrer == "file") {
    if (($intros == "1") AND ($musicbrainz == "1")) {
      $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_Intro` IS NULL AND (`File_MusicBrainzArtist` = '' OR `File_MusicBrainzTrack` = '') AND `Show_ID` = '$show' AND `File_Type` = '$filetype' AND `File_Artist` LIKE '%$artist%' AND `File_Title` LIKE '%$title%' AND `File_Album` LIKE '%$album%' ORDER BY `File_Artist`, `File_Title`");
    } else if ($intros == "1") {
      $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_Intro` IS NULL AND `Show_ID` = '$show' AND `File_Type` = '$filetype' AND `File_Artist` LIKE '%$artist%' AND `File_Title` LIKE '%$title%' AND `File_Album` LIKE '%$album%' ORDER BY `File_Artist`, `File_Title`");
    } else if ($musicbrainz == "1") {
      $query = mysql_query("SELECT * FROM `tbl_files` WHERE (`File_MusicBrainzArtist` = '' OR `File_MusicBrainzTrack` = '') AND `Show_ID` = '$show' AND `File_Type` = '$filetype' AND `File_Artist` LIKE '%$artist%' AND `File_Title` LIKE '%$title%' AND `File_Album` LIKE '%$album%' ORDER BY `File_Artist`, `File_Title`");
    } else {
      $query = mysql_query("SELECT * FROM `tbl_files` WHERE `Show_ID` = '$show' AND `File_Type` = '$filetype' AND `File_Artist` LIKE '%$artist%' AND `File_Title` LIKE '%$title%' AND `File_Album` LIKE '%$album%' ORDER BY `File_Artist`, `File_Title`");
    }
  } else {
    $query = mysql_query("SELECT * FROM `tbl_files` WHERE (`Show_ID` = '$show' OR `Show_ID` = '0') AND `File_Type` = '$filetype' AND `File_Artist` LIKE '%$artist%' AND `File_Title` LIKE '%$title%' AND `File_Album` LIKE '%$album%' ORDER BY `File_Artist`, `File_Title`");
  }
  if ($query) {
    if (mysql_num_rows($query) > 50) {
      $pages = ceil(mysql_num_rows($query) / 50);
      mysql_data_seek($query,(($currentpage - 1)*50));
    }
    if (mysql_num_rows($query) > 0) {
      echo "<table cellpadding=\"1\" border=\"1\" style=\"width: 100%; border-collapse: collapse\">";
      echo "<tr><td colspan=\"100\" align=\"center\"><form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','1');return false\" value=\"|<--\"";
      if ($currentpage == 1) {
        echo " disabled";
      }
      echo "></form> <form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','" . ($currentpage - 1) . "');return false\" value=\"<--\"";
      if ($currentpage == 1) {
        echo " disabled";
      }
      echo "></form> Page $currentpage of $pages <form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','" . ($currentpage + 1) . "');return false\" value=\"-->\"";
      if ($currentpage == $pages) {
        echo " disabled";
      }
      echo "></form> <form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','$pages');return false\" value=\"-->|\"";
      if ($currentpage == $pages) {
        echo " disabled";
      }
      echo "></form></td></tr>";
      echo "<tr style=\"font-weight: bold\"><td>Artist</td><td>Title</td><td>Album</td><td width=\"52\">Duration</td>";
      if (isfiletypesong($filetype)) {
        echo "<td width=\"45\">Intro</td><td width=\"45\">Fade In</td><td width=\"55\">Fade Out</td><td width=\"120\">Last Played</td>";
      }
      echo "<td colspan=\"3\"></td></tr>";
      $i = 0;
      while($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
        $i++;
        echo "<tr><td>";
        if ($row['File_MusicBrainzArtist'] != "" ) {
          echo "<a href=\"http://musicbrainz.org/artist/" . $row['File_MusicBrainzArtist'] . ".html\" target=\"_blank\">";
        }
        echo htmlentities($row['File_Artist']);
        if ($row['File_MusicBrainzArtist'] != "" ) {
          echo "</a>";
        }
        echo "</td><td>";
        if ($row['File_MusicBrainzTrack'] != "" ) {
          echo "<a href=\"http://musicbrainz.org/track/" . $row['File_MusicBrainzTrack'] . ".html\" target=\"_blank\">";
        }
        echo htmlentities($row['File_Title']);
        if ($row['File_MusicBrainzTrack'] != "" ) {
          echo "</a>";
        }
        echo "</td><td>" . htmlentities($row['File_Album']) . "</td>";
        $durationmins = floor($row['File_Duration'] / 60);
        $durationsecs = round(($row['File_Duration'] % 60),2);
        if (strlen($durationsecs) == 1) {
          echo "<td>$durationmins:0$durationsecs</td>";
        } else {
          echo "<td>$durationmins:$durationsecs</td>";
        }
        if (isfiletypesong($filetype)) {
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
        }
        if (($referrer == "file" AND $row['Show_ID'] == $show AND $row['Show_ID'] != "0") OR ($referrer == "file" AND $row['Show_ID'] == $show AND $adminaccess)) {
          echo "<td width=\"10\"><form onsubmit=\"get(document.getElementById('search'),'" . $row['File_ID'] . "','edit','$currentpage'); return false;\" style=\"margin-bottom: 0px\"><input type=\"submit\" id=\"edit\" value=\"Edit\" style=\"font-size: 8pt\"></form></td>";
          echo "<td width=\"10\"><form onsubmit=\"get(document.getElementById('search'),'" . $row['File_ID'] . "','del','$currentpage'); return false;\" style=\"margin-bottom: 0px\"><input type=\"submit\" id=\"del\" value=\"Delete\" style=\"font-size: 8pt\"></form></td>";
        } else if ($referrer == "file" ) {
          echo "<td width=\"10\"><form style=\"margin-bottom: 0px\"><input type=\"submit\" value=\"Edit\" style=\"font-size: 8pt\" disabled></form></td>";
          echo "<td width=\"10\"><form style=\"margin-bottom: 0px\"><input type=\"submit\" value=\"Delete\" style=\"font-size: 8pt\" disabled></form></td>";
        }
        if ($referrer == "cart") {
          echo "<td width=\"10\"><form onsubmit=\"get(document.getElementById('search'),'" . $row['File_ID'] . "','select'); return false;\" style=\"margin-bottom: 0px\"><input type=\"submit\" id=\"select\" value=\"Select\" style=\"font-size: 8pt\"></form></td>";
        }
        if ($referrer == "playlist") {
          echo "<td width=\"10\"><form action=\"playlist.php?option=edit\" method=\"post\" onsubmit=\"playlist.value = document.getElementById('back').playlist.value\" style=\"margin-bottom: 0px\"><input type=\"hidden\" name=\"show\" value=\"$show\"><input type=\"hidden\" name=\"playlist\" value=\"\"><input type=\"hidden\" name=\"action\" value=\"add\"><input type=\"hidden\" name=\"fileid\" value=\"" . $row['File_ID'] . "\"><input type=\"submit\" id=\"add\" value=\"Add\" style=\"font-size: 8pt\"></form></td>";
        }
        $pathinfo = pathinfo($row['File_Location']);
        $extension = $pathinfo['extension'];
        echo "<td width=\"10\"><form style=\"margin-bottom: 0px\" method=\"post\" onsubmit=\"javascript: window.open('listen.php?option=play&file=" . $row['File_ID'] . "','playoutplayer','height=150,width=350'); return false\"><input type=\"submit\" value=\"Listen\" style=\"font-size: 8pt\"";

        //listen.php?option=play\" target=\"_blank\"
        if (strtolower($extension) != "mp3") {
          echo " disabled";
        }
        echo "></form></td></tr>";
        if ($i == 50) {
          break;
        }
      } 
      echo "<tr><td colspan=\"100\" align=\"center\"><form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','1');return false\" value=\"|<--\"";
      if ($currentpage == 1) {
        echo " disabled";
      }
      echo "></form> <form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','" . ($currentpage - 1) . "');return false\" value=\"<--\"";
      if ($currentpage == 1) {
        echo " disabled";
      }
      echo "></form> Page $currentpage of $pages <form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','" . ($currentpage + 1) . "');return false\" value=\"-->\"";
      if ($currentpage == $pages) {
        echo " disabled";
      }
      echo "></form> <form style=\"display: inline\"><input type=\"submit\" onclick=\"get(document.getElementById('search'),'','search','$pages');return false\" value=\"-->|\"";
      if ($currentpage == $pages) {
        echo " disabled";
      }
      echo "></form></td></tr>";
      echo "</table>";
    } else {
      echo "No results found.";
    }
  }
} else if ($action == "edit") {
  if (($show == "0") AND (!$adminaccess)) {
    die("Possible hacking attempt");
  }
  $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid' LIMIT 1");
  while($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    echo "<table style=\"border: none\"><tr style=\"border: none\"><td valign=\"top\" style=\"border: none\">";
    echo "<form style=\"display: inline\" action=\"file.php?option=edit\" method=\"post\">Artist: <input type=\"text\" name=\"artist\" value=\"" . utf8_encode($row['File_Artist']) . "\"";
    if ($row['File_MusicBrainzArtist'] <> "") {
      echo " readonly";
    }
    echo "><br />";
    echo "Title: <input type=\"text\" name=\"title\" value=\"" . utf8_encode($row['File_Title']) . "\"";
    if ($row['File_MusicBrainzTrack'] <> "") {
      echo " readonly";
    }
    echo "><br />";
    echo "Album: <input type=\"text\" name=\"album\" value=\"" . utf8_encode($row['File_Album']) . "\"><br /><br />";
    if (isfiletypesong($row["File_Type"])) {
      echo "Intro (s): <input type=\"text\" name=\"intro\" id=\"intro\" value=\"" . $row['File_Intro'] . "\"><br />";
      $pathinfo = pathinfo(strtolower($row['File_Location']));
      $fileextension = $pathinfo['extension'];
      echo '<object type="application/x-shockwave-flash" data="player/introSetter.swf" width="300" height="50">
					<param name="movie" value="player/introSetter.swf" />
					<param name="bgcolor" value="#ffffff" />
					<param name="FlashVars" value="file=http://studio.fusefm.co.uk/playout/stream.mp3?file=' . $row['File_ID'] . '&format=' . $fileextension . '" />
					<param name="allowScriptAccess" value="always" />
        </object><br />';
      echo "<br />Fade In (s): <input type=\"text\" name=\"fadein\" value=\"" . $row['File_Fadein'] . "\" readonly><br />";
      echo "Fade Out (s): <input type=\"text\" name=\"fadeout\" value=\"" . $row['File_Fadeout'] . "\" readonly><br /><br />";
    }
    echo "<input type=\"hidden\" name=\"mbartist\" value=\"" . $row['File_MusicBrainzArtist'] . "\"><input type=\"hidden\" name=\"mbtrack\" value=\"" . $row['File_MusicBrainzTrack'] . "\">";
    echo "<input type=\"hidden\" name=\"searchartist\" value=\"" . stripslashes($artist) . "\"><input type=\"hidden\" name=\"searchtitle\" value=\"" . stripslashes($title) . "\"><input type=\"hidden\" name=\"searchalbum\" value=\"" . stripslashes($album) . "\"><input type=\"hidden\" name=\"searchfiletype\" value=\"$filetype\"><input type=\"hidden\" name=\"searchoffset\" value=\"$currentpage\"><input type=\"hidden\" name=\"searchintros\" value=\"$intros\"><input type=\"hidden\" name=\"searchmusicbrainz\" value=\"$musicbrainz\">";
    echo "<input type=\"hidden\" name=\"show\" value=\"$show\"><input type=\"hidden\" name=\"file\" value=\"$fileid\"><input type=\"hidden\" name=\"action\" value=\"edit\"><input type=\"submit\" value=\"Save\"></form><form onsubmit=\"get(document.getElementById('search'),'','search',$currentpage); return false;\" style=\"display: inline\"><input type=\"submit\" value=\"Cancel\"></form>";
    if ((($row['File_MusicBrainzTrack'] == "") OR ($row['File_MusicBrainzArtist'] == "")) AND (isfiletypesong($row["File_Type"]))) {
      require_once("phpbrainz/phpBrainz.class.php");
      //Create new phpBrainz object
      $phpBrainz = new phpBrainz();
      $args = array(
        "title"=>str_replace("&","and",htmlspecialchars(utf8_encode($row['File_Title']), ENT_COMPAT, 'UTF-8')),
        "artist"=>str_replace("&","and",htmlspecialchars(utf8_encode($row['File_Artist']), ENT_COMPAT, 'UTF-8'))
      );
      $trackFilter = new phpBrainz_TrackFilter($args);
      if ($trackFilter) {
        $trackResults = $phpBrainz->findTrack($trackFilter);
      }
      if ($trackResults) {
        $mbartistobj = $trackResults[0]->getArtist();
        $mbartist = $mbartistobj->getName();
        $mbtitle = $trackResults[0]->getTitle();
        $mbtrackid = $trackResults[0]->getId();
        $mbartistid = $mbartistobj->getId();
      } else {
        $mbartist = "";
        $mbtitle = "";
      }
    }
    /*
    // Now get the album
    $phpBrainz = new phpBrainz();
    $args = array(
      "title"=>$row['File_Album'],
      "artist"=>$row['File_Artist']
    );
    //print_r($args);
    $releaseFilter = new phpBrainz_ReleaseFilter($args);
    $releaseResults = $phpBrainz->findRelease($releaseFilter);
    $mbalbum = $releaseResults[0]->getTitle();
    $mbalbumid = $releaseResults[0]->getId();
    */
    if ((($row['File_MusicBrainzTrack'] == "") OR ($row['File_MusicBrainzArtist'] == "")) AND ($mbartist <> "") AND ($mbtitle <> "") AND (isfiletypesong($row["File_Type"]))) {
      echo "</td><td style=\"border: none; padding-left: 20px\">- OR -<br /><br /><br /></td><td valign=\"top\" style=\"border: none; padding-left: 20px\"><form style=\"display: inline\" action=\"file.php?option=edit\" method=\"post\">MusicBrainz would like to make the following changes:<br /><br />Artist: $mbartist<br />Title: $mbtitle<br />Artist ID: $mbartistid<br />Track ID: $mbtrackid<br />";
      echo "<input type=\"hidden\" name=\"artist\" value=\"$mbartist\">";
      echo "<input type=\"hidden\" name=\"title\" value=\"$mbtitle\">";
      echo "<input type=\"hidden\" name=\"album\" value=\"" . $row['File_Album'] . "\">";
      echo "<input type=\"hidden\" name=\"intro\" value=\"" . $row['File_Intro'] . "\">";
      echo "<input type=\"hidden\" name=\"fadein\" value=\"" . $row['File_Fadein'] . "\">";
      echo "<input type=\"hidden\" name=\"fadeout\" value=\"" . $row['File_Fadeout'] . "\">";
      echo "<input type=\"hidden\" name=\"mbartist\" value=\"$mbartistid\"><input type=\"hidden\" name=\"mbtrack\" value=\"$mbtrackid\">";
      echo "<input type=\"hidden\" name=\"searchartist\" value=\"" . stripslashes($artist) . "\"><input type=\"hidden\" name=\"searchtitle\" value=\"" . stripslashes($title) . "\"><input type=\"hidden\" name=\"searchalbum\" value=\"" . stripslashes($album) . "\"><input type=\"hidden\" name=\"searchfiletype\" value=\"$filetype\"><input type=\"hidden\" name=\"searchoffset\" value=\"$currentpage\"><input type=\"hidden\" name=\"searchintros\" value=\"$intros\"><input type=\"hidden\" name=\"searchmusicbrainz\" value=\"$musicbrainz\">";
      echo "<input type=\"hidden\" name=\"show\" value=\"$show\"><input type=\"hidden\" name=\"file\" value=\"$fileid\"><input type=\"hidden\" name=\"action\" value=\"edit\">";
      echo "<br /><input type=\"submit\" value=\"Accept Changes\"></form>";
    } else if (($row['File_MusicBrainzTrack'] == "") AND (isfiletypesong($row["File_Type"]))) {
      echo "</td><td style=\"border: none; padding-left: 20px\">- OR -<br /><br /><br /></td><td valign=\"top\" style=\"border: none; padding-left: 20px\">MusicBrainz could not identify a match for this track.<br />Please correct any errors and try again.";
    } else if ($row['File_MusicBrainzTrack'] <> "") {
      echo "</td><td style=\"border: none; padding-left: 20px\">- OR -<br /><br /><br /></td><td valign=\"top\" style=\"border: none; padding-left: 20px\">MusicBrainz tagging has already been completed for this track.<br />You will not be able to edit the artist or title fields.";
    }
    echo "</td></tr></table>";
  }
} else if ($action == "del") {
  if (($show == "0") AND (!$adminaccess)) {
    die("Possible hacking attempt");
  }
  $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid' LIMIT 1");
  while($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    echo "Are you sure you want to delete the file " . htmlentities($row['File_Title']) . " by " . htmlentities($row['File_Artist']) . "?";
    echo "<br />";
    $cartquery = mysql_query("SELECT DISTINCT `Show_ID` FROM `tbl_carts` WHERE `File_ID` = '$fileid'");
    $i = 0;
    $elsewhere = false;
    while($row = mysql_fetch_array($cartquery, MYSQL_ASSOC)) {
      if ($i == 0) {
        echo "<br />This file is currently being used in carts for the following shows:<br />";
      }
      echo shownamefromid($row['Show_ID']) . "<br />";
      $elsewhere = true;
    }
    $playlistquery = mysql_query("SELECT DISTINCT `Playlist_ID` FROM `tbl_playlist_files` WHERE `File_ID` = '$fileid'");
    $i = 0;
    while($row = mysql_fetch_array($playlistquery, MYSQL_ASSOC)) {
      if ($i == 0) {
        echo "<br />This file is currently being used in the following playlists:<br />";
      }
      $query = mysql_query("SELECT * FROM `tbl_playlist` WHERE `Playlist_ID` = '" . $row['Playlist_ID'] . "'");
      echo mysql_result($query,0,'Playlist_Name') . " - <i>(" . shownamefromid(mysql_result($query,0,'Show_ID')) . ")</i><br />";
      $elsewhere = true;
    }
    if ($elsewhere) {
      echo "<br />Deleting it will also remove it from the areas above.<br />";
    }
    echo "<br /><form style=\"display: inline\" action=\"file.php?option=edit\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"searchartist\" value=\"" . stripslashes($artist) . "\"><input type=\"hidden\" name=\"searchtitle\" value=\"" . stripslashes($title) . "\"><input type=\"hidden\" name=\"searchalbum\" value=\"" . stripslashes($album) . "\"><input type=\"hidden\" name=\"searchfiletype\" value=\"$filetype\"><input type=\"hidden\" name=\"searchoffset\" value=\"$currentpage\"><input type=\"hidden\" name=\"searchintros\" value=\"$intros\"><input type=\"hidden\" name=\"searchmusicbrainz\" value=\"$musicbrainz\">";
    echo "<input type=\"hidden\" name=\"file\" value=\"$fileid\"><input type=\"hidden\" name=\"show\" value=\"$show\"><input type=\"hidden\" name=\"action\" value=\"del\"><input type=\"submit\" value=\"I'm Sure\"></form>&nbsp;<form onsubmit=\"get(document.getElementById('search'),'','search',$currentpage); return false;\" style=\"display: inline\"><input type=\"submit\" value=\"Cancel\"></form>";
  }
} else if ($action == "select") {
  if (($show == "0") AND (!$adminaccess)) {
    die("Possible hacking attempt");
  }
  $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid' LIMIT 1");
  while($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    echo "You have selected the file " . htmlentities($row['File_Title']) . " by " . htmlentities($row['File_Artist']) . " for this cart.<form method=\"post\" id=\"result\" action=\"carts.php?option=edit\" style=\"display: inline\"><input type=\"hidden\" name=\"file\" value=\"" . $row['File_ID'] . "\"></form>";
  }
}
?>