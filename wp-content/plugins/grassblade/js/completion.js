(function(send) {
    XMLHttpRequest.prototype.send = function(data) {
        console_log('Intercept');
        console_log(data);
        send.call(this, data);
        var completion_statement = gb_get_completion_statement(data);
        if ((typeof(completion_statement) != "undefined") && (completion_statement != '')) {
            var targetWindow = window.parent;
            var completion_content_data = {"statement" : completion_statement};
            if(this.status == 0){
                this.addEventListener('load', function() {
                    console_log('xapi XHR-Status',this.status);
                    if (this.readyState == 4) { //will always be 4 (ajax is completed successfully)
                        call_gb_completion(targetWindow,completion_content_data);
                    }
                });
            } else {
                console_log('scorm XHR-Status',this.status);
                if (this.readyState == 4) { //will always be 4 (ajax is completed successfully)
                    call_gb_completion(targetWindow,completion_content_data);
                }
            }
        }
    };
})(XMLHttpRequest.prototype.send);

function call_gb_completion(targetWindow,completion_content_data){
    if (typeof(window.parent.call_grassblade_get_completion) == 'function') {
        targetWindow.call_grassblade_get_completion(completion_content_data);
    } else {
        targetWindow.postMessage(completion_content_data, targetWindow.origin);
    }
}

function gb_get_completion_statement(data){
    if ((typeof(data) != "undefined") && (data != null) ) {
        if ( !gb_IsJsonString(data) ) {
           data = get_statement_frm_urldata(data); 
           if (!gb_IsJsonString(data)) {
                return '';
            }
        }
        if ((typeof(data) != "undefined") && (data != null) ) {
            return grassblade_receive_statement(data);
        }
    }
}

function grassblade_receive_statement(data){

    var data = JSON.parse(data);
    if (Array.isArray(data)) {
        var statement = data[data.length-1];
    } else {
        var statement = data;
    }
    if ( (typeof statement === 'object') && ("verb" in statement) && ("object" in statement) ) {
        if (typeof(statement.verb.id) != "undefined") {
            var verb_id = statement.verb.id;
        }else{
            var verb_id = statement.verb;
        }
        var completed_array = ["http://adlnet.gov/expapi/verbs/completed","http://adlnet.gov/expapi/verbs/passed","http://adlnet.gov/expapi/verbs/failed","completed","passed","failed"];
        if( verb_id !== null && gb_array_includes(completed_array,verb_id) ) {
            console_log('Completed Statement');
            console_log(statement);
            return statement;
        }
    }
    return '';
}

function gb_IsJsonString(str){
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function gb_is_JSON(data){
    try {
        json = jQuery.parseJSON(data);
    } catch (e) {
        return false;
    }
    return true;
}

function get_statement_frm_urldata(str){
    var decoded_str = decodeURIComponent(str);
    var url_data = decoded_str.split("=");
    for (var i = 0; i < url_data.length; i++) {
        if ( gb_array_includes(url_data[i],"&content") ) {
            var url_data_str = url_data[i+1].split("&");
            return url_data_str[0];
        }
    }
}

function gb_array_includes(container, value) {
    var returnValue = false;
    var pos = container.indexOf(value);
    if (pos >= 0) {
        returnValue = true;
    }
    return returnValue;
}
function console_log(arguments) {
  if(typeof window.gbdebug != "undefined")
  console.error(arguments);
}