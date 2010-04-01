<?

/*
carts.php
Fuse Playout System Management
Manages cart walls within the database. File allocation, naming and colours.
*/

//include('dbinfo.php');
//include('authenticate.php');

include('menu.php');
echo "<div style=\"position: absolute; left: 220px; width: 100%-220px; padding: 10px\">";
$option = $_GET["option"];
$activeshow = $_POST["show"];
$activewall = $_POST["wall"];
if ($activewall == "") {
  $activewall = 1;
}
$activecart = $_POST["cart"];

$title = $_POST["title"];
$fileid = $_POST["file"];
$colour = $_POST["colour"];
$refresh = "0";

if (($activeshow == "0") AND ($adminaccess === false) AND ($option != "edit")) {
  die("Possible hacking attempt.");
}

if ($option == "edit") {
  if (($activeshow == "0") AND ($adminaccess === false) AND ($activecart <> "")) {
      die("Possible hacking attempt.");
  }
  echo "<b><font style=\"font-size: 10pt\">Cart Editor</font></b>";

  // Update cart
  if ($activecart <> "") {
    if ($fileid == "0") {
      $title = "{CLR}";
    }
    if ($title == "{CLR}") {
      $query = mysql_query("UPDATE `tbl_carts` SET `Cart_Title` = 'Empty', `Cart_Colour` = '255255255', `File_ID` = '0', `Cart_Refresh` = '0' WHERE `Show_ID` = '$activeshow' AND `Cart_ID` = '$activecart'");
    } else {
      $query = mysql_query("UPDATE `tbl_carts` SET `Cart_Title` = '$title', `Cart_Colour` = '$colour', `File_ID` = '$fileid', `Cart_Refresh` = '$refresh' WHERE `Show_ID` = '$activeshow' AND `Cart_ID` = '$activecart'");
    }
    if ($query) {
      if ($title == "{CLR}") {
        if ($fileid == "0") {
          echo " - <i>No file selected. Cart successfully cleared.</i>";
        } else {
          echo " - <i>Cart successfully cleared.</i>";
        }
      } else {
        echo " - <i>Cart with title '" . stripslashes($title) . "' successfully edited.</i>";
      }
    } else {
      echo " - <i>Error: Cart modification failed.</i>";
    }
  }
  
  echo "<br />";
  
  // Find out what shows a user can manage and show them in a combo box
  // May also show the Fuse wall but not allow editing by normal users... ######################## <<<<<<<<<<<<<<<
  $query = showidsfromuid($userid);
  $numberofrows = 0;
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
  // Check if the user can manage the main Fuse wall and if so add to the combo box
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
  
  // If this is a user's first login, the cart wall may not have been set up with all the relevant numbers, so do this now
  initialisewalls();
  
  // Create the 'walls' dropdown.
  for ($i=1;$i < 9;$i++) {
    if ($activewall == $i) {
      $selected = ' selected'; 
    } else {
      $selected = "";
    }
    $walloptionset = $walloptionset . '<option value="' . $i . '"' . $selected . '>Wall ' . $i . '</option>';
  }
  
  // Print out the combo box selection form
  echo '<form method="post">Please select a show and cart wall: <select name="show">' . $showoptionset . '</select>&nbsp;<select name="wall">' . $walloptionset . '</select>&nbsp;<input type="submit" value="Go / Refresh"></form><br />';
  // If a show and wall has been selected, display it for editing.
  if (($activeshow <> "") AND ($activewall <> "") AND ($activeshow <> "X")) {
    echo 'Currently viewing cart <b>Wall ' . $activewall . '</b> for show <b>\'' . shownamefromid($activeshow) . '\'</b>.<br /><br />';
    $contents = getcarts($activeshow,$activewall);
    if ($contents) {
      if (mysql_num_rows($contents) > 0) {
        echo "<table width=\"100%\" cellpadding=\"2\" cellspacing=\"0\" border=\"1\">";
        $col = 1;
        while ($row = mysql_fetch_assoc($contents)) {
          if ($col == 1) {
            echo "<tr valign=\"top\" width=\"100%\">";
          }
          $red = substr($row['Cart_Colour'],0,3);
          $green = substr($row['Cart_Colour'],3,3);
          $blue = substr($row['Cart_Colour'],6,3);
          if ($row['Cart_Colour'] == "255255255") {
            $textcolour = "000000";
          } else {
            $textcolour = "FFFFFF";
          }
          if (($activeshow == "0") AND ($row['Cart_ID'] == "111")) {
            $newscheck = " disabled";
          } elseif (($activeshow == "0") AND (!$adminaccess)) {
            $newscheck = " disabled";
          } else {
            $newscheck = "";
          }
          echo "<td valign=\"top\" width=\"20%\" style=\"color: #$textcolour; text-align: center; background-color: rgb($red,$green,$blue)\"><br /><b>" . $row['Cart_Title'] . "</b><br /><br /><form style=\"display: inline\" method=\"post\" action=\"carts.php?option=cart\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"cart\" value=\"" . $row['Cart_ID'] . "\"><input type=\"hidden\" name=\"wall\" value=\"$activewall\"><input type=\"submit\" value=\"Edit\"$newscheck>&nbsp;</form>";
          echo "<form method=\"post\" action=\"carts.php?option=edit\" style=\"display: inline\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"cart\" value=\"" . $row['Cart_ID'] . "\"><input type=\"hidden\" name=\"wall\" value=\"$activewall\"><input type=\"hidden\" name=\"title\" value=\"{CLR}\"><input type=\"submit\" value=\"Clear\"$newscheck></form>";
          if ($row['File_ID'] != "0") {
            $filequery = mysql_query("SELECT * FROM tbl_files WHERE `File_ID` = '" . $row['File_ID'] . "'");
            $pathinfo = pathinfo(mysql_result($filequery,0,'File_Location'));
            $extension = $pathinfo['extension'];
          } else {
            $extension = "false";
          }
          echo "<form style=\"display: inline;\" onsubmit=\"javascript: window.open('listen.php?option=play&file=" . $row['File_ID'] . "','playoutplayer','height=150,width=350'); return false\">&nbsp;<input type=\"submit\" value=\"Listen\"";
          if (strtolower($extension) != "mp3") {
            echo " disabled";
          }
          echo "></form><br /><br /></td>";
          $col++;
          if ($col == 6) {
            echo "</tr>";
            $col = 1;
          }
        }
        echo "</table>";
      } else {
        // Query for cart wall returned no results. Perhaps it wasn't initialised properly.
        echo "Error: Cart wall could not be loaded. Please try again or contact support.";  
      }
    } else {
      // Query didn't even succeed. Temporary error?
      echo "Error: Cart wall could not be loaded. Please try again or contact support.";
    }
  }
} else if ($option == "cart") {
  echo "<b><font style=\"font-size: 10pt\">Cart Editor</font></b><br />";
  $query = mysql_query("SELECT * FROM `tbl_carts` WHERE `Show_ID` = '$activeshow' AND `Cart_ID` = '$activecart'");
  $title = mysql_result($query,0,'Cart_Title');
  $colour = mysql_result($query,0,'Cart_Colour');
  $fileid = mysql_result($query,0,'File_ID');
  $rgbarray = array("128128128", "000000000", "255064064", "128128255", "255064255", "128000000", "192192000", "000128000", "000000128", "128000128");
  $colournamearray = array("Grey", "Black", "Light Red", "Light Blue", "Light Purple", "Dark Red", "Dark Yellow", "Dark Green", "Dark Blue", "Dark Purple");
  for ($i=0;$i < 10;$i++) {
    $red = substr($rgbarray[$i],0,3);
    $green = substr($rgbarray[$i],3,3);
    $blue = substr($rgbarray[$i],6,3);
    if ($colour == $rgbarray[$i]) {
      $selected = " selected";
    } else {
      $selected = "";
    }
    $colouroptionset = $colouroptionset . "<option style=\"color: rgb($red,$green,$blue)\" value=\"" . $rgbarray[$i] . "\"$selected>" . $colournamearray[$i] . "</option>";
  }
  echo "<script type=\"text/javascript\" src=\"scripts/search.js\"></script>";
  echo "<div id=\"searchdiv\" style=\"display: none;\">";
  echo "Please select a file for this cart:<br /><br />";
  echo "<form name=\"search\" id=\"search\"><input type=\"hidden\" name=\"referrer\" id=\"referrer\" value=\"cart\"><input type=\"hidden\" name=\"show\" id=\"show\" value=\"$activeshow\">Artist: <input type=\"text\" name=\"artist\" id=\"artist\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - Title: <input type=\"text\" name=\"title\" id=\"title\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - Album: <input type=\"text\" name=\"album\" id=\"album\" onkeyup=\"get(document.getElementById('search'),'','search');\"> - <select name=\"filetype\" id=\"filetype\" onchange=\"get(document.getElementById('search'),'','search');\">";
  $filetypesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_values'");
  $filetypesarray = unserialize(mysql_result($filetypesquery,0,"Setting_Value"));
  $filetypenamesquery = mysql_query("SELECT * FROM `tbl_settings` WHERE `Setting_Name` = 'file_type_names'");
  $filetypenamesarray = unserialize(mysql_result($filetypenamesquery,0,"Setting_Value"));
  for ($i=0;$i<sizeof($filetypesarray);$i++) {
    echo "<option value=\"" . $filetypesarray[$i] . "\">" . $filetypenamesarray[$i] . "</option>";
  }
  echo "</select></form>";
  echo "<br /></div>";
  echo "<div id=\"results\">";
  if ($fileid > 0) {
    $query = mysql_query("SELECT * FROM `tbl_files` WHERE `File_ID` = '$fileid'");
    $fileartist = mysql_result($query,0,'File_Artist');
    $filetitle = mysql_result($query,0,'File_Title');
    echo "Current File: $filetitle by $fileartist <form style=\"display: inline\" onsubmit=\"get(document.getElementById('search'),'','search'); document.getElementById('searchdiv').style.display = 'block'; return false;\"> <input type=\"submit\" value=\"Change\" style=\"font-size: 8pt\"></form>";
    echo "</div>";
  } else {
    echo "</div>";
    echo "<script type=\"text/javascript\">get(document.getElementById('search'),'','search'); document.getElementById('searchdiv').style.display = 'block';</script>";
  }
  echo "<br /><form method=\"post\" id=\"submitter\" action=\"carts.php?option=edit\" onsubmit=\"if (document.getElementById('result').file.value) { document.getElementById('submitter').file.value = document.getElementById('result').file.value; } \" style=\"display: inline\"><input type=\"hidden\" name=\"file\" value=\"$fileid\">";
  echo "Cart Title: <input type=\"text\" name=\"title\" value=\"$title\" maxlength=\"30\"><br />";
  echo "Cart Colour: <select name=\"colour\">$colouroptionset</select> <a href=\"javascript:void(0)\" onclick=\"window.open('help.php?option=colours', 'helpwindow2', 'status = 1, height = 300, width = 300, resizable = 0');return false\" target=\"_blank\"><i>What are these?</i></a><br />";
  if ($refresh == "1") {
    $checked = " checked";
  } else {
    $checked = "";
  }
  echo "<input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"wall\" value=\"$activewall\"><input type=\"hidden\" name=\"cart\" value=\"$activecart\">";
  echo "<br /><input type=\"submit\" value=\"Save\"></form>&nbsp;";
  echo "<form method=\"post\" action=\"carts.php?option=edit\" style=\"display: inline\"><input type=\"hidden\" name=\"show\" value=\"$activeshow\"><input type=\"hidden\" name=\"wall\" value=\"$activewall\"><input type=\"submit\" value=\"Cancel\"></form>";
}
echo "</div></body></html>";
?>
