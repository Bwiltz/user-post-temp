/**
 * @todo Make this play nicer with multiple forms with Live Merge tags on the same page.
 */
import debounce from 'lodash.debounce';
import getFormFieldValues from '../helpers/getFormFieldValues';
import {disableSubmitButton, enableSubmitButton} from '../helpers/toggleSubmitButton';
import GPPARequestQueue from "./GPPARequestQueue";

const $ = window.jQuery;

interface ILiveMergeTagValues {
	[mergeTag: string]: string
}

export default class GPPALiveMergeTags {

	public formId:number;
	public $registeredEls!:JQuery;
	public mergeTagValuesPromise!:JQueryXHR;
	public liveAttrsOnPage:string[] = [];
	public currentMergeTagValues:ILiveMergeTagValues = {};
	private _requestQueue:GPPARequestQueue;

	constructor (formId:string, requestQueue:GPPARequestQueue) {
		this.formId = parseInt(formId);
		this._requestQueue = requestQueue;

		this.getLiveAttrs();
		this.getRegisteredEls();
		this.bind();
	}

	getLiveAttrs () {
		const prefix = 'GPPA_LIVE_ATTRS_FORM_';

		if (prefix + this.formId in window) {
			this.liveAttrsOnPage = (window as any)[prefix + this.formId];
		}
	}

	loadAndPopulateMergeTags = () => {
		this.showLoadingIndicators();

		if (this.mergeTagValuesPromise && this.mergeTagValuesPromise.state() === 'pending') {
			this.mergeTagValuesPromise.abort();
		}

		this.mergeTagValuesPromise = this.getMergeTagValues();
		this.mergeTagValuesPromise.then(this.replaceMergeTagValues);
	};

	onPageChange = () => {
		this.getRegisteredEls();

		this.loadAndPopulateMergeTags();
	};

	bind () {
		/* TODO: make sure this works with batch updates, page changes, and input changes that aren't in batch updates */
		/* TODO: Do not update merge tags that don't have an updated field */
		$(document).on('change keyup', '.gform_fields input, .gform_fields select, .gform_fields textarea', function (this: Element, event) {
			if ($(this).closest('.gfield_trigger_change').length) {
				return;
			}

			window.gf_raw_input_change(event, this);
		});

		$(document).on('gform_post_render', this.onPageChange);
		$(document).on('gppa_updated_batch_fields', this.onPageChange);

		window.gform.addAction('gform_input_change', debounce(this.loadAndPopulateMergeTags, 50), 10);

		this.getRegisteredEls();
	}

	getRegisteredEls () {
		const attributes = ['data-gppa-live-merge-tag'].concat(this.liveAttrsOnPage).map((attr) => {
			return '[' + attr + ']';
		});

		this.$registeredEls = $('#gform_wrapper_' + this.formId).find(attributes.join(','));
	}

	getRegisteredMergeTags () {
		const mergeTags:string[] = [];

		this.$registeredEls.each ((_, el: Element) => {
			const $el = $(el);

			for ( const dataAttr of ['data-gppa-live-merge-tag'].concat(this.liveAttrsOnPage) ) {
				const mergeTag = $el.attr(dataAttr);

				if (mergeTag) {
					mergeTags.push(mergeTag);
				}
			}
		});

		return mergeTags;
	}

	getMergeTagValues () : JQueryXHR {
		if (!this.$registeredEls.length) {
			return $.when() as JQueryXHR;
		}

		disableSubmitButton(this.getFormElement());

		const xhr = $.post(window.GPPA_AJAXURL, {
			'action': 'gppa_get_live_merge_tag_values',
			'form-id': this.formId,
			'field-values': getFormFieldValues(this.formId),
			'merge-tags': this.getRegisteredMergeTags(),
			'security': window.GPPA_NONCE
		}, () => {}, 'json');

		this._requestQueue.addRequest(xhr);

		return xhr;
	}

