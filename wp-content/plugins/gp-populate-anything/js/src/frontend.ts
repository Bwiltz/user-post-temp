/* Polyfills */
import 'core-js/es/array/includes'
import 'core-js/es/object/assign'
import 'core-js/es/object/values'
import 'core-js/es/object/entries'

import GPPopulateAnything, {fieldMap} from './classes/GPPopulateAnything';
import GPPALiveMergeTags from './classes/GPPALiveMergeTags';
import GPPARequestQueue from './classes/GPPARequestQueue';
import deepmerge from 'deepmerge';

const gppaMergedFieldMaps:{ [formId: string]: fieldMap } = {};

window.gppaRequestQueues = {};
window.gppaForms = {};
window.gppaLiveMergeTags = {};

/**
 * Instantiate request queue class for each form. This will be shared between numerous classes such as
 * GPPopulateAnything and GPPALiveMereTags.
 *
 * @param formId
 */
const addRequestQueue = (formId: string) => {
	if (!(formId in window.gppaRequestQueues)) {
		window.gppaRequestQueues[formId] = new GPPARequestQueue(formId);
	}

	return window.gppaRequestQueues[formId];
};

for( const prop in window ) {
	if ( window.hasOwnProperty( prop ) &&
		( prop.indexOf( 'GPPA_FILTER_FIELD_MAP' ) === 0 || prop.indexOf( 'GPPA_FIELD_VALUE_OBJECT_MAP' ) === 0 )
	) {
		const formId = prop.split('_').pop() as string;
		const map = (window as any)[ prop ];

		if ( !(formId in gppaMergedFieldMaps) ) {
			gppaMergedFieldMaps[formId] = {};
		}

		gppaMergedFieldMaps[formId] = deepmerge(gppaMergedFieldMaps[formId], map[formId]);
	}
}

for ( const [formId, fieldMap] of Object.entries(gppaMergedFieldMaps) ) {
	window.gppaForms[formId] = new GPPopulateAnything(formId, fieldMap, addRequestQueue(formId));
}

window.jQuery(document).on('gform_post_render', (_:Event, formId:string) => {
	if (!(formId in window.gppaLiveMergeTags)) {
		window.gppaLiveMergeTags[formId] = new GPPALiveMergeTags(formId, addRequestQueue(formId));
	}
});
