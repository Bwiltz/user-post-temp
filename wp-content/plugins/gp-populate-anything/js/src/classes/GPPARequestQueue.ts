const $ = window.jQuery;

export default class GPPARequestQueue {

	public formId:number;
	private _requests:{ [rid: string] : JQueryPromise<any> } = {};

	constructor (formId:string) {
		this.formId = parseInt(formId);
	}

	addRequest (promise: JQueryPromise<any>) {
		const rid = new Date().getMilliseconds().toString();
		this._requests[rid] = promise;
	}

	waitForRequests () : JQueryPromise<any> {
		const requestIds:string[] = Object.keys(this._requests);
		const requestPromises = Object.values(this._requests);

		return $.when(...requestPromises).always(() : JQueryPromise<any> => {
			for (const rid of requestIds) {
				if (rid in this._requests) {
					delete this._requests[rid];
				}
			}

			if (Object.values(this._requests).length) {
				return this.waitForRequests();
			}

			return $.when();
		});
	}

}
