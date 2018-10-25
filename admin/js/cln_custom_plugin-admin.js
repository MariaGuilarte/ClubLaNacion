(function( $ ) {
	'use strict';

	$( function(){
		console.log("Just load");

		var $from = $('.cln-csv-date-from'),
				$to   = $('.cln-csv-date-to');

		$from.datepicker({
			maxDate: '0',
			dateFormat: 'dd-mm-yy',
			onSelect : function(date){
				console.log( date );
				$to.datepicker("option", "minDate", date);
			}
		}).datepicker("setDate", new Date());

		$to.datepicker({
			maxDate: '0',
			dateFormat: 'dd-mm-yy'
		}).datepicker("setDate", new Date());;

	});
	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