	showLoadingIndicators () {
		this.$registeredEls.each(function (this: Element) {

			var $target      = $( this ).parents( 'label, .gfield_html, .ginput_container' ).eq( 0 ),
				loadingClass = 'gppa-loading';

            /**
             * Specify which element is used to indicate that a live merge tag is about to be replaced with
             * fresh data and which element will be replaced when that data is fetched.
             *
             * @param array targetMeta
             *
             *      @var {jQuery} $target      The element that should show the loading indicator and be replaced.
             *      @var string   loadingClass The class that will be applied to the target element.
             *
             * @param {jQuery} $element The live merge tag element. By default, the live merge tag's parent element will get the loading indicator.
             * @param string   context  The context of the target meta. Will be 'loading' or 'replace'.
             */
            [ $target, loadingClass ] = window.gform.applyFilters( 'gppa_loading_target_meta', [ $target, loadingClass ], $( this ), 'loading' );

            $target.addClass( loadingClass );

		});
	}

	replaceMergeTagValues = (mergeTagValues: ILiveMergeTagValues) => {
		this.$registeredEls.each( (_, el: Element) => {
			const $el = $(el);

			if ($el.data('gppa-live-merge-tag')) {
				this.handleElementLiveContent($el, mergeTagValues);
			} else {
				this.handleElementLiveAttr($el, mergeTagValues);
			}
		});

		this.currentMergeTagValues = mergeTagValues;

		enableSubmitButton(this.getFormElement(), this._requestQueue);

		$(document).trigger('gppa_merge_tag_values_replaced', [this.formId]);

		return $.when();
	};

	handleElementLiveContent ($el: JQuery, mergeTagValues: any) {
		const elementMergeTag = $el.data('gppa-live-merge-tag');

		if (!(elementMergeTag in mergeTagValues)) {
			return;
		}

        var value       = mergeTagValues[ elementMergeTag ],
            removeClass = 'gppa-loading gppa-empty',
            $target     = $el.parents( 'label, .gfield_html, .ginput_container' ).eq( 0 );

        /** This filter is documented above. */
        [ $target, removeClass ] = window.gform.applyFilters( 'gppa_loading_target_meta', [ $target, removeClass ], $el, 'replace' );

        // Replace markup.
        $el.html(mergeTagValues[elementMergeTag]);

        var isMergeTagSpecific = $target == $el,
            isEmpty            = isMergeTagSpecific ? ! value && value !== 0 : ! $target.text(),
            addClass           = isEmpty ? 'gppa-empty' : '';

        $target.removeClass( removeClass ).addClass( addClass );

	}

	handleElementLiveAttr($el: JQuery, mergeTagValues: ILiveMergeTagValues) {
		for (const liveAttr of this.liveAttrsOnPage) {

			const elementMergeTag = $el.attr(liveAttr);
			const attr = liveAttr.replace(/^data-gppa-live-merge-tag-/, '');
			let attrVal;

			/**
			 * Special innerHtml attribute should be handled differently. innerHtml is a fake attribute utilized to replace
			 * live merge tags in <option>'s and <textarea>'s.
			 **/
			switch (attr) {
				case 'innerHtml':
					if ($el.is(':input')) {
						attrVal = $el.val();
					} else {
						attrVal = $el.html();
					}
					break;

				default:
					attrVal = $el.attr(attr);

					break;
			}

			var value       = mergeTagValues[ elementMergeTag ],
                removeClass = 'gppa-loading',
                $target     = $el.parents( 'label, .gfield_html, .ginput_container' ).eq( 0 );

            /** This filter is documented above. */
            [ $target, removeClass ] = window.gform.applyFilters( 'gppa_loading_target_meta', [ $target, removeClass ], $el, 'replace' );

			$target.removeClass( removeClass );

			if (!(elementMergeTag in mergeTagValues)) {
				continue;
			}

			if (elementMergeTag in this.currentMergeTagValues && attrVal != this.currentMergeTagValues[elementMergeTag]) {
				continue;
			}

			switch (attr) {
				case 'innerHtml':
					if ($el.is(':input')) {
						$el.val( value );
					} else {
						$el.html( value );
					}

					break;

				default:
					$el.attr(attr,  value);
					break;
			}

		}
	}

	getFormElement() {
		return $( 'input[name="is_submit_' + this.formId + '"]' ).parents( 'form' );
	}
}
