(function($){

    $('.subscription_payu_latam_spl_enable').click(function(e){
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url:  ajaxurl,
            data: 'action=subscription_payu_latam_spl',
            dataType: 'json',
            beforeSend: () =>{
                swal.fire({
                    title: '',
                    onOpen: () => {
                        swal.showLoading()
                    },
                    allowOutsideClick: false
                });
            },
            success: (r) =>{
                if (r.status){
                    swal.fire({
                        title: 'Se han activado exitosamente',
                        text: 'redireccionando a configuraciones...',
                        type: 'success',
                        showConfirmButton: false
                    });
                    window.location.replace(subscriptionPayuLatam.urlConfig);
                }else{
                    swal.fire({
                        title: 'Error al instalar',
                        text: 'No se ha podido descargar y activar el plugin "WooCommerce Subscriptions", intenta de nuevo o hazlo manualmente',
                        type: 'warning'
                    });
                }
            }
        });
    });
})(jQuery);