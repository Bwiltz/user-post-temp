import GPPARequestQueue from "../classes/GPPARequestQueue";

const getSubmitButton = ($form: JQuery) : JQuery => {
	return $form
		.find('.gform_footer, .gform_page_footer')
		.find('input[type="submit"], input[type="button"]');
};

const toggleSubmitButton = ($form: JQuery, disabled: boolean) : void => {
	/**
	 * Disable toggling of form navigation when data is loading.
	 *
	 * @param bool disabled Return true to disable form navigation toggling. Defaults to false.
	 */
	if( gform.applyFilters( 'gppa_disable_form_navigation_toggling', false ) ) {
		return;
	}
	getSubmitButton($form).prop('disabled', disabled);
};

const disableSubmitButton = ($form: JQuery) : void => toggleSubmitButton($form, true);
const enableSubmitButton = ($form: JQuery, requestQueue: GPPARequestQueue) : void => {
	requestQueue.waitForRequests().done(() => {
		toggleSubmitButton($form, false);
	});
};

export { disableSubmitButton, enableSubmitButton };
