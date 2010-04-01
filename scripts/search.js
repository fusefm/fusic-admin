   var http_request = false;
   function makePOSTRequest(url, parameters) {
      http_request = false;
      if (window.XMLHttpRequest) { // Mozilla, Safari,...
         http_request = new XMLHttpRequest();
         if (http_request.overrideMimeType) {
         	// set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            http_request.overrideMimeType('text/html');
         }
      } else if (window.ActiveXObject) { // IE
         try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
            try {
               http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
         }
      }
      if (!http_request) {
         alert('Cannot create XMLHTTP instance');
         return false;
      }
      
      http_request.onreadystatechange = alertContents;
      http_request.open('POST', url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Connection", "close");
      http_request.send(parameters);
   }

   function alertContents() {
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            //alert(http_request.responseText);
            result = http_request.responseText;
	    if (result.indexOf("<b>Please Log In</b> - <i>Using Your Site Login Details</i>") == "-1") {
                document.getElementById('results').innerHTML = result;
	    } else {
		top.location = 'logon.php';            
	    }
         } else {
            alert('There was a problem with the request.');
         }
      }
   }
   
   function get(obj, fileid, action, offset) {
      if (document.getElementById("referrer").value == "file") {
        if(document.getElementById("introcheck").checked) {
	  var introval = 1;
        } else {
      	  var introval = 0;
        }
	if(document.getElementById("musicbrainzcheck").checked) {
	  var musicbrainzval = 1;
        } else {
      	  var musicbrainzval = 0;
        }
      }
      var poststr = "artist=" + encodeURI( document.getElementById("artist").value ) +
                    "&title=" + encodeURI( document.getElementById("title").value ) +
                    "&album=" + encodeURI( document.getElementById("album").value ) +
                    "&filetype=" + encodeURI( document.getElementById("filetype").value ) +
                    "&show=" + encodeURI( document.getElementById("show").value ) +
                    "&referrer=" + encodeURI( document.getElementById("referrer").value +
                    "&intros=" + introval +
                    "&musicbrainz=" + musicbrainzval +
                    "&fileid=" + fileid +
                    "&action=" + action +
		    "&offset=" + offset );
      if (action == "edit" || action == "del" || action == "select") {
        document.getElementById("artist").disabled = true;
        document.getElementById("title").disabled = true;
        document.getElementById("album").disabled = true;
        document.getElementById("filetype").disabled = true;
	if (document.getElementById("introcheck")) {
        	document.getElementById("introcheck").disabled = true;
	}
	if (document.getElementById("musicbrainzcheck")) {
	        document.getElementById("musicbrainzcheck").disabled = true;
	}
      }
      if (action == "search") {
        document.getElementById("artist").disabled = false;
        document.getElementById("title").disabled = false;
        document.getElementById("album").disabled = false;
        document.getElementById("filetype").disabled = false;
        if (document.getElementById("introcheck")) {
        	document.getElementById("introcheck").disabled = false;
	}
	if (document.getElementById("musicbrainzcheck")) {
	        document.getElementById("musicbrainzcheck").disabled = false;
	}
      }
      makePOSTRequest('search.php', poststr);
   }
   
   function setIntro(val) {
    document.getElementById('intro').value = val;
   }
