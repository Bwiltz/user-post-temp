jQuery(document).ready(function($){

	$( document ).ajaxComplete(function( event, xhr, settings ) {
		
		var questionLoaded = jQuery('ol.wpProQuiz_list > li').length;
		var totalQuestion = jQuery('.wpProQuiz_reviewQuestion li').length;
		
		if(settings.data.split("&")[0]=== "action=wp_pro_quiz_admin_ajax"){
		    // $( ".log" ).text( "Triggered ajaxComplete handler. The result is ";
			for(var i =0;i<(totalQuestion-questionLoaded);i++){
			jQuery('.wpProQuiz_reviewQuestion li:last').remove();
			} 
		}
		
		jQuery('.wpProQuiz_correct_answer').next().text(questionLoaded);
		var temp  = jQuery('.wpProQuiz_checkPage').find('p:first');
		var stemp = temp.find('span').text();

		jQuery('.wpProQuiz_checkPage > p').html('<span>'+stemp+'</span> of '+questionLoaded+' questions completed');
		jQuery('.wpProQuiz_checkPage > p:first').remove();


	})
	
});
