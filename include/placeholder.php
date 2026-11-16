<div class="sticky-top qrcdr-sticky-preview">
    <div class="placeresult qrcdr-preview-panel d-grid">
        <span class="qrcdr-crop-mark qrcdr-crop-tl"></span>
        <span class="qrcdr-crop-mark qrcdr-crop-tr"></span>
        <span class="qrcdr-crop-mark qrcdr-crop-bl"></span>
        <span class="qrcdr-crop-mark qrcdr-crop-br"></span>
        <div class="qrcdr-preview-header d-flex justify-content-between align-items-center">
            <span class="qrcdr-preview-badge"><i class="fa fa-crosshairs"></i> Optical Preview</span>
            <span class="qrcdr-preview-spec">1:1 VECTOR</span>
        </div>
        <div class="form-group text-center wrapresult">
            <div class="resultholder">
                <img class="img-fluid" src="<?php echo $relative.qrcdr()->getConfig('placeholder'); ?>" alt="QR Preview" />
                <div class="infopanel"></div>
            </div>
        </div>
        <div class="preloader"><i class="fa fa-spinner fa-spin"></i></div>
        <input type="hidden" class="holdresult">
        <button class="btn btn-lg btn-block btn-primary ellipsis generate_qrcode<?php echo $rounded_btn_save; ?>" disabled><i class="fa fa-check"></i> <?php echo qrcdr()->getString('save'); ?></button>
        <div class="text-center mt-3 linksholder"></div>
    </div>
</div>
