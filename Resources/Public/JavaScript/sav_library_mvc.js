// JavaScript Document

// PickList II script (aka Menu Swapper)- By Phil Webb (http://www.philwebb.com)
// Visit JavaScript Kit (http://www.javascriptkit.com) for this JavaScript and 100s more
// Please keep this notice intact
// Modified by Laurent Foulloy <yolf.typo3@orange.fr>

  // Compare functions
  function sortAlpha(a,b){
    return (a[1]>b[1] ? 1 : -1);
  }
  function sortByValue(a,b){
    return a[0]-b[0];
  }
  
  // Loads the selectors by removing unselected item
  function loadDoubleSelector(form, from, to) {
    var fbox=document.forms[form].elements[from];
    var tbox=document.forms[form].elements[to];
    var i;

	for(i = fbox.options.length -1 ; i >= 0; i--) {    	    	
	  if (fbox.options[i].selected) {        	
	     fbox.options.remove(i);
	     tbox.options[i].selected = false;
	  } else {
	     tbox.options.remove(i);    
	     fbox.options[i].selected = false;	        	
	  }
	}	
  }
  
  // Moves item function
  function move(form, from, to, sort) {
    var fromBox = document.forms[form].elements[from];
    var toBox = document.forms[form].elements[to];
    var i;
     
    // Checks if one option is selected
    for(i=0; i<fromBox.options.length; i++) {
      if(fromBox.options[i].selected) {
    	toBoxLength = toBox.length;
    	toBox.options[toBoxLength] = fromBox.options[i];
    	toBox.options[toBoxLength].selected = false;   	
      }
    }
  }

  // Selects all items  
  function selectAll(form, from) {
    var box = document.forms[form].elements[from];

    for(var i=0; i<box.length; i++) {
      box[i].selected = true;
    }
  } 
