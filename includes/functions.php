<?

/*
functions.php
Fuse Playout System Management
This file defines a set of useful or frequently used functions throughout the system.
*/

// LOGON ELEMENTS - ######################################################################################

// LDAP cn search: Gets a cn from a uid. Returns cn if available, or returns error message otherwise.
function cnsearch($uid,$ldapconn,$ldapbasedn) {
  $filter = "uid=$uid";
  $justthese = array("cn");
  $search = ldap_search($ldapconn, $ldapbasedn, $filter, $justthese);
  $info = ldap_get_entries($ldapconn, $search);
  if ($info[0]["cn"]["count"] == 0) {
    return "No Results";
  } else if ($info[0]["cn"]["count"] > 1) {
    return "Too Many Results";
  } else {
    return $info[0]["cn"][0];
  }
}

// LDAP password search: Gets a user's password from a uid. Returns password in clear text if available, or returns error message otherwise.
function passwordsearch($uid,$ldapconn,$ldapbasedn) {
  $filter = "uid=$uid";
  $justthese = array("userPassword");
  $search = ldap_search($ldapconn, $ldapbasedn, $filter, $justthese);
  $info = ldap_get_entries($ldapconn, $search);
  if ($info[0]["userpassword"]["count"] == 0) {
    return "No Results";
  } else if ($info[0]["userpassword"]["count"] > 1) {
    return "Too Many Results";
  } else {
    return $info[0]["userpassword"][0];
  }
}

// LDAP group search: Checks a uid is part of a specified group. Returns true if uid is part of a group.
function groupsearch($groupname,$memberuid,$ldapconn,$ldapbasedn) {
  $filter = "cn=$groupname";
  $justthese = array("memberUid");
  $search = ldap_search($ldapconn, $ldapbasedn, $filter, $justthese);
  $info = ldap_get_entries($ldapconn, $search);
  for ($i = 0; $i < $info[0]["memberuid"]["count"]; $i++) {
    if ($info[0]["memberuid"][$i] == $memberuid) {
      return true;
    }
  }
}

// DATABASE FUNCTIONS - ######################################################################################

// Reconnect to the database on failure
function dbreconnect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb) {
  $mysqllink = mysql_connect($mysqlserver, $mysqluser, $mysqlpass);
  if ($mysqllink) {
    $db_selected = mysql_select_db($mysqldb, $mysqllink);
  }
  return $mysqllink;
}

// USER / SHOW ELEMENTS - ######################################################################################

// Get user's shows: Finds all show ID's a user can edit from their ID. Does not detect if user is an admin. Orders by show name.
function showidsfromuid($memberuid) {
  $query = mysql_query("SELECT * FROM `tbl_show_users` WHERE `User_ID` = '$memberuid'");
  if (!$query) {
    return false;
  } else {
    if (mysql_num_rows($query) > 0) {
      $querytext = "SELECT * FROM `tbl_show` WHERE ";
      $i = 0;
      while ($row = mysql_fetch_assoc($query)) {
        if ($i == 0) {
          $querytext = $querytext . "`Show_ID` = '" . $row['Show_ID'] . "'";
        } else {
          $querytext = $querytext . " OR `Show_ID` = '" . $row['Show_ID'] . "'";
        }
        $i++;
      }
      $querytext = $querytext . " ORDER BY `Show_Name`";
      $query = mysql_query("$querytext");
      if (!$query) {
        return false;
      } else {
        return $query;
      }
    } else {
      return false;
    }
  }
}

// Get shows that aren't a user's: Finds all show ID's not owned by, but editable by admins. Orders by show name.
function notshowidsfromuid($memberuid) {
  $query = mysql_query("SELECT * FROM `tbl_show_users` WHERE `User_ID` = '$memberuid'");
  if (!$query) {
    return false;
  } else {
    if (mysql_num_rows($query) > 0) {
      $querytext = "SELECT * FROM `tbl_show` WHERE ";
      $i = 0;
      while ($row = mysql_fetch_assoc($query)) {
        if ($i == 0) {
          $querytext = $querytext . "`Show_ID` != '" . $row['Show_ID'] . "'";
        } else {
          $querytext = $querytext . " AND `Show_ID` != '" . $row['Show_ID'] . "'";
        }
        $i++;
      }
      $querytext = $querytext . " AND `Show_ID` != '0' ORDER BY `Show_Name`";
      $query = mysql_query("$querytext");
      if (!$query) {
        return false;
      } else {
        return $query;
      }
    } else {
      $querytext = "SELECT * FROM `tbl_show` WHERE `Show_ID` != '0' ORDER BY `Show_Name`";
      $query = mysql_query("$querytext");
      if (!$query) {
        return false;
      } else {
        return $query;
      }
    }
  }
}

