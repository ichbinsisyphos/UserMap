var geocoder;
var map;
var marker = null;
var hidden;
var myPos;
var myZoom;
var kmzLink;
var ctaLayer;

function savePos() {
    myPos = map.getCenter();
    myZoom = map.getZoom();
}

function restorePos() {
    map.setCenter(myPos);
    map.setZoom(myZoom);
}

function initialize() {
  geocoder = new google.maps.Geocoder();

  hidden = true;
  if ($("#register").length > 0) {
    $("#register").hide();
  }

  map = new google.maps.Map(document.getElementById("map"));

  toggleButton();
  
  ctaLayer = new google.maps.KmlLayer({ url: kmzLink });
  ctaLayer.setMap(map);
}

function codeAddress() {
  addressInput = document.getElementById("locationInput").value;
  if (addressInput.length < 3) {
    alert("Bitte gib einen Suchbegriff mit mindestens 3 Zeichen ein.");
  }
  else {
    select = document.submitForm.locationSelect;

    for(i = select.options.length - 1; i >= 0; i--) {
      select.remove(i);
    }

    geocoder.geocode( { "address": addressInput }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        for(i = 0; i < results.length; i++) {
          addressString = results[i].formatted_address;
          lat = results[i].geometry.location.lat().toFixed(7);
          lng = results[i].geometry.location.lng().toFixed(7);
          locationString = addressString + " (" + lat + "," + lng + ")";

          option = document.createElement("option");
          option.setAttribute("value", locationString);
          option.innerHTML = locationString;
          select.options[i] = option
        }

        map.setCenter(results[0].geometry.location);

        setMarker();
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
}

function toggleHidden() {
  $("#register").toggle("slide", { direction: "left" }, 600, function() { toggleButton(); });
  hidden = !hidden;
}

function toggleButton() {
  if ($("#register").length > 0) {
    savePos();
    google.maps.event.trigger(map, "resize");
    restorePos();
    button = document.getElementById("registerButton");
    if (hidden) {
      button.value=">> bearbeiten"
    }
    else {
      button.value="<<"
    }
  }
}

function setMarker() {
  if(marker != null) {
   marker.setMap(null);
  }
  locationString = document.submitForm.locationSelect.value;

  latlngRegEx = /(\-*\d+\.\d+)\,(\-*\d+\.\d+)/;
  latlngString = latlngRegEx.exec(locationString);

  lat = parseFloat(latlngString[1]);
  lng = parseFloat(latlngString[2]);
  
  latlng = new google.maps.LatLng(lat,lng);
  map.setCenter(latlng);
  marker = new google.maps.Marker({
    map: map,
    position: latlng
  });
  map.setCenter(latlng);
  marker.setDraggable(true);

  google.maps.event.addListener(marker, "drag", function() {
    correctLocationSelect();
  });
}

function correctLocationSelect() {
  if(marker) {
    lat = marker.position.lat().toFixed(7);
    lng = marker.position.lng().toFixed(7);

    select = document.submitForm.locationSelect;
    oldLocationString = select.value;

    for(i = select.options.length-1; i > 0; i--) {
      select.remove(i);
    }

    newLocationString = oldLocationString.replace(/\(\-*\d+\.\d+\,\-*\d+\.\d+\)/, "(" + lat + "," + lng + ")");

    option = document.submitForm.locationSelect.options[0];
    option.setAttribute("value", newLocationString);
    option.innerHTML = newLocationString;
  }
}