<?

/*
nowplaying.php
Fuse Playout System Management
Parses the log table to provide information to external sources on request.
*/

mysql_connect('localhost','drupal_live','FJ737zGN6UPdwf5D');
mysql_select_db('drupal_live');
$query = mysql_query("SELECT * FROM drup_variable WHERE `name` = 'fusefm_onair'");
if (mysql_num_rows($query) > 0) {
  $onair = unserialize(mysql_result($query,0,"value"));
} else {
  $onair = "0";
}

include('dbinfo.php');
$option = $_GET["option"];

if ($option <> "") {
  $query = mysql_query("SELECT * FROM tbl_logs ORDER BY `Log_TimePlayed` DESC LIMIT 1");
  if (mysql_num_rows($query) > 0) {
    $fileid = mysql_result($query,0,"Log_FileID");
    $artist = mysql_result($query,0,"Log_Artist");
    $title = mysql_result($query,0,"Log_Title");
    $duration = floor(mysql_result($query,0,"Log_Duration"));
    $timeplayed = mysql_result($query,0,"Log_TimePlayed");
    $musicbrainzartist = mysql_result($query,0,"Log_MusicBrainzArtist");
    $musicbrainztrack = mysql_result($query,0,"Log_MusicBrainzTrack");
    $song = $title . " by " . $artist;
  }
}

if ($option == "SRA") {
  // Only submits data when Drupal site says we're on air, to avoid dodgy twitter updates by SRA
  if ($onair == "0") {
    $artist = "";
    $title = "";
    $musicbrainzartist = "";
  }
  echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
  echo "<song>";
  echo " <now>";
  echo "  <artist>";
  echo "   <name>$artist</name>";
  echo "   <id>$musicbrainzartist</id>";
  echo "  </artist>";
  echo "  <track>$title</track>";
  echo " </now>";
  echo "</song>";
} else if (($option == "LastFM") AND (mysql_num_rows($query) > 0) AND ($onair != "0")) {
  // Submits once only per track
  $query = mysql_query("SELECT * FROM tbl_settings WHERE `Setting_Name` = 'last_scrobbled'");
  $lastscrobbled = mysql_result($query,0,"Setting_Value");
  if ($lastscrobbled != $timeplayed) {
    require('scrobbler/scrobbler.php');
    $scrobbler = new md_Scrobbler('fusefmuk', 'Fu534dm1n');
    $scrobbler->add($artist, $title, '', $duration, '', '', 'P', '', $musicbrainztrack); // Don't scrobble album, it's probably wrong
    $scrobbler->submit();
    $query = mysql_query("UPDATE tbl_settings SET `Setting_Value` = '$timeplayed' WHERE `Setting_Name` = 'last_scrobbled'");
    echo "Scrobbled $title by $artist";
  } else {
    echo "Already done that one...";
  }
} else if ($option == "IceCast") {
  // Check if the current song is part of a Fuse playlist starting with the word 'Playlist'
  $playlistarray = array();
  $playlistnamearray = array();
  $query = mysql_query("SELECT * FROM tbl_playlist WHERE `Show_ID` = '0' AND `Playlist_Name` LIKE '%Playlist%' ORDER BY `Playlist_Name`");
  for ($i=0;$i<mysql_num_rows($query);$i++) {
    if (strpos(mysql_result($query,$i,"Playlist_Name"),"Playlist") == 0) {
      $playlistarray[] = mysql_result($query,$i,"Playlist_ID");
      $playlistnamearray[] = mysql_result($query,$i,"Playlist_Name");
    }
  }
  $playlistname;
  for ($i=0;$i<sizeof($playlistarray);$i++) {
    $query = mysql_query("SELECT * FROM tbl_playlist_files WHERE `Playlist_ID` = '$playlistarray[$i]'");
    for ($j=0;$j<mysql_num_rows($query);$j++) {
      if (mysql_result($query,$j,"File_ID") == $fileid) {
        $playlistname = $playlistnamearray[$i];
        break;
      }
    }
    if ($playlistname <> "") {
      $song .= " (Fuse $playlistname)";
      break;
    }
  }

  // Submits every time it's run
  if (($song == "") OR (time() > (strtotime($timeplayed) + $duration))) {
    $song = "Manchester University's Student Radio Station";
  }

  $song .= " .:. www.fusefm.co.uk";
  echo "Submitting: " . $song;
  $song = urlencode($song);
  $mounts = array("live", "livelo", "livehi");
  foreach ($mounts as $mount) {
    $handle = fopen("http://admin:Fu534dm1n@192.168.180.108:8000/admin/metadata.xsl?song=" . $song . "&mount=%2F" . $mount . "&mode=updinfo", "r");
    if ($handle) {
      fclose($handle);
    }
  }
  foreach ($mounts as $mount) {
    $handle = fopen("http://admin:Fu534dm1n@192.168.180.113:8000/admin/metadata.xsl?song=" . $song . "&mount=%2F" . $mount . "&mode=updinfo", "r");
    if ($handle) {
      fclose($handle);
    }
  }
}

?>