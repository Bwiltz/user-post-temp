import {disableSubmitButton, enableSubmitButton} from '../helpers/toggleSubmitButton';
import GPPARequestQueue from "./GPPARequestQueue";
import initTinyMCE from '../helpers/initTinyMCE';

const $ = window.jQuery;

type fieldID = number|string;

export interface fieldMapFilter {
	gf_field: string
	operator?: string
	property?: string
}

export interface fieldMap {
	[fieldId: string]: fieldMapFilter[]
}

export interface gravityViewMeta {
	search_fields: any
}

interface fieldDetails {
	field: fieldID
	filters?: fieldMapFilter[]
	$el?: JQuery
	hasChosen: boolean
}

export default class GPPopulateAnything {

	public currentPage = 1;
	public populatedFields:fieldID[] = [];
	public postedValues:{ [input: string]: string } = {};
	public gravityViewMeta?: gravityViewMeta;

	constructor(public formId: fieldID, public fieldMap: fieldMap, private _requestQueue: GPPARequestQueue) {

		if ('GPPA_POSTED_VALUES_' + formId in window) {
			this.postedValues = (window as any)['GPPA_POSTED_VALUES_' + formId];
		}

		if ('GPPA_GRAVITYVIEW_META_' + formId in window) {
			this.gravityViewMeta = (window as any)['GPPA_GRAVITYVIEW_META_' + formId];
		}

		jQuery(document).on('gform_post_render', this.postRenderSetCurrentPage);
		jQuery(document).on('gform_post_render', this.postRender);

		/**
		 * gform_post_render doesn't fire in the admin entry detail view so we'll call post render manually.
		 *
		 * Likewise for the GravityView search widget.
		 */
		if ($('#wpwrap #entry_form').length || this.gravityViewMeta) {
			this.postRender(null, formId, 0);
		}

	}

	postRenderSetCurrentPage = (event: JQueryEventObject, formId: any, currentPage: number) => {
		this.currentPage = currentPage;
	};

	postRender = (event: JQueryEventObject|null, formId: any, currentPage: number) => {

		if (formId != this.formId) {
			return;
		}

		let inputPrefix = 'input_';

		/* Bind to change. */
        // We have to target the form a little strangely as some plugins (i.e. WC GF Product Add-ons) don't use the
        // default form element.
		let $form = this.getFormElement();

		if (this.gravityViewMeta) {
			inputPrefix = 'filter_';
		}

		$form.each((_, el) => {
			const $el = $(el);

			$el.on('change', '[name^="' + inputPrefix + '"]',  ({ currentTarget: el }) => {
				const fieldId = parseInt($(el).attr('name').replace(new RegExp(`^${inputPrefix}`), ''));
				const dependentFieldIds = this.getDependentFields(fieldId);

				this.getBatchFieldHTML($el, dependentFieldIds);
			});
		});

		$form.on('submit', ({ currentTarget: form }) => {
			$(form).find('[name^="' + inputPrefix + '"]').each( (index, el: Element) => {
				var $el = $(el);
				var id = $el.attr('name').replace(inputPrefix, '');
				var fieldId = parseInt(id);

				if (this.getFieldPage(fieldId) != this.currentPage) {
					return;
				}

				this.postedValues[id] = $el.val();
			});
		});

	};

	getFieldFilterValues($form: JQuery, filters:fieldMapFilter[]) {

		let prefix = 'input_';

		if (this.gravityViewMeta) {
			prefix = 'filter_';
		}

		/* Use entry form if we're in the Gravity Forms admin entry view. */
		if ($('#wpwrap #entry_form').length) {
			$form = $('#entry_form');
		}

		const formInputValues = $form.serializeArray();
		const gfFieldFilters:string[] = [];
		const values:{ [input: string]: string } = {};

		for ( const filter of filters ) {
			gfFieldFilters.push(filter.gf_field);
		}

		for ( const input of formInputValues ) {
			const inputName = input.name.replace(prefix, '');
			const fieldId = Math.abs(parseInt(inputName)).toString();

			if (gfFieldFilters.indexOf(fieldId) === -1) {
				continue;
			}

			values[inputName] = input.value;
		}

		return values;

	}

	/**
	 * This is primarily used for field value objects since it has to traverse up
	 * and figure out what other filters are required.
	 *
	 * Regular filters work without this since all of the filters are present in the single field.
	 **/
	recursiveGetDependentFilters(filters:fieldMapFilter[]) {

		let dependentFilters:fieldMapFilter[] = [];

		for ( const filter of filters ) {
			if ('property' in filter || !('gf_field' in filter)) {
				continue;
			}

			var currentField = filter.gf_field;

			if (!(currentField in this.fieldMap)) {
				continue;
			}

			dependentFilters = dependentFilters
				.concat(this.fieldMap[currentField])
				.concat(this.recursiveGetDependentFilters(this.fieldMap[currentField]));
		}

		return dependentFilters;

	}

	getBatchFieldHTML($form: JQuery, requestedFields: { field: fieldID, filters: fieldMapFilter[] }[]) : void {

		let filters:fieldMapFilter[] = [];

		const fieldIDs:fieldID[] = [];
		const fields:fieldDetails[] = [];

		if ( !requestedFields.length ) {
			return;
		}

		/* Process field array and populate filters */
		for ( const fieldDetails of requestedFields ) {
			const fieldID = fieldDetails.field;

			if (fieldIDs.includes(fieldID)) {
				continue;
			}

			let $el = $form.find('#field_' + this.formId + '_' + fieldID);
			let hasChosen = !!$form.find('#input_' + this.formId + '_' + fieldID).data('chosen');

			if (this.gravityViewMeta) {
				const $searchBoxFilter = $form.find('#search-box-filter_' + fieldID);
				let $searchBox = $searchBoxFilter.closest('.gv-search-box');

				/* Add data attribute so we can find the element after it's replaced via AJAX. */
				if ($searchBox.length) {
					$searchBox.attr('data-gv-search-box', fieldID);
				}

				if (!$searchBox.length) {
					$searchBox = $('[data-gv-search-box="' + fieldID + '"]');
				}

				$el = $searchBox;
				hasChosen = !!$searchBox.data('chosen');
			}

			fields.push(Object.assign({}, fieldDetails, {
				$el,
				hasChosen,
			}));

			filters = filters
				.concat(fieldDetails.filters)
				.concat(this.recursiveGetDependentFilters(fieldDetails.filters));

			fieldIDs.push(fieldID);
		}

		fields.sort((a, b) => {
			const idAttrPrefix = this.gravityViewMeta ? '[id^=search-box-filter]' : '[id^=field]';

			const aIndex = a.$el!.index(idAttrPrefix);
			const bIndex = b.$el!.index(idAttrPrefix);

			return aIndex - bIndex;
		});

		$.each(fields, function (index, fieldDetails) {

			var fieldID = fieldDetails.field;
			var $el = fieldDetails.$el!;
			var $fieldContainer = $el.children('.clear-multi, .gform_hidden, .ginput_container, p').first();
			var spinnerSource = window.GPPA_GF_BASEURL + '/images/spinner.gif';

			/* Prevent multiple choices hidden inputs */
			$el
				.closest('form')
				.find('input[type="hidden"][name="choices_' + fieldID + '"]')
				.remove();

			var isEmpty  = $fieldContainer.find( '.gppa-requires-interaction' ).length > 0,
                addClass = isEmpty ? 'gppa-empty' : '';

			addClass += ' gppa-loading';

			$fieldContainer.addClass( addClass );

		});

		// Get field values after clearing out dependent fields with spinner.
		const fieldValues = this.getFieldFilterValues($form, filters);

		const data = window.gform.applyFilters('gppa_batch_field_html_ajax_data', {
			'action': 'gppa_get_batch_field_html',
			'form-id': this.formId,
			'lead-id': window.gform.applyFilters('gppa_batch_field_html_entry_id', null, this.formId),
			'field-ids': fields.map((field) => {
				return field.field;
			}),
			'gravityview-meta': this.gravityViewMeta,
			'field-values': fieldValues,
			'security': window.GPPA_NONCE,
		}, this.formId);

		disableSubmitButton(this.getFormElement());

		this._requestQueue.addRequest($.post(window.GPPA_AJAXURL, data,  (fieldHTMLResults) => {

			for ( const fieldDetails of fields ) {
				var fieldID = fieldDetails.field;
				var $field = fieldDetails.$el!;
				var $fieldContainer = $field.children('.clear-multi, .gform_hidden, .ginput_container, p').first();

				if (!this.gravityViewMeta) {
					$fieldContainer.replaceWith(fieldHTMLResults[fieldID]);
				} else {
					var $results = $(fieldHTMLResults[fieldID]);

					$fieldContainer.replaceWith($results.find('p'));
				}

				this.populatedFields.push(fieldID);

				if( fieldDetails.hasChosen ) {
					window.gformInitChosenFields( ('#input_{0}_{1}' as any).format( this.formId, fieldID ), window.GPPA_I18N.chosen_no_results );
				}

				if ( $fieldContainer.find('.wp-editor-area').length ) {
					initTinyMCE();
				}

				window.gform.doAction('gform_input_change', $fieldContainer, this.formId, fieldID);
			}

			this.runAndBindCalculationEvents();

			$(document).trigger('gppa_updated_batch_fields', this.formId);

			enableSubmitButton(this.getFormElement(), this._requestQueue);

		}, 'json'));

	}

	/**
	 * Run the calculation events for any field that is dependent on a GPPA-populated field that has been updated.
	 */
	runAndBindCalculationEvents() {

		if (!window.gf_global || !window.gf_global.gfcalc || !window.gf_global.gfcalc[this.formId]) {
			return;
		}

		var GFCalc = window.gf_global.gfcalc[this.formId];

		for (var i = 0; i < GFCalc.formulaFields.length; i++) {
			var formulaField = $.extend({}, GFCalc.formulaFields[i]);
			// @todo: Will previously bound events stack and create a performance issue?
			GFCalc.bindCalcEvents( formulaField, this.formId );
			GFCalc.runCalc(formulaField, this.formId);
		}

	}

	getFieldPage(fieldId:fieldID) {

		var $field = $('#field_' + this.formId + '_' + fieldId);
		var $page = $field.closest('.gform_page');

		if (!$page.length) {
			return 1;
		}

		return $page.prop('id').replace('gform_page_' + this.formId + '_', '');

	}

	getDependentFields(fieldId:fieldID) {

		const dependentFields = [];

		let currentFieldDependents;
		let currentFields = [fieldId.toString()];

		while (currentFields) {

			currentFieldDependents = [];

			for ( const [field, filters] of Object.entries(this.fieldMap) ) {
				for ( const filter of Object.values(filters) ) {
					if ('gf_field' in filter && currentFields.includes(filter.gf_field.toString())) {
						currentFieldDependents.push(field);
						dependentFields.push({field: field, filters: filters});
					}
				}
			}

			if (!currentFieldDependents.length) {
				break;
			}

			currentFields = currentFieldDependents;

		}

		return dependentFields;

	}

	fieldHasPostedValue(fieldId:fieldID) {

		var hasPostedField = false;

		for ( const inputId of Object.keys(this.postedValues) ) {
			const currentFieldId = parseInt(inputId);

			if (currentFieldId == fieldId) {
				hasPostedField = true;

				break;
			}
		}

		return hasPostedField;

	}

	getFormElement() {

		let $form = $( 'input[name="is_submit_' + this.formId + '"]' ).parents( 'form' );

		if ( this.gravityViewMeta ) {
			$form = $( '.gv-widget-search' );
		}

		/* Use entry form if we're in the Gravity Forms admin entry view. */
		if ( $( '#wpwrap #entry_form' ).length ) {
			$form = $( '#entry_form' );
		}

		return $form;
	}

}
