/* call QRcdr plugin */
$(document).ready(function(){
    QRcdr = $('.qrcdr').qrcdr({
        svglogo : false
    });

    function activateGeneratorMode() {
        $('#qrcdr-landing').addClass('d-none');
        $('#qrcdr-top-nav').addClass('d-none');
        $('#qrcdr-footer').addClass('d-none');
        $('#qrcdr-generator-section').removeClass('d-none');
        $('html, body').animate({ scrollTop: 0 }, 200);
    }

    $('#start-generating-btn').on('click', function(event){
        event.preventDefault();
        activateGeneratorMode();
    });
});
