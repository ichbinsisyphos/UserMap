
/* TODO: MENÜAUSWAHL AUCH IN COOKIE SCHREIBEN */
/* TODO: Vorschlag/Vorgabe für Beschreibung: forumlink, Ortsangabe auch im Text, etc etc */

function validateLocationString() {
  setCookie();

  returnValue = (window.document.locationForm.locationInput.value.length > 2);
  
  if(!returnValue) alert("Gib bitte mindestens 3 Zeichen als Suchbegriff ein.");
  
  return returnValue;
}

function validate() {
  setCookie();

  nameValid = (window.document.submitForm.nameInput.value != "");
  locationValid = (window.document.submitForm.locationSelect.length > 0);

  if(!nameValid)
  {
    if(!locationValid) alert("Bitte gib deinen Benutzernamen an und wähle deinen Heimatort");
    else alert("Bitte gib deinen Benutzernamen an");
  }
  else
  {
    if(!locationValid) alert("Bitte wähle einen Heimatort");
  }

  return (nameValid && locationValid);
}

function addLink() {
    startIndex = window.document.submitForm.descriptionInput.selectionStart;
    endIndex = window.document.submitForm.descriptionInput.selectionEnd;
    selectedText = window.document.submitForm.descriptionInput.value.slice(startIndex, endIndex);
    
    urlText = "";

    if(selectedText.slice(0,7) != "http://") {
      urlText = "http://";
    }

    if (startIndex == endIndex) {
      urlText += "LINKGOESHERE";
    }

    insertDescriptionFormatting('<a href="' + urlText, '">TEXTGOESHERE</a>');
}

function addBold() {
    insertDescriptionFormatting("<b>","</b>");
}

function addUnderline() {
    insertDescriptionFormatting("<u>","</u>");
}

function addItalic() {
    insertDescriptionFormatting("<i>","</i>");
}

function addStrikethrough() {
    insertDescriptionFormatting("<strike>", "</strike>");
}

function addNewLine() {
    insertDescriptionFormatting("", "<br>");
}

function insertDescriptionFormatting(open, close) {
    len = window.document.submitForm.descriptionInput.value.length;
    startIndex = window.document.submitForm.descriptionInput.selectionStart;
    endIndex = window.document.submitForm.descriptionInput.selectionEnd;
    pre = window.document.submitForm.descriptionInput.value.slice(0,startIndex);
    selected = window.document.submitForm.descriptionInput.value.slice(startIndex,endIndex);
    post = window.document.submitForm.descriptionInput.value.slice(endIndex,len);

    window.document.submitForm.descriptionInput.value = pre + open + selected + close + post;
    window.document.submitForm.descriptionInput.focus();
    window.document.submitForm.descriptionInput.selectionStart = endIndex + open.length + close.length;
    window.document.submitForm.descriptionInput.selectionEnd = endIndex + open.length + close.length;

}

function previewDescription() {
  if (this.previewWindow) {
    this.previewWindow.close();
  }

var preHTML=["<!DOCTYPE html>",
  "<html>",
    "<head>",
      '<link rel="stylesheet" type="text/css" href="preview.css" />',
      '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >',
      "<title>Description Preview</title>",
    "</head>",
    "<body>"].join("\n");

var postHTML=["</body>",
"</html>"].join("\n");

  this.previewWindow = window.open("", "Beschreibungsvorschau", "width=500,height=200");
  this.previewWindow.value="";
  html = preHTML + window.document.submitForm.descriptionInput.value + postHTML;
  this.previewWindow.document.write(html);
}

function setCookie() { //duration in Sekunden, also gegebenenfalls multiplizieren
  duration = 60000; // milliseconds -> 1 Minute
  now = new Date();
  diesAt = new Date(now.getTime() + duration);
  name = "formValues";

  userName = window.document.submitForm.nameInput.value;
  userLocation = window.document.locationForm.locationInput.value;
  userDescription = window.document.submitForm.descriptionInput.value;

  value = userName + "$&$" + userLocation + "$&$" + userDescription;
  document.cookie = name + "=" + value + ";expires=" + diesAt.toGMTString() + ";";
  
  delete now;
}

function readCookie() {
  value = "";

  if(document.cookie) {
    valueStart = document.cookie.indexOf("=") + 1;
    valueEnd = document.cookie.indexOf(";");
    if(valueEnd == -1) {
      valueEnd = document.cookie.length;
    }
    value = document.cookie.substring(valueStart, valueEnd);
    
    tempArray = value.split("$&$");

    window.document.submitForm.nameInput.value = tempArray[0];
    window.document.locationForm.locationInput.value = tempArray[1];
    window.document.submitForm.descriptionInput.value = tempArray[2];

    delete tempArray;
  }
}
