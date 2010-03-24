/**
 * Returns a list of elements of a given class and type
 */
function getElementsByClass(searchClass,tag)
{
  var classElements = new Array() ;
  if ( tag == null ) {
    tag = "*" ;
  }
  var els = document.getElementsByTagName(tag) ;
  var elsLen = els.length ;
  var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)") ;
  for (i = 0, j = 0; i < elsLen; i++) {
    if ( pattern.test(els[i].className) ) {
      classElements[j] = els[i] ;
      j++ ;
    }
  }
  return classElements;
}

function init()
{
  //This function is called by default when a page loads
  if (xmlRequest()) { //die if we can't do AJAX
    var moreLinks = getElementsByClass("ajax", "a");
    for(var i=0; i < moreLinks.length; i++) {
      if (moreLinks[i].hasAttribute("rel")) {
        moreLinks[i].setAttribute("onclick", "AJAXLoad('"+moreLinks[i].href+"','"+moreLinks[i].rel+"')");
        moreLinks[i].setAttribute("href", "#");
      }
    }
  }

}

/**
 * Loads an XML request into a target ID
 */
function AJAXLoad(contentURL, targetID)
{
  var getRequest = new xmlRequest();
  getRequest.onreadystatechange=function() {
    var targetElement = document.getElementById(targetID);
    targetElement.innerHTML = "<img src='/static/throbber.gif' alt='loading...'>";
    if (getRequest.readyState==4) {  //request has finished
      if (getRequest.status==200) {  //request was OK
        targetElement.innerHTML=getRequest.responseText;
      } else {
        targetElement.innerHTML="<p>The requested resource failed to load.</p>";
      }
    }
  }
  getRequest.open("GET", contentURL, true); //load the target
  getRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
  getRequest.send(null);
}

/**
 * Wrapper for XMLHttpRequest to cope with IE < 7
 */
function xmlRequest()
{
  if (window.XMLHttpRequest) {
    return new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    return new ActiveXObject("Microsoft.XMLHTTP");
  } else {
    return false;
  }
}