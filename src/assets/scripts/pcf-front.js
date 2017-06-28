(function(window) {

	window.PFC_Form = (function( params ) {

		/***************************************/
		/************* INITIALIZE **************/
		/***************************************/

		var Class = function( params ) {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// SETUP VARIABLES FROM USER-DEFINED PARAMETERS

			/* Variable:  formID                               */
			/* Type:      String                               */
			/* Default:   N/A (Required)                       */
			/* Purpose:   This string is the ID of the HTML    */
			/*            element which is hardcoded on the    */
			/*            page and will become the wrapper.    */
			var formID = 'formID' in params ? params.formID : '';
			if ( formID == null ) {
				return false;
			}

			/* Variable:  submitButtonID                       */
			/* Type:      String                               */
			/* Default:   N/A (Required)                       */
			/* Purpose:   This string is the ID of the HTML    */
			/*            element which is hardcoded on the    */
			/*            page and will become the wrapper.    */
			var submitButtonID = 'submitButtonID' in params ? params.submitButtonID : '';

			// SET UP TOP LEVEL GLOBAL VARIABLES TO MANAGE EVERYTHING

			/* Variable:  el                                   */
			/* Type:      Object                               */
			/* Purpose:   This is the object containing all    */
			/*            relevant HTML elements of the form.  */
			self.el = {};	

			// STORE HTML FORM CONTROL ELEMENTS

			self.el.form = document.getElementById(formID);
			self.el.button = document.getElementById(submitButtonID);

			// CHANGE FORM TO AJAX ENDPOINTS SINCE WE HAVE WORKING JS

			var ajaxAction = self.el.form.getAttribute('ajax-action');
			self.el.form.setAttribute('action', ajaxAction);

			self.el.form.querySelector('input[name="action"]').value = 'pcf_send_email_ajax';

			// WATCH FOR FORM SUBMISSION AND CHECK VALIDITY BEFORE PROCEEDING

			self.el.form.addEventListener('submit', function( event ) {

				event.preventDefault();

				if ( window.grecaptcha ) {

					self.el.form.classList.add('pcf_reCaptcha');

					window.setTimeout(function() {

						window.grecaptcha.execute();

					}, 2000 );

				} else {

					self.submitViaAjax();

				}

			}, true);

		};

		/***************************************/
		/********** PUBLIC FUNCTIONS ***********/
		/***************************************/

		Class.prototype.submitViaAjax = function() {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// VISUALLY CHANGE FORM TO SUBMITTING

			self.el.form.classList.remove('pcf_reCaptcha');
			self.el.form.classList.add('pcf_submitting');

			// SUBMIT THE FORM VIA AJAX

			window.setTimeout(function(){

				pcf_ajax({
					method: self.el.form.getAttribute('method'),
					queryURL: self.el.form.getAttribute('action'),
					data: self.el.form.pcf_serialize(),
					success: function(serverResponse) {
						serverResponse = JSON.parse(serverResponse);
						self.respondToUser({
							success: serverResponse.success,
						});
					},
					error: function(serverResponse) {
						self.respondToUser({
							response: 'There was a problem connecting to the server. Wait a moment, and then you can try to submit again if you\'d like to.',
						});
					}
				});

			}, 500 );

		};

		Class.prototype.respondToUser = function( params ) {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// SETUP VARIABLES

			var success = 'success' in params ? params.success : false;
			var response = 'response' in params ? params.response : ( success ? 'Your message has been sent, thanks!' : 'Your message hasn\'t been sent, due to a server error. Please wait a few minutes and try again. We are very sorry for the inconvenience.' );

			// UPDATE THE FORM VISUALLY TO RESPOND TO THE USER

			setTimeout(function(){

				self.updateVisibleStatus({
					success: success,
					response: response
				});

				if ( success ) {

					self.el.form.reset();

				}

				setTimeout(function(){

					self.resetVisibleStatus();
					
				}, 5000);

			}, 1000);

		};

		Class.prototype.resetVisibleStatus = function() {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// VISUALLY RESET THE FORM BACK TO BEGINNING

			self.el.form.classList.remove('pcf_error');
			self.el.form.classList.remove('pcf_success');

		};

		Class.prototype.updateVisibleStatus = function( params ) {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// SETUP VARIABLES FROM USER-DEFINED PARAMETERS

			var success = 'success' in params ? params.success : false;
			var response = 'response' in params ? params.response : '';

			// EDIT THE CLASSLIST OF THE FORM TO CHANGE THE COLOR AND ICON SHOWN TO USER

			if ( success == true ) {

				self.el.form.classList.add('pcf_success');
				self.el.form.classList.remove('pcf_submitting');
				self.el.form.classList.remove('pcf_reCaptcha');
				self.el.button.innerText = 'Send Another Message';

			} else {

				self.el.form.classList.add('pcf_error');
				self.el.form.classList.remove('pcf_submitting');
				self.el.form.classList.remove('pcf_reCaptcha');

			}

		};

		return Class;

		/***************************************/
		/********** PRIVATE FUNCTIONS **********/
		/***************************************/

		function pcf_ajax( params ) {

			var method = 'method' in params ? params['method'] : 'get';
			var queryURL = 'queryURL' in params ? params['queryURL'] : '';
			var data = 'data' in params ? params['data'] : '';
			var successCallback = 'success' in params ? params['success'] : function(params){console.log('Successfully completed AJAX request.')};
			var errorCallback = 'error' in params ? params['error'] : function(params){console.log('Error during AJAX request.');};
			var ajaxRequest = new XMLHttpRequest();

			ajaxRequest.onreadystatechange = function () {
				if ( ajaxRequest.readyState === 4 ) {
					if ( ajaxRequest.status === 200 ) {
						successCallback(ajaxRequest.responseText, ajaxRequest.status);
					} else {
						errorCallback(ajaxRequest.responseText, ajaxRequest.status);
					}
				}
			};

			if ( method.toLowerCase() == 'post' ) {

				ajaxRequest.open(method, queryURL, true);

				ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

				ajaxRequest.send( data );

			} else {

				ajaxRequest.open(method, queryURL + ( data == '' ? '' : '?' + data ), true);

				ajaxRequest.send( null );

			}

		}

	}());

	HTMLFormElement.prototype.pcf_serialize = function() {

		var data = [];

		for ( var i = this.elements.length - 1; i >= 0; i-- ) {
			if ( this.elements[i].name === '' ) {
				continue;
			}
			switch ( this.elements[i].nodeName ) {
				case 'INPUT':
					switch ( this.elements[i].type ) {
						case 'file':
						case 'submit':
						case 'button':
							break;
						case 'checkbox':
						case 'radio':
							if ( this.elements[i].checked ) {
								data.push(this.elements[i].name + '=' + encodeURIComponent(this.elements[i].value));
							}
							break;
						default:
							data.push(this.elements[i].name + '=' + encodeURIComponent(this.elements[i].value));
							break;
					}
					break;
				case 'TEXTAREA':
					data.push(this.elements[i].name + '=' + encodeURIComponent(this.elements[i].value));
					break;
				case 'SELECT':
					switch ( this.elements[i].type ) {
						case 'select-one':
							data.push(this.elements[i].name + '=' + encodeURIComponent(this.elements[i].value));
							break;
						case 'select-multiple':
							for ( var j = this.elements[i].options.length - 1; j >= 0; j-- ) {
								if ( this.elements[i].options[j].selected ) {
									data.push(this.elements[i].name + '=' + encodeURIComponent(this.elements[i].options[j].value));
								}
							}
							break;
					}
					break;
			}
		}

		return data.join('&');

	};

}(window));