// Get a show's name from its ID
function shownamefromid($showid) {
  $query = mysql_query("SELECT * FROM `tbl_show` WHERE `Show_ID` = '$showid' LIMIT 1");
  if (!$query) {
    return "Error Loading Show Name";
  } else {
    return mysql_result($query,0,'Show_Name');
  }
}

// CART ELEMENTS - ######################################################################################

// Initalise a cart wall if it hasn't been done already, adding all the relevant fields.
function initialisewalls() {
  $query = mysql_query("SELECT * FROM `tbl_show`");
  if ($query) {
    while ($row = mysql_fetch_assoc($query)) { // Add carts to other shows if they're not there already.
      $cartquery = mysql_query("SELECT * FROM `tbl_carts` WHERE `Show_ID` = " . $row['Show_ID'] . "");
      if (mysql_num_rows($cartquery) == 0) {
        for ($i=11;$i < 16;$i++) {
          for ($j=1;$j < 9;$j++) {
            $val = ($j * 100) + ($i);
            $cartquery = mysql_query("INSERT INTO `tbl_carts` (Cart_ID, Show_ID, File_ID, Cart_Colour, Cart_Title) VALUES ('$val', " . $row['Show_ID'] . ", '0', '255255255', 'Empty')");
          }
        }
        for ($i=21;$i < 26;$i++) {
          for ($j=1;$j < 9;$j++) {
            $val = ($j * 100) + ($i);
            $cartquery = mysql_query("INSERT INTO `tbl_carts` (Cart_ID, Show_ID, File_ID, Cart_Colour, Cart_Title) VALUES ('$val', " . $row['Show_ID'] . ", '0', '255255255', 'Empty')");
          }
        }
        for ($i=31;$i < 36;$i++) {
          for ($j=1;$j < 9;$j++) {
            $val = ($j * 100) + ($i);
            $cartquery = mysql_query("INSERT INTO `tbl_carts` (Cart_ID, Show_ID, File_ID, Cart_Colour, Cart_Title) VALUES ('$val', " . $row['Show_ID'] . ", '0', '255255255', 'Empty')");
          }
        }
        for ($i=41;$i < 46;$i++) {
          for ($j=1;$j < 9;$j++) {
            $val = ($j * 100) + ($i);
            $cartquery = mysql_query("INSERT INTO `tbl_carts` (Cart_ID, Show_ID, File_ID, Cart_Colour, Cart_Title) VALUES ('$val', " . $row['Show_ID'] . ", '0', '255255255', 'Empty')");
          }
        }
      }
    }
  }
}

// Get the carts for a specific show
function getcarts($showid,$wallid) {
  $wallid = $wallid * 100;
  $walltop = $wallid + 46;
  $query = mysql_query("SELECT * FROM `tbl_carts` WHERE `Show_ID` = '$showid' AND `Cart_ID` > '$wallid' AND `Cart_ID` < '$walltop' ORDER BY `Cart_ID`");
  if (!$query) {
    return false;
  } else {
    return $query;
  }
}

// PLAYLIST ELEMENTS - ######################################################################################

// Get the playlists for a specific show
function getplaylists($showid) {
  $query = mysql_query("SELECT * FROM `tbl_playlist` WHERE `Show_ID` = '$showid' ORDER BY `Playlist_Name`");
  if (!$query) {
    return false;
  } else {
    return $query;
  }
}

// Get the file ID's present in a particular playlist
function getplaylistfiles($playlistid) {
  $query = mysql_query("SELECT * FROM `tbl_playlist_files` WHERE `Playlist_ID` = '$playlistid'");
  if (!$query) {
    return false;
  } else {
    return $query;
  }
}

// Get the files present in a particular playlist
function getplaylistfiledetails($playlistid) {
  $query = mysql_query("SELECT * FROM `tbl_playlist_files` WHERE `Playlist_ID` = '$playlistid'");
  if (!$query) {
    return false;
  } else {
    $i = 0;
    while($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
      if ($i == 0) {
        $querytext = "SELECT * FROM `tbl_files` WHERE `File_ID` = '" . $row['File_ID'] . "'";
      } else {
        $querytext = $querytext . " OR `File_ID` = '" . $row['File_ID'] . "'";
      }
      $i++;
    }
    if ($querytext) {
      $querytext = $querytext . " ORDER BY `File_Artist`";
      $query = mysql_query("$querytext");
      return $query;
    } else {
      return false;
    }
  }
}

// Get playlist name from ID
function playlistnamefromid($playlistid) {
  $query = mysql_query("SELECT * FROM `tbl_playlist` WHERE `Playlist_ID` = '$playlistid'");
  if (!$query) {
    return "Error Loading Playlist Name";
  } else {
    return mysql_result($query,0,'Playlist_Name');
  }
}

// FILE OPERATIONS - ######################################################################################

// Add an uploaded file to the database
function addfiletodb($artist,$title,$album,$path,$type,$duration,$showid) {
  //echo "\"INSERT INTO `tbl_files` (File_Location, File_Type, File_Artist, File_Title, File_Album, File_Duration, Show_ID, File_PlayedLast) VALUES ('$path', '$type', '$artist', '$title', '$album', '$duration', '$showid', '0000-00-00 00:00:00')\"<br />";
  $query = mysql_query("INSERT INTO `tbl_files` (File_Location, File_Type, File_Artist, File_Title, File_Album, File_Duration, Show_ID, File_PlayedLast) VALUES ('$path', '$type', '$artist', '$title', '$album', '$duration', '$showid', '0000-00-00 00:00:00')");
  if (!$query) {
    return false;
  } else {
    return true;
  }
}

// Unzip archive to temp directory
function unzipfiles($targetpath,$filepath) {
  $unzippedfiles = array();
  $filestodelete = array();
  /*$zip = new ZipArchive;
  $res = $zip->open($targetpath);
  $unzippedfiles = array();
  if ($res === TRUE) {
    $zip->extractTo($filepath . "/temp/zip");
    $zip->close();*/
    
  // The above works with one directory level, but errors for more
  $zip = zip_open($targetpath);
  if (is_resource($zip)) {
    while ($zip_entry = zip_read($zip)) {
      if (strpos(zip_entry_name($zip_entry),"/") === false) {
        $fp = fopen($filepath . "/temp/zip/".zip_entry_name($zip_entry), "w");
        if (zip_entry_open($zip, $zip_entry, "r")) {
          $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
          fwrite($fp,"$buf");
          zip_entry_close($zip_entry);
          fclose($fp);
        }
      }
    }
    zip_close($zip);
    if ($handle = opendir($filepath . "/temp/zip/")) {
      while (false !== ($file = readdir($handle))) {
        $pathinfo = pathinfo($file);
        if (($file != ".") AND ($file != "..") AND (($pathinfo['extension'] == "mp3") OR ($pathinfo['extension'] == "m4a") OR ($pathinfo['extension'] == "wav") OR ($pathinfo['extension'] == "flac"))) {
          $unzippedfiles[] = $file;
        } else if (($file != ".") AND ($file != "..")) {
          $filestodelete[] = $file;
        }
      }
      closedir($handle);
    }
    unlink($targetpath); // remove original zip file
    $movedfiles = array();
    for ($i=0;$i<sizeof($unzippedfiles);$i++) {
      $movedfiles[] = $filepath . "/temp/" . $unzippedfiles[$i];
      copy($filepath . "/temp/zip/" . $unzippedfiles[$i],$filepath . "/temp/" . $unzippedfiles[$i]);
      unlink($filepath . "/temp/zip/" . $unzippedfiles[$i]); // remove unzipped audio files (not others)
    }
    for ($i=0;$i<sizeof($filestodelete);$i++) {
      unlink($filepath . "/temp/zip/" . $filestodelete[$i]); // remove all other unzipped files
    }
    return $movedfiles; // Return the path to each extracted file
  } else {
    return false; // Extraction failed
  }
}

// List the files in a directory
function directorytoarray($directory, $recursive) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, directorytoarray($directory. "/" . $file, $recursive));
					}
					//$file = $directory . "/" . $file;
					//$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}

// SETTING LOOKUPS - ######################################################################################

// Check if a particular file type is a song
function isfiletypesong($filetype) {
  $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
  $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
  $filetypeissongquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_is_song'");
  $filetypeissongarray = unserialize(mysql_result($filetypeissongquery,0,"Setting_Value"));
  for ($i=0;$i<sizeof($filetypesarray);$i++) {
    if ($filetype == $filetypesarray[$i]) {
      if ($filetypeissongarray[$i] == "1") {
        return true;
      } else {
        return false;
      }
    }
  }
}

?>