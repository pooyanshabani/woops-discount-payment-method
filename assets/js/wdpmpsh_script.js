jQuery(function($){
    
	$('form.checkout').on('change', 'input[name="payment_method"]', function(){
        $(document.body).trigger('update_checkout');
    });
	
	$(document.body).on('updated_checkout', function(){
        var gateway = $('input[name="payment_method"]:checked').val();

        if (gateway !== 'pa_zarinpal') {
            $('.pooya-gateway-discount').remove();
        }
    });
	function fixFeeTd(){
		$('tr.fee').each(function(){
			var $tr = $(this);
			var $td = $tr.find('td').first();

			if (!$tr.hasClass('final')) $tr.addClass('cart-discount');
			if (!$td.hasClass('final')) $td.addClass('final');
			if (!$td.attr('data-title')) $td.attr('data-title', 'discount');

		});
    }

    fixFeeTd();

    $(document.body).on('updated_checkout', function(){
        fixFeeTd();
    });
});
