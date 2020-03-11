var alerted = localStorage.getItem('alerted');

/* Function that returns boolean in case the browser is Internet Explorer*/
// function isIE() {
  ua = navigator.userAgent;
  /* MSIE used to detect old browsers and Trident used to newer ones*/
  var is_ie = ua.indexOf("MSIE") > -1 || ua.indexOf("Trident/") > -1;

  /* Create an alert to show if the browser is IE or not */
    if (is_ie){
        if (alerted != 'yes') {
            alert("You are using Internet Explorer to view this webpage.  Your experience may be subpar while using Internet Explorer; we recommend using an alternative internet browser, such as Chrome or Firefox, to view our website.");
            localStorage.setItem('alerted','yes');
        }
    }

//   return is_ie; 
// }


