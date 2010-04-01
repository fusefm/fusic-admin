<?

/*
listen.php
Fuse Playout System Management
Small audio player for previewing tracks and viewing current positions to set intro times.
*/

include('dbinfo.php');
include('authenticate.php');

$option = $_GET["option"];
$fileid = $_REQUEST["file"];

if ($option == "play") {
  if ($fileid <> "") {
    $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid'");
    
    $artist = mysql_result($query,0,'File_Artist');
    $title = mysql_result($query,0,'File_Title');
    
    echo "<title>Now Playing: ";
    echo $title;
    if ($artist != "") {
      echo ' by ' . $artist;
    }
    echo " | Fuse FM</title>";
    
    echo '<style type="text/css">
    body {font-family: Arial, sans-serif;font-size: 9pt}
    </style>';
    
    echo "<b><font style=\"font-size: 10pt\">Audio Preview</font></b><br />";
    
    echo "Now Playing: ";
    echo $title;
    if ($artist != "") {
      echo ' by ' . $artist;
    }
    echo "<br />";
    /*
    echo '<OBJECT ID="MediaPlayer" WIDTH="192" HEIGHT="190" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
    STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
    <PARAM NAME="FileName" VALUE="stream.mp3?file=' . $fileid . '">
    <PARAM name="autostart" VALUE="true">
    <PARAM name="ShowControls" VALUE="true">
    <param name="ShowStatusBar" value="false">
    <PARAM name="ShowDisplay" VALUE="false">
    <EMBED TYPE="application/x-mplayer2" SRC="stream.mp3?file=' . $fileid . '" NAME="MediaPlayer"
    WIDTH="192" HEIGHT="190" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="1"> </EMBED>
    </OBJECT>';*/

    echo '<object type="application/x-shockwave-flash" data="player/player.swf" width="300" height="25">
					<param name="movie" value="player/player.swf" />
					<param name="bgcolor" value="#ffffff" />
					<param name="FlashVars" value="mp3=stream.mp3?file=' . $fileid . '&showloading=always&autoplay=1&volume=100&showtime=2&showstop=1&showvolume=1&showslider=1&sliderwidth=0&width=300&height=25&buttonovercolor=000099" />
        </object>';
        
    echo "<br /><br /><b>Note:</b> Music played here is the property of Fuse FM or its members. Recording or copying of these files is illegal.";

  } else {
    echo "Error: No file specified.";
  }
} else {
  $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid'");
  $location = mysql_result($query,0,'File_Location');
  $location = str_replace($accesspath,$filepath,$location);
  $location = str_replace("\\","/",$location);
  $timestamp = time();
  $path_parts = pathinfo($location);
  if (file_exists($location)) {
    header('Content-type: audio/mpeg');
    header('Content-Length: '.filesize($location)); // provide file size
    header("Expires: -1");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    readfile($location);
    exit;
  }
}

?>