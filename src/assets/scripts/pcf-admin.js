(function(window) {

	/*************************************************************/
	/******************* REUSABLE CUSTOM CLASSES *****************/
	/*************************************************************/

	window.PCF_FieldGroup = (function( params ) {

		/***************************************/
		/************* INITIALIZE **************/
		/***************************************/

		var Class = function( params ) {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// SET UP GLOBAL VARIABLES TO MANAGE EVERYTHING

			/* Variable:  parentID                             */
			/* Type:      String                               */
			/* Default:   null                                 */
			/* Purpose:   This is the ID of the wrapper        */
			/*            element of the toggle.               */
			var parentID = 'parentID' in params ? params.parentID : null;

			/* Variable:  childrenIDs                          */
			/* Type:      Array of Strings                     */
			/* Default:   null                                 */
			/* Purpose:   This is the ID of the wrapper        */
			/*            element of the toggle.               */
			var childrenIDs = 'childrenIDs' in params ? params.childrenIDs : null;

			/* Variable:  updateLogic                          */
			/* Type:      Function                             */
			/* Default:   function(){}                         */
			/* Purpose:   This is the ID of the wrapper        */
			/*            element of the toggle.               */
			self.updateLogic = 'updateLogic' in params ? params.updateLogic : function( self ) { self.toggleChildren(); };

			/* Variable:  updateEvent                          */
			/* Type:      String                               */
			/* Default:   'click'                              */
			/* Purpose:   This is the ID of the wrapper        */
			/*            element of the toggle.               */
			self.updateEvent = 'updateEvent' in params ? params.updateEvent : 'click';

			/* Variable:  el                                   */
			/* Type:      Object                               */
			/* Default:   {}                                   */
			/* Purpose:   This object contains references to   */
			/*            the HTML elements associated with    */
			/*            this field.                          */
			self.el = {};

			// STORE HTML WRAPPER ELEMENT

			self.el.parent = document.getElementById(parentID);
			self.el.children = [];

			if ( ! self.el.parent ) {

				return false;

			}

			var child = null;
			childrenIDs.forEach(function(childID){
				child = document.getElementById(childID).parentNode.parentNode;
				for (var i = 0; i < child.children.length; i++) {
					child.children[i].classList.add( 'pcf-child' );
				};
				self.el.children.push( child );
			});

			// CHECK TO SEE IF WRAPPER ELEMENT EXIST, AND LISTEN FOR CHANGE EVENTS PASSED UP THROUGH IT

			if ( self.el.parent ) {
				self.el.parent.addEventListener(self.updateEvent, function(event){
					self.updateChildren();
				});
			}

			self.updateChildren();

		};

		/***************************************/
		/********** PUBLIC FUNCTIONS ***********/
		/***************************************/

		Class.prototype.updateChildren = function() {


			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			self.updateLogic(self);

		};

		Class.prototype.toggleChildren = function() {


			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// TOGGLE ALL CHILDREN

			self.el.children.forEach(function(child){
				child.classList.toggle('hidden');
			});

		};

		Class.prototype.showChildren = function() {


			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// SHOW ALL CHILDREN

			self.el.children.forEach(function(child){
				child.classList.remove('hidden');
			});


		};

		Class.prototype.hideChildren = function() {

			// STORE this AS self, SO THAT IT IS ACCESSIBLE IN SUB-FUNCTIONS AND TIMEOUTS.

			var self = this;

			// HIDE ALL CHILDREN

			self.el.children.forEach(function(child){
				child.classList.add('hidden');
			});

		};

		return Class;

	}());

	/*************************************************************/
	/****************** REUSABLE CUSTOM FUNCTIONS ****************/
	/*************************************************************/

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

	function pcf_showAdminNotice( status, message ) {

		var wrapper = document.querySelector('.wrap');
		var newNode = document.createElement('div');

		if ( status == 'success' ) {
			newNode.className = 'updated notice is-dismissible';
		} else if ( status = 'error' ) {
			newNode.className = 'error notice is-dismissible';
		} else {
			newNode.className = 'update-nag notice';
		}
		newNode.innerHTML = '<p>' + message + '</p>';

		wrapper.insertBefore(newNode, wrapper.firstElementChild.nextSibling);

	}

	/*************************************************************/
	/************************* INITIALIZE ************************/
	/*************************************************************/

	new PCF_FieldGroup({
		parentID: 'pcf_reCaptcha_status',
		childrenIDs: [
			'pcf_reCaptcha_site_key',
			'pcf_reCaptcha_secret_key'
		],
		updateEvent: 'change',
		updateLogic: function( self ) {
			if ( self.el.parent.querySelector(':checked').value == 'enabled' ) {
				self.showChildren();
			} else {
				self.hideChildren();
			}
		}
	});

	new PCF_FieldGroup({
		parentID: 'pcf_field_name_status_customize',
		childrenIDs: [
			'pcf_field_name_required',
			'pcf_field_name_placeholder'
		]
	});

	new PCF_FieldGroup({
		parentID: 'pcf_field_email_status_customize',
		childrenIDs: [
			'pcf_field_email_required',
			'pcf_field_email_placeholder'
		]
	});

	new PCF_FieldGroup({
		parentID: 'pcf_field_phone_status_customize',
		childrenIDs: [
			'pcf_field_phone_required',
			'pcf_field_phone_placeholder'
		]
	});

	var pcf_testButton = document.getElementById('send_test_email');

	if ( pcf_testButton ) {

		var original = pcf_testButton.textContent;

		pcf_testButton.addEventListener('click', function(event){

			event.preventDefault();

			pcf_testButton.classList.add('disabled');
			pcf_testButton.textContent = 'Sending...';

			pcf_ajax({
				method: 'get',
				queryURL: event.target.href.replace('options-general', 'admin-ajax') + '_ajax',
				success: function(serverResponse) {
					pcf_testButton.classList.remove('disabled');
					pcf_testButton.textContent = original;
					if ( JSON.parse(serverResponse).success) {
						pcf_showAdminNotice('success', 'Your test email has been successfully sent. Please check and make sure that you recieved it.');
					} else {
						pcf_showAdminNotice('error', 'Oops, something went wrong and the email wasn\'t sent. Please check your server\'s email configuration. If you are running on localhost, please make sure that you have an email client installed.');
					}
				},
				error: function(serverResponse) {
					pcf_testButton.classList.remove('disabled');
					pcf_testButton.textContent = original;
					pcf_showAdminNotice('error', 'Oops, something went wrong and we can\'t connect to the server. Please wait a moment and try reloading.');
				}
			});

		});

	}

}(window));