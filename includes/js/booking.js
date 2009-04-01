function checkEmail (strng) {
var error="";
if (strng == "") {
   return "";
}

    var emailFilter=/^.+@.+\..{2,3}$/;
    if (!(emailFilter.test(strng))) { 
       error = "Please enter a valid email address.\n";
    }
    else {
//test email for illegal characters
       var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/
         if (strng.match(illegalChars)) {
          error = "The email address contains illegal characters.\n";
       }
    }
return error;    
}


// phone number - strip out delimiters and check for 10 digits

function checkPhone (strng) {
var error = "";
if (strng == "") {
	return "";
}

var stripped = strng.replace(/[\(\)\.\-\ ]/g, ''); //strip out acceptable non-numeric characters
    if (isNaN(parseInt(stripped))) {
       error = "The phone number contains illegal characters.";
  
    }
    if (stripped.length < 8) {
	error = "The phone number is the wrong length. Make sure you included an area code.\n";
    } 
return error;
}

function checkDates(start, end) {
	var syear = start.substr(0,4);
	var smon = start.substr(5,2) - 1;
	var sday = start.substr(start.length-2,2);
	var eyear = end.substr(0,4);
	var emon = end.substr(5,2) - 1;
	var eday = end.substr(end.length-2,2);
	var error = "";
	
	sdate = new Date(syear,smon,sday);
	edate = new Date(eyear,emon,eday);
	//Check is dates are same;
	
	if(!(syear == sdate.getFullYear() && smon == sdate.getMonth())) {
		error = "Please enter a valid Start Date";
	}
	if(!(eyear == edate.getFullYear() && emon == edate.getMonth())) {
		error = "Please enter a valid Departure Date";
	}
	if(edate < sdate) {
		error = "Please enter a Departure date later than the Start date";
	} 
	
	return error;
}

function checkTitle(strng) {
var error = "";
  if (strng.length == 0) {
     error = "Please enter a Title.\n"
  }
return error;	  
}

function checkWholeForm(theForm) {
	
    var why = "";
    why += checkEmail(document.getElementById('renteremail').value);
    why += checkPhone(document.getElementById('rentertel').value);
    why += checkTitle(document.getElementById('bookingtitle').value);
    var start = document.getElementById('startmonth').value + '-' + document.getElementById('startday').value;
    var end = document.getElementById('endmonth').value + '-' + document.getElementById('endday').value;
    why += checkDates(start,end);
    if (why != "") {
       alert(why);
       return false;
    }
	return true;
}

