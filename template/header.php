<header id="qrcdr-app-header" class="qrcdr-app-header">
  <div class="container qrcdr-nav-container">
    <a href="#" id="qrcdr-brand-logo" class="qrcdr-logo" onclick="event.preventDefault()">
      <span class="qrcdr-logo-icon"><i data-lucide="qr-code"></i></span>
      <span class="qrcdr-logo-text">
        <strong>QR Studio</strong>
        <span class="qrcdr-logo-badge">Vector Engine</span>
      </span>
    </a>

    <div class="qrcdr-nav-right">
      <ul class="navbar-nav qrcdr-lang-nav">
        <?php echo qrcdr()->langMenu('menu', 'qrcdr-lang-dropdown'); ?>
      </ul>
      <button id="start-generating-btn" class="qrcdr-start-btn" type="button">
        <i data-lucide="arrow-right"></i> <span>Open Studio</span>
      </button>
    </div>
  </div>
</header>

<div id="qrcdr-landing" class="qrcdr-landing-ui">
  <main class="qrcdr-landing-main">
    <div class="container text-center position-relative">
      <div class="qrcdr-status-badge">
        <span class="qrcdr-status-dot"></span>
        <span>Vector Engine v5.4.2 &bull; Lossless SVG / PDF</span>
      </div>
      <h1 class="qrcdr-hero-title">Vector QR Code Studio</h1>
      <p class="qrcdr-hero-subtitle">Generate resolution-independent SVG, high-DPI PNG, and print-ready PDF QR codes with custom quiet zones and corner geometry.</p>
      
      <div class="qrcdr-landing-actions">
        <button id="start-generating-btn-hero" class="qrcdr-hero-cta-btn" type="button">
          <i data-lucide="arrow-right"></i> <span>Open Configurator</span>
        </button>
      </div>

      <!-- Feature Grid with Optical Registration Markers -->
      <div class="qrcdr-features-grid mt-5">
        <div class="qrcdr-feature-card">
          <span class="qrcdr-crop-mark qrcdr-crop-tl"></span>
          <span class="qrcdr-crop-mark qrcdr-crop-br"></span>
          <div class="qrcdr-feature-icon"><i data-lucide="layers"></i></div>
          <h3>14 Encoders</h3>
          <p>URLs, WiFi credentials, vCard contacts, SMS, coordinates, events, and crypto addresses.</p>
        </div>
        <div class="qrcdr-feature-card">
          <span class="qrcdr-crop-mark qrcdr-crop-tl"></span>
          <span class="qrcdr-crop-mark qrcdr-crop-br"></span>
          <div class="qrcdr-feature-icon"><i data-lucide="palette"></i></div>
          <h3>Vector Geometry</h3>
          <p>Custom corner marker styles, module dot geometry, and framed label boundaries.</p>
        </div>
        <div class="qrcdr-feature-card">
          <span class="qrcdr-crop-mark qrcdr-crop-tl"></span>
          <span class="qrcdr-crop-mark qrcdr-crop-br"></span>
          <div class="qrcdr-feature-icon"><i data-lucide="download"></i></div>
          <h3>Lossless Export</h3>
          <p>Resolution-independent SVG, high-DPI raster PNG, and vector PDF sheets.</p>
        </div>
        <div class="qrcdr-feature-card">
          <span class="qrcdr-crop-mark qrcdr-crop-tl"></span>
          <span class="qrcdr-crop-mark qrcdr-crop-br"></span>
          <div class="qrcdr-feature-icon"><i data-lucide="shield-check"></i></div>
          <h3>Private &amp; Fast</h3>
          <p>Client-side chunked processing with zero tracking and sanitized SVG payloads.</p>
        </div>
      </div>
    </div>
  </main>
</div>
