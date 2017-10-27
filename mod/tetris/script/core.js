// © 2009 Daniel Schulz
var NS = {};
NS.SVG = "http://www.w3.org/2000/svg";
NS.HTML = "http://www.w3.org/1999/xhtml";
NS.XLINK = "http://www.w3.org/1999/xlink";

function requestDocument(file) {
	var req = new XMLHttpRequest();
	req.open("GET", file, false);
	req.send();
	return req.responseXML;
}			
