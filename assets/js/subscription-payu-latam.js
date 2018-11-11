(function( $ ) {
    $(function() {
        if ($('#form-payu-latam').is(":visible"))
        {
            new Card({
                form: document.querySelector('#form-payu-latam'),
                container: '.card-wrapper'
            });
        }
    });
    $('#form-payu-latam').submit(function(e){
        e.preventDefault();
        let msjerror = $('#card-payu-latam-suscribir .msj-error-payu ul strong li');
        let mjsjwait = $('#card-payu-latam-suscribir .overlay').children('div');
        $(msjerror).parents( ".msj-error-payu" ).hide();
        $("input[type=submit]").attr('disabled','disabled');
        if(!checkCard()){
            $(msjerror).parents( ".msj-error-payu" ).show();
            $(msjerror).text(payu_latam_suscription.msjNoCard);
            return;
        }
        $.ajax({
           type: 'POST',
           url:  payu_latam_suscription.ajaxurl,
           data: $(this).serialize() + '&action=subscription_payu_latam',
           dataType: "json",
           beforeSend: function(){
               $('#card-payu-latam-suscribir').css('cursor', 'wait');
               $('#card-payu-latam-suscribir .overlay').show();
               mjsjwait.text(payu_latam_suscription.msjProcess);
           },
           success: function(r){
               if(r.status){
                   mjsjwait.text(payu_latam_suscription.msjReturn);
                   window.location.replace(r.url);
               }else{
                   mjsjwait.text(r.message);
               }
           }
        });
    });
 function checkCard(){
     let countruCode = payu_latam_suscription.country;
     let classCard = $(".jp-card-identified" ).attr( "class" );
     let inputCard = $("input[name=type]");
     switch(true) {
         case (classCard.indexOf('visa') !== -1 && countruCode != 'PA'):
             $(inputCard).val('VISA');
             return true;
             break;
         case (classCard.indexOf('mastercard') !== -1):
             $(inputCard).val('MASTERCARD');
             return true;
             break;
         case (classCard.indexOf('amex') !== -1 && countruCode != 'PA'):
             $(inputCard).val('AMEX');
             return true;
             break;
         case (classCard.indexOf('diners') !== -1 && (countruCode != 'MX' || countruCode != 'PA') ):
             $(inputCard).val('DINERS');
             return true;
             break;
         default:
             return false;
     }
 }
})(jQuery);