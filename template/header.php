<header id="qrcdr-app-header" class="qrcdr-app-header">
  <div class="container qrcdr-nav-container">
    <a href="#" id="qrcdr-brand-logo" class="qrcdr-logo" onclick="event.preventDefault()">
      <span class="qrcdr-logo-icon"><i data-lucide="qr-code"></i></span>
      <span class="qrcdr-logo-text">
        <strong>QR Studio</strong>
        <small>Pro Vector Generator</small>
      </span>
    </a>

    <div class="qrcdr-nav-right">
      <ul class="navbar-nav qrcdr-lang-nav">
        <?php echo qrcdr()->langMenu('menu', 'qrcdr-lang-dropdown'); ?>
      </ul>
      <button id="start-generating-btn" class="qrcdr-start-btn" type="button">
        <i data-lucide="sparkles"></i> <span>Open Generator</span>
      </button>
    </div>
  </div>
</header>

<div id="qrcdr-landing" class="qrcdr-landing-ui">
  <main class="qrcdr-landing-main">
    <div class="qrcdr-glow qrcdr-glow-a"></div>
    <div class="qrcdr-glow qrcdr-glow-b"></div>
    <div class="container text-center position-relative">
      <div class="qrcdr-pill"><i data-lucide="zap"></i> Modern &amp; Client-Safe &bull; v5.4 Pro</div>
      <h1 class="qrcdr-hero-title">Generate Custom QR Codes <span>In Seconds</span></h1>
      <p class="qrcdr-hero-subtitle">Design professional, pixel-perfect vector QR codes for websites, WiFi, vCards, events, payments, and more with instant real-time preview.</p>
      
      <div class="qrcdr-landing-actions">
        <button id="start-generating-btn-hero" class="qrcdr-hero-cta-btn" type="button">
          <i data-lucide="qr-code"></i> Start Generating Free
        </button>
      </div>

      <!-- Feature Grid -->
      <div class="qrcdr-features-grid mt-5">
        <div class="qrcdr-feature-card">
          <div class="qrcdr-feature-icon"><i data-lucide="layers"></i></div>
          <h3>14+ Data Types</h3>
          <p>Links, WiFi, vCards, SMS, PayPal, Bitcoin, Geo maps, and Calendar events.</p>
        </div>
        <div class="qrcdr-feature-card">
          <div class="qrcdr-feature-icon"><i data-lucide="palette"></i></div>
          <h3>Full Vector Styling</h3>
          <p>Custom linear/radial gradients, corner marker styles, and tailored frame badges.</p>
        </div>
        <div class="qrcdr-feature-card">
          <div class="qrcdr-feature-icon"><i data-lucide="download"></i></div>
          <h3>SVG, PNG &amp; PDF Export</h3>
          <p>Export crisp infinite-resolution SVGs, high-DPI PNGs, and print-ready PDF sheets.</p>
        </div>
        <div class="qrcdr-feature-card">
          <div class="qrcdr-feature-icon"><i data-lucide="shield-check"></i></div>
          <h3>Secure &amp; Fast</h3>
          <p>Chunked client-side uploads, XSS sanitized SVGs, and zero tracking.</p>
        </div>
      </div>
    </div>
  </main>
</div>