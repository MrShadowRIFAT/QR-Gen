/* call QRcdr plugin */
$(document).ready(function(){
    try {
        QRcdr = $('.qrcdr').qrcdr({
            svglogo : false
        });
    } catch(err) {
        console.error('Error initializing QRcdr:', err);
    }

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }

    function activateGeneratorMode() {
        $('#qrcdr-landing').addClass('d-none');
        $('#qrcdr-generator-section').removeClass('d-none');
        $('#start-generating-btn').html('<i data-lucide="arrow-left"></i> <span>Overview</span>').addClass('qrcdr-btn-back').removeClass('qrcdr-start-btn');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
        $('html, body').animate({ scrollTop: 0 }, 150);
    }

    function activateLandingMode() {
        $('#qrcdr-generator-section').addClass('d-none');
        $('#qrcdr-landing').removeClass('d-none');
        $('#start-generating-btn').html('<i data-lucide="arrow-right"></i> <span>Open Studio</span>').addClass('qrcdr-start-btn').removeClass('qrcdr-btn-back');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
        $('html, body').animate({ scrollTop: 0 }, 150);
    }

    $(document).on('click', '#start-generating-btn-hero', function(event){
        event.preventDefault();
        activateGeneratorMode();
    });

    $(document).on('click', '#start-generating-btn', function(event){
        event.preventDefault();
        if ($('#qrcdr-generator-section').hasClass('d-none')) {
            activateGeneratorMode();
        } else {
            activateLandingMode();
        }
    });

    $(document).on('click', '#btn-back-landing, #qrcdr-brand-logo', function(event){
        event.preventDefault();
        activateLandingMode();
    });

    // Check if URL hash indicates direct tab request
    if (window.location.hash && window.location.hash.length > 1 && window.location.hash !== '#') {
        activateGeneratorMode();
    }
});
