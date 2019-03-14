jQuery( function( $ ) {
    'use strict';

    const checkout_form = $( 'form.woocommerce-checkout' );

    $( 'body' ).on( 'updated_checkout', function() {

        if (checkout_form.find('#form-payu-latam').is(":visible"))
        {
            new Card({
                form: document.querySelector('#form-payu-latam'),
                container: '.card-wrapper'
            });
        }

    } );

    $(document.body).on('checkout_error', function () {
        swal.close();
    });

    checkout_form.on( 'checkout_place_order', function() {

        if($('form[name="checkout"] input[name="payment_method"]:checked').val() === 'subscription_payu_latam'){

            let number_card = checkout_form.find('#subscriptionpayulatam_number').val();
            let card_holder = checkout_form.find('#subscriptionpayulatam_name').val();
            let card_type = checkout_form.find('#subscriptionpayulatam_type').val();
            let card_expire = checkout_form.find('#subscriptionpayulatam_expiry').val();
            let card_cvv = checkout_form.find('#subscriptionpayulatam_cvc').val();

            checkout_form.append($('<input name="subscriptionpayulatam_number" type="hidden" />' ).val( number_card ));
            checkout_form.append($('<input name="subscriptionpayulatam_name" type="hidden" />' ).val( card_holder ));
            checkout_form.append($('<input name="subscriptionpayulatam_type" type="hidden" />' ).val( getTypeCard() ));
            checkout_form.append($('<input name="subscriptionpayulatam_expiry" type="hidden" />' ).val( card_expire ));
            checkout_form.append($('<input name="subscriptionpayulatam_cvc" type="hidden" />' ).val( card_cvv ));

            let inputError = checkout_form.find("input[name=subscriptionpayulatam_errorcard]");

            if( inputError.length )
            {
                inputError.remove();
            }


            if (!number_card || !card_holder || getTypeCard(checkout_form) === null || !card_expire || !card_cvv){
                checkout_form.append(`<input type="hidden" name="subscriptionpayulatam_errorcard" value="${payu_latam_suscription.msjEmptyInputs}">`);
            }else if (!checkCard()){
                checkout_form.append(`<input type="hidden" name="subscriptionpayulatam_errorcard" value="${payu_latam_suscription.msjNoCard}">`);
            }else if(!valid_credit_card(number_card)){
                checkout_form.append(`<input type="hidden" name="subscriptionpayulatam_errorcard" value="${payu_latam_suscription.msjNoCardValidate}">`);
            }

            swal.fire({
                title: payu_latam_suscription.msjProcess,
                onOpen: () => {
                    swal.showLoading()
                },
                allowOutsideClick: false
            });
        }

    });

 function checkCard(){
     let countryCode = payu_latam_suscription.country;
     let classCard = $(".jp-card-identified" ).attr( "class" );
     let inputCard = $("input[name=subscriptionpayulatam_type]");

     let  isAcceptableCard = false;

     switch(true) {
         case (classCard.indexOf('visa') !== -1 && countryCode !== 'PA'):
             $(inputCard).val('VISA');
             isAcceptableCard = true;
             break;
         case (classCard.indexOf('mastercard') !== -1):
             $(inputCard).val('MASTERCARD');
             isAcceptableCard = true;
             break;
         case (classCard.indexOf('amex') !== -1 && countryCode !== 'PA'):
             $(inputCard).val('AMEX');
             isAcceptableCard = true;
             break;
         case (classCard.indexOf('diners') !== -1 && (countryCode !== 'MX' || countryCode !== 'PA') ):
             $(inputCard).val('DINERS');
             isAcceptableCard = true;
     }

     return isAcceptableCard;

 }

 function getTypeCard(){
     let classCard = checkout_form.find(".jp-card-identified" ).attr( "class" );

     if (typeof classCard === 'undefined')
         return null;

     let classTypeCard = classCard.split(' ');
     let typeCard = classTypeCard[1].split('jp-card-');
     return typeCard[1].toUpperCase();
 }

 function valid_credit_card(value) {
    // accept only digits, dashes or spaces
    if (/[^0-9-\s]+/.test(value)) return false;

    // The Luhn Algorithm. It's so pretty.
    var nCheck = 0, nDigit = 0, bEven = false;
    value = value.replace(/\D/g, "");

    for (var n = value.length - 1; n >= 0; n--) {
        var cDigit = value.charAt(n);
            nDigit = parseInt(cDigit, 10);

        if (bEven) {
            if ((nDigit *= 2) > 9) nDigit -= 9;
        }

        nCheck += nDigit;
        bEven = !bEven;
 }

    return (nCheck % 10) === 0;
}

});