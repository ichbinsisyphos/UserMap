var geocoder;
var map;
var marker = null;
var hidden;
var myPos;
var myZoom;
var kmzLink;
var markerPosition;

function savePos() {
    myPos = map.getCenter();
    myZoom = map.getZoom();
}

function restorePos() {
    map.setCenter(myPos);
    map.setZoom(myZoom);
}

function initialize() {
  googleLink = document.getElementsByTagName("a")[0].href;
  temp = googleLink.split("/maps?q=")[1];
  kmzLink = temp.split(".kmz")[0] + ".kmz";

  geocoder = new google.maps.Geocoder();
  hidden = true;
  $("#register").hide();

  var latlng = new google.maps.LatLng(47.8571,12.1181);
  var mapOptions = {
    zoom: 6,
    center: latlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  }
  map = new google.maps.Map(document.getElementById("map"), mapOptions);
  toggleButton();

  var ctaLayer = new google.maps.KmlLayer({ url: kmzLink });
  ctaLayer.setMap(map);
}

function codeAddress() {
  var address = document.getElementById("locationInput").value;
  if (address.length < 3) {
    alert("Bitte gib einen Suchbegriff mit mindestens 3 Zeichen ein.");
  }
  else {
    var select = document.submitForm.locationSelect;

    for(i = select.options.length-1; i >= 0; i--) {
      select.remove(i);
    }

    geocoder.geocode( { 'address': address }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        for(i=0;i<results.length;i++) {
          var place = results[i].formatted_address;
          var lat = results[i].geometry.location.lat().toFixed(7);
          var lng = results[i].geometry.location.lng().toFixed(7);
          var locstring = place + " (" + lat + "," + lng + ")";
          markerPosition = locstring;

          var option = document.createElement('option');
          option.setAttribute("value", locstring);
          option.innerHTML = locstring;
          select.options[i] = option
        }
        map.setCenter(results[0].geometry.location);
        // marker = new google.maps.Marker({
        //   map: map,
        //   position: results[0].geometry.location
        // });
        setMarker();
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
}

function setMarker() {
  if(marker != null) {
   marker.setMap(null);
  }
  locstring = document.submitForm.locationSelect.value;
  locarray = locstring.split("(");
  place = locarray[0];
  latlng = locarray[1].replace(")","").split(",");
  lat = parseFloat(latlng[0]);
  lng = parseFloat(latlng[1]);
  var latlng = new google.maps.LatLng(lat,lng);
  map.setCenter(latlng);
  marker = new google.maps.Marker({
    map: map,
    position: latlng
  });
  map.setCenter(latlng);
  marker.setDraggable(true);

  google.maps.event.addListener(marker, "drag", function() {
    setMarkerLatLng();
  });

  google.maps.event.addListener(marker, "dragend", function() {
    setMarkerPositionFull();
  });

}

function setMarkerLatLng() {
  if(marker) {
    lat=marker.position.lat().toFixed(7);
    lng=marker.position.lng().toFixed(7);
    document.getElementById("markerposition").value = "(" + lat + "," + lng + ")";
  }
}

function setMarkerPositionFull() {
  if(marker) {
    searchString = "(" + marker.position.lat().toFixed(7) + ",";
    searchString += marker.position.lng().toFixed(7) +")";
    geocoder.geocode( { 'address': searchString }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        for(i=0; i < results[0].address_components.length;++i) {
        if (results[0].address_components[i].types[0] == "country") {
          land=results[0].address_components[i].long_name;
        }
        if (results[0].address_components[i].types[0] == "locality") {
          stadt=results[0].address_components[i].long_name;
        }
      }
      locstring = stadt + ", " + land + " (" + lat + "," + lng + ")";
      document.getElementById("markerposition").value = locstring;
      markerPosition = locstring;

      var select = document.submitForm.locationSelect;

      for(i = select.options.length-1; i >= 0; i--) {
        select.remove(i);
      }
      var option = document.createElement('option');
      option.setAttribute("value", locstring);
      option.innerHTML = locstring;
      select.options[0] = option
      }
    });
  }
}

function toggleHidden() {
  $("#register").toggle("slide", { direction: "left" }, 600, function() { toggleButton(); });
  hidden = !hidden;
}

function toggleButton() {
  savePos();
  google.maps.event.trigger(map, "resize");
  restorePos();
  button = document.getElementById("registerButton");
  if (hidden) {
    button.value=">> eintragen"
  }
  else {
    button.value="<<            "
  }
}