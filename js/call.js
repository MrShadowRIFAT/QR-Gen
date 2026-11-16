/* call QRcdr plugin */
$(document).ready(function(){
    QRcdr = $('.qrcdr').qrcdr({
        svglogo : false
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }

    function activateGeneratorMode() {
        $('#qrcdr-landing').addClass('d-none');
        $('#qrcdr-generator-section').removeClass('d-none');
        $('#start-generating-btn').html('<i data-lucide="arrow-left"></i> <span>Back to Home</span>').addClass('qrcdr-btn-back').removeClass('qrcdr-start-btn');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
        $('html, body').animate({ scrollTop: 0 }, 200);
    }

    function activateLandingMode() {
        $('#qrcdr-generator-section').addClass('d-none');
        $('#qrcdr-landing').removeClass('d-none');
        $('#start-generating-btn').html('<i data-lucide="sparkles"></i> <span>Open Generator</span>').addClass('qrcdr-start-btn').removeClass('qrcdr-btn-back');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
        $('html, body').animate({ scrollTop: 0 }, 200);
    }

    $('#start-generating-btn-hero').on('click', function(event){
        event.preventDefault();
        activateGeneratorMode();
    });

    $('#start-generating-btn').on('click', function(event){
        event.preventDefault();
        if ($('#qrcdr-generator-section').hasClass('d-none')) {
            activateGeneratorMode();
        } else {
            activateLandingMode();
        }
    });

    $('#btn-back-landing, #qrcdr-brand-logo').on('click', function(event){
        event.preventDefault();
        activateLandingMode();
    });

    // Check if URL hash indicates direct tab request
    if (window.location.hash && window.location.hash.length > 1 && window.location.hash !== '#') {
        activateGeneratorMode();
    }
});
