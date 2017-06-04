Stripe.setPublishableKey('pk_test_k1izWPPLzTxqavhIsoFz52ug');

var $form = $('#checkout-form');

$form.submit(function(event){
	$('#charge-error').addClass('hidden');
	$form.find('button').prop('disable',true);


	Stripe.card.createToken({
  number: $('#card-number').val(),
  cvc: $('#card-cvc').val(),
  exp_month: $('#card-expiry-month').val(),
  exp_year: $('#card-expiry-year').val(),
  name: $('#card-name').val() 
}, stripeResponseHandler);

	//wait a second, don't continue form submission
	return false;

});

function stripeResponseHandler(status,response){

	if(response.error){
		$('#charge-error').removeClass('hidden');
		$('#charge-error').text(response.error.message);
		$form.find('button').prop('disable',false);
	}else{
		var token = response.id;
		//console.log("token" + token);
		// Insert the token into the form so it gets submitted to the server:
		$form.append($('<input type="hidden" name="stripeToken" />').val(token));

    // Submit the form:
    $form.get(0).submit();
	}



}

