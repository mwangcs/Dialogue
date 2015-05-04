<html>
<head>
	<title>
	FoodSpeak: A Yelp Voice Search Interface
	</title>
	<link rel="stylesheet" href="microphone/microphone.min.css">
	<style>
	 button {
		background-color: Transparent;
		background-repeat:no-repeat;
		border: none;
		cursor:pointer;
		overflow: hidden;
		outline:none;
  	}
	body {
		text-align: center;
		background-image: url("wine-cheese.png");
		background-color: #FFFFFF
		-moz-background-size: cover;
		-webkit-background-size: cover;
		background-size: cover;
		background-position: top center !important;
		background-repeat: no-repeat !important;
		background-attachment: fixed;
	}
	table.center {
		margin-left: auto;
		margin-right: auto;
		width: 80%;
	}
	table.restauranttbl {
		margin-left: auto;
		margin-right: auto;
		width: 400px;
		background: rgba(255,245,200,0.6);
		border-radius: 10px;
		border-collapse: collapse;
		border-style: hidden;
	}
	tr.restaurant {
		border:3px solid white;
	}
	td.restaurantimg {
		width: 100px;
		padding: 10px;
		border-radius:5px;
	}
	td.restauranttxt {
		padding: 10px;
		vertical-align:top;
	}
	#footer {
		clear: both;
		font-size: 100%;
		text-align: center;
		bottom: 0;
		height: 30px;
	}
	</style>
</head>
<body>
	<table style="height: 50px;
				  background: #EE3333;
				  border-radius: 15px;
				  text-align: center;
				  font-size: 200%;
				  font-weight: 700;
				  color: #FFFFFF;
				  box-shadow: inset 0 -2px 1px rgba(0,0,0,0.5),
				  inset 0 2px 1px rgba(255,255,255,1);"
				  class="center">
		<tr><td><center><img src="title.png" alt="Welcome to FoodSpeak"></center></td></tr>
	</table>
	<br>
	<table style="background: transparent;
				  text-align: center;
				  border-radius: 15px;
				  padding: 15px;"
				  class="center">
	<tr><td>
	<br>
	<center><div id="microphone"></div></center>
	<pre id="result"></pre>
	<div id="info"></div>
	<div id="error"></div>
	<p> System Response: <p>
	<br><br>
	</td></tr>
	<tr><td>
	<!-- Table of Restaurants goes here -->
	<div id="response"></div>
	</td></tr>
	</table>
	<br><br>
	
	<head>
	   <style>
      #map-canvas {
        width: 800px;
        height: 400px;
      }
      .controls {
        margin-top: 16px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
      }

      #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
      }

      #pac-input:focus {
        border-color: #4d90fe;
      }

      .pac-container {
        font-family: Roboto;
      }

      #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
      }

      #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places"></script>
    <script>
      function initialize() {

		  var markers = [];
		  var map = new google.maps.Map(document.getElementById('map-canvas'), {
		    mapTypeId: google.maps.MapTypeId.ROADMAP
		  });

		  var defaultBounds = new google.maps.LatLngBounds(
		      new google.maps.LatLng(40.8054067, -73.96133),
		      new google.maps.LatLng(40.7994067, -73.93133));
		  map.fitBounds(defaultBounds);

		  // Create the search box and link it to the UI element.
		  var input = /** @type {HTMLInputElement} */(
		      document.getElementById('pac-input'));
		  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

		  var searchBox = new google.maps.places.SearchBox(
		    /** @type {HTMLInputElement} */(input));

		  // Listen for the event fired when the user selects an item from the
		  // pick list. Retrieve the matching places for that item.
		  google.maps.event.addListener(searchBox, 'places_changed', function() {
		    var places = searchBox.getPlaces();

		    if (places.length == 0) {
		      return;
		    }
		    for (var i = 0, marker; marker = markers[i]; i++) {
		      marker.setMap(null);
		    }

		    // For each place, get the icon, place name, and location.
		    markers = [];
		    var bounds = new google.maps.LatLngBounds();
		    for (var i = 0, place; place = places[i]; i++) {
		      var image = {
		        url: place.icon,
		        size: new google.maps.Size(71, 71),
		        origin: new google.maps.Point(0, 0),
		        anchor: new google.maps.Point(17, 34),
		        scaledSize: new google.maps.Size(25, 25)
		      };

		      // Create a marker for each place.
		      var marker = new google.maps.Marker({
		        map: map,
		        icon: image,
		        title: place.name,
		        position: place.geometry.location
		      });

		      markers.push(marker);

		      bounds.extend(place.geometry.location);
		    }

		    map.fitBounds(bounds);
		  });

		  // Bias the SearchBox results towards places that are within the bounds of the
		  // current map's viewport.
		  google.maps.event.addListener(map, 'bounds_changed', function() {
		    var bounds = map.getBounds();
		    searchBox.setBounds(bounds);
		  });
		}

		google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <?php
  if($_GET["mode"] == "map"){
	  echo "<body><table class=\"center\"><tr><td>";
	  echo "  <input id=\"pac-input\" class=\"controls\" type=\"text\" placeholder=\"Search Box\">";
	  echo "  <div id=\"map-canvas\"></div>";
	  echo "</td></tr></table></body>";
  }
  
  ?>
	<div id="footer" class="footer">
	<hr>
	Search powered by <a href="http://www.yelp.com" target="_blank"><img src="yelp-logo.png" height="24" width="48"></a>.  Find us on <a href="https://github.com/mwangcs/Dialogue"><img src="GitHub-Mark-32px.png" height="25" width="25" target="_blank"></a>
	</div>
	
	
	
	<script src="microphone/microphone.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	

	<script>
		var mic = new Wit.Microphone(document.getElementById("microphone"));
		var info = function (msg) {
		document.getElementById("info").innerHTML = msg;
		};
		var error = function (msg) {
		document.getElementById("error").innerHTML = msg;
		};
		var response = function (msg) {
		document.getElementById("response").innerHTML = msg;
		};
		mic.onready = function () {
		info("Microphone is ready to record");
		};
		mic.onaudiostart = function () {
		info("Recording started");
		error("");
		};
		mic.onaudioend = function () {
		info("Recording stopped, processing started");
		};
		mic.onresult = function (json, dummy) {
		var r = kv("JSON", json);

		if (toString.call(json) !== "[object String]") {
			  json= JSON.stringify(json);
		}
		$.ajax({
		  type: 'POST',
		  url: 'DM.php',
		  data: {'nlu': json},
		  success: function(msg) {
			response(msg);
		  }
		});
		};
		mic.onerror = function (err) {
		error("Error: " + err);
		};
		mic.onconnecting = function () {
		info("Microphone is connecting");
		};
		mic.ondisconnected = function () {
		info("Microphone is not connected");
		};
		mic.connect("BKDL76AFW2R6OXS7QBCECA2CDM4KKK6Z");
		function kv (k, v) {
		if (toString.call(v) !== "[object String]") {
		  v = JSON.stringify(v);
		}
		return k + "=" + v + "\n";
		}
	</script>
	</body>
</html>