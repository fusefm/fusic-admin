<?
include('dbinfo.php');
include('authenticate.php');
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" 
  \"http://www.w3.org/TR/html4/loose.dtd\">
<html><head><title>Playout System Management | Fuse FM</title>";
?>
<style type="text/css">
  A:link {text-decoration: none; color: #000000;}
  A:visited {text-decoration: none; color: #000000;}
  A:active {text-decoration: none; color: #000000;}
  A:hover {text-decoration: none; color: #000000;}
  body {font-family: Arial, sans-serif;font-size: 9pt}
  td {font-family: Arial, sans-serif;font-size: 9pt}
  table {
    border-width: 1px 1px 1px 1px;
    border-spacing: 0px;
    border-style: solid solid solid solid;
    border-color: black black black black;
    border-collapse: collapse;
  }
  table th {
    border-width: 1px 1px 1px 1px;
    padding: 1px 1px 1px 1px;
    border-style: solid solid solid solid;
    border-color: black black black black;
    -moz-border-radius: 0px 0px 0px 0px;
  }
  table td {
    border-width: 1px 1px 1px 1px;
    padding: 1px 1px 1px 1px;
    border-style: solid solid solid solid;
    border-color: black black black black;
    background-color: white;
    -moz-border-radius: 0px 0px 0px 0px;
  }
  </style>
</head><body>
<?
$option = $_GET['option'];
if ($option == "filetypes") {
echo "
<b>Fuse File Types</b><br />
Important Note: As part of the Fuse website 'now playing' system, all SONGS, POWER INTROS and LOOPED INTROS will be displayed in the format 'Title by Artist' in various internet locations. It is therefore critical that this information is correct and contains no notes of your own. Also note that no songs should be uploaded containing swearing. If we find any they will be removed without warning.<br />
<br />
<i>Song:</i> A full length piece of music with no interruptions from sound effects or voiceovers.<br />
<i>Power Intro:</i> A full length piece of music with the intro time taken up by a pre-produced sequence of station or show voiceovers and / or effects.<br />
<i>Looped Intro:</i> A full length piece of music with an extended intro time to allow for its use as a bed prior to the track playing.<br />
<i>Jingle:</i> A voiceover identifying the show or station including background effects or music (wet).<br />
<i>Sweeper:</i> A voiceover identifying the show or station without any background effects or music (dry).<br />
<i>Bed:</i> A piece of generally instrumental music used solely for talking over between tracks or as part of a feature.<br />
<i>Promo:</i> An advert relating directly to a part of your show or the station, promoting an upcoming event or opportunity.<br />
<i>Advertisement:</i> An advert relating to a company outside of the station played at a cost to the provider.<br />
<i>Imager:</i> A sequence of around 3 music clips, usually around 30 to 40 seconds, either beatmatched or not, including station voiceovers to profile the station's music output.<br />
<i>News Bulletin:</i> What it says on the tin. A short news broadcast to be played generally at the top of the hour.<br />
<i>Sound Effect:</i> Sirens, whooshes etc for sparing use as part of show features.<br />
<i>Interview:</i> A short (generally dry) audio clip of an interview carried out by a show with an individual or group.<br />
<i>Clip:</i> A short recording, perhaps from a TV or radio show, used as part of a show feature.<br />
<i>Other:</i> Anything other than the above.";
} else if ($option == "colours") {
echo "<b>Fuse Colour Code</b><br />
All Fuse carts will be entered as follows. You are free to use your own colour code, but to avoid confusion we recommend the following.<br />
<br />
<i>Grey:</i> Currently Unused<br />
<i>Black:</i> Currently Unused<br />
<i>Light Red:</i> Sound Effects<br />
<i>Light Blue:</i> Promos<br />
<i>Light Purple:</i> Jingles<br />
<i>Dark Red:</i> Advertisements<br />
<i>Dark Yellow:</i> Sweepers<br />
<i>Dark Green:</i> Currently Unused<br />
<i>Dark Blue:</i> Beds<br />
<i>Dark Purple:</i> News Bulletins";
}
?>
</body></html>