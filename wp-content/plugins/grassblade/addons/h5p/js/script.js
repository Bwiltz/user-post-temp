H5P.externalDispatcher.on('xAPI', function(event) { 
// Statement is available at: event.data.statement 

var statement =  event.data.statement;

if(ADL.XAPIWrapper.lrs.actor != undefined && typeof ADL.XAPIWrapper.lrs.actor == "string")
statement.actor = JSON.parse(ADL.XAPIWrapper.lrs.actor);

if(typeof statement.actor != "object" || typeof statement.actor.actor != "undefined" || typeof statement.object != "object"  || typeof statement.verb != "object" || typeof statement.verb.id != "string" ||  statement.verb.id == "http://adlnet.gov/expapi/verbs/interacted")
	return;
//Check if statement actor, verb and object is present or not. 

// Send the statement using xAPI Wrapper code you added earlier. 
ADL.XAPIWrapper.sendStatement(statement);
gb_get_h5p_statement(statement);
});

function gb_get_h5p_statement(data){
    var completion_statement = gb_get_completion_statement(data);
    if ((typeof(completion_statement) != "undefined") && (completion_statement != '')) {
        var targetWindow = window.parent;
        var completion_content_data = {"statement" : completion_statement};
        if (typeof(window.parent.call_grassblade_get_completion) == 'function') {
            targetWindow.call_grassblade_get_completion(completion_content_data);
        } else {
            targetWindow.postMessage(completion_content_data, targetWindow.origin);
        }
    }
}

function gb_get_completion_statement(statement){
    if (typeof(statement) != "undefined") {
        if ( ("verb" in statement) && ("object" in statement) ) {
            var verb_id = statement.verb.id;
            var completed_array = ["http://adlnet.gov/expapi/verbs/completed","http://adlnet.gov/expapi/verbs/passed","http://adlnet.gov/expapi/verbs/failed"];
            if( typeof(statement.verb.id) != "undefined" && verb_id !== null && gb_array_includes(completed_array,verb_id) ) {
                return statement;
            }
        } 
    } // end of if type of undefined
}

function gb_array_includes(container, value) {
    var returnValue = false;
    var pos = container.indexOf(value);
    if (pos >= 0) {
        returnValue = true;
    }
    return returnValue;
}

