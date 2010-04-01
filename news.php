<?

/*
news.php
Fuse Playout System Management
Allows direct editing of Fuse cart wall 1, button 1 for hourly news
*/

//include('dbinfo.php');
//include('authenticate.php');
include('menu.php');
echo "<div style=\"position: absolute; left: 220px; width: 100%-220px; padding: 10px\">";
require_once('getid3/getid3.php');
$getID3 = new getID3;

$option = $_GET["option"];
$filetype = "N";
$activeshow = "0";
$artist = $_POST["artist"];
$filename = "file";

if (($newsaccess) Or ($adminaccess)) {
  if ($option == "edit") {
    echo "<b><font style=\"font-size: 10pt\">News Uploader</font></b>";
  
    if (($_FILES[$filename]['name']) AND ($artist <> "")) {
      $pathinfo = pathinfo(strtolower($_FILES[$filename]['name']));
      if (($pathinfo['extension'] == "mp3") OR ($pathinfo['extension'] == "m4a") OR ($pathinfo['extension'] == "wav") OR ($pathinfo['extension'] == "flac")) {
        $original = $_FILES[$filename]['name'];
        $new = $_FILES[$filename]['tmp_name'];
        $targetpath = $filepath . "/shows/" . $activeshow . "/" . $filetype . "/";
        if (!file_exists($targetpath)) {
          mkdir($targetpath,0777,true);
        }
        $newfilename = date("m-d-H-i") . "." . $pathinfo['extension'];
        $targetpath = $targetpath . $newfilename; 
        if(!file_exists($targetpath)) {
          if(move_uploaded_file($_FILES[$filename]['tmp_name'], $targetpath)) {
            $tag = $getID3->analyze($targetpath);
            getid3_lib::CopyTagsToComments($tag);
            $title = "News Bulletin - " . date("m-d-H-i");
            $album = "";
            $duration = $tag['playtime_seconds'];
            $filelocation = $accesspath . "\\shows\\" . $activeshow . "\\" . $filetype . "\\" . $newfilename;
            //$artist = str_replace("'","''",$artist);
            if (addfiletodb($artist,$title,$album,mysql_real_escape_string($filelocation),$filetype,$duration,$activeshow)) {
              $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_Title` = '$title' ORDER BY `File_ID` DESC");
              $colour = "128000128";
              //$artist = str_replace("''","'",$artist);
              $artist = stripslashes($artist);
              $title = mysql_real_escape_string("Fuse News with " . $artist);
              $fileid = mysql_result($query,0,'File_ID');
              $refresh = "1";
              $activecart = "111";
              $query = mysql_query("UPDATE `tbl_carts` SET `Cart_Title` = '$title', `Cart_Colour` = '$colour', `File_ID` = '$fileid', `Cart_Refresh` = '$refresh' WHERE `Show_ID` = '$activeshow' AND `Cart_ID` = '$activecart'");
              if ($query) {
                echo " - <i>The file ".  stripslashes(basename($_FILES[$filename]['name'])). " has been successfully added to the database and to the cart wall.</i>";
              } else {
                echo " - <i>There was an error adding the file ".  $newfilename ." to the cart wall.</i>";
              }
            } else {
              echo " - <i>There was an error adding the file ".  stripslashes(basename($_FILES[$filename]['name'])). " to the database, please try again!</i>";
            }
          } else {
            echo " - <i>There was an error uploading the file ".  stripslashes(basename($_FILES[$filename]['name'])). ", please try again!</i>";
          }
        } else {
          echo " - <i>The file ".  $newfilename . " already exists and could not be re-added. Please wait for one minute and try again.</i>";
        }
      } else {
        echo " - <i>Files must be one of the types listed below. The file " . stripslashes(basename($_FILES[$filename]['name'])) . " was not uploaded.</i>";
      }
    } else if ($_FILES[$filename]['name']) {
      if ($artist <> "") {
        echo " - <i>No file selected for upload, please try again.</i>";
      } else {
        echo " - <i>No name entered to accompany the file, please try again.</i>";
      }
    }
  
    echo "<br />";
    // Print out the combo box selection form
  
    if ($activeshow <> "") {
      $targetpath = $filepath . "/shows/" . $activeshow . "/";
      if (!file_exists($targetpath)) {
        mkdir($targetpath,0777,true);
      }
      if (!file_exists($targetpath . "N/")) {
        mkdir($targetpath . "N/",0777,true);
      }
      $spaceused = shell_exec("du -s -k $targetpath");
      $spaceused = round(($spaceused / 1024),2);
      $spacetotal = round(((disk_free_space($targetpath) / 1024) / 1024),2);
      echo "File extensions permitted: .mp3 .m4a .wav .flac<br />Quota: ";
      if ($spaceused > (0.95 * $spacetotal)) {
        echo "<font color=\"red\">";
      } else if ($spaceused > (0.75 * $spacetotal)) {
        echo "<font color=\"orange\">";
      } else {
        echo "<font color=\"green\">";
      }
      $spaceavailable = round(($spacetotal - $spaceused),2);
      echo "$spaceused MB of $spacetotal MB used</font> ($spaceavailable MB available)<br /><br />";
      if ($spaceused < $spacetotal) {
        echo "<script type=\"text/javascript\">function disable() {document.getElementById(\"upload\").disabled = true; document.getElementById(\"upload\").value = \"Please Wait...\";}</script>";
        echo "<form enctype=\"multipart/form-data\" onsubmit=\"disable();\" action=\"news.php?option=edit\" method=\"post\">";
        echo "Your Full Name: <input type=\"text\" name=\"artist\" size=\"30\"><br />";
        echo "News File: <input name=\"file\" type=\"file\" size=\"40\" /><br />";
        echo "<input type=\"hidden\" name=\"show\" value=\"$activeshow\">";
        echo "<br /><input type=\"submit\" id=\"upload\" value=\"Upload\"></form>";
      } else {
        echo "Error: Your disk usage quota has been exceeded.<br /><br />You will have to remove some old files before you can upload any more. If you believe you are receiving this message in error, please contact support.";
      }
    }
  }
} else {
  die("Possible hacking attempt");
}
echo "</div></body></html>";
?>
