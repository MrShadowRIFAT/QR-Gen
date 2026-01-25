# Security Fixes Required - Priority List

This document outlines the required security fixes based on the comprehensive security audit. Fixes are organized by priority.

## 🔴 CRITICAL - Fix Immediately

### 1. Path Traversal Vulnerability (index.php)

**File:** `index.php` lines 58-62

**Current Code:**
```php
if (isset($_GET['p'])) {
    $load_page = $absolute.'/template/'.$_GET['p'].'.html';
    if (file_exists($load_page)) {
        $custom_page = file_get_contents($load_page);
    }
}
```

**Fixed Code:**
```php
if (isset($_GET['p'])) {
    $page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Whitelist allowed pages only
    $allowed_pages = ['about', 'help', 'contact', 'privacy', 'terms'];
    
    if ($page && in_array($page, $allowed_pages)) {
        $load_page = $absolute.'/template/'.$page.'.html';
        
        // Verify the file is within the template directory
        $real_path = realpath($load_page);
        $template_dir = realpath($absolute.'/template/');
        
        if ($real_path && $template_dir && 
            strpos($real_path, $template_dir) === 0 && 
            file_exists($load_page)) {
            $custom_page = file_get_contents($load_page);
        }
    }
}
```

---

### 2. Disabled SSL Certificate Verification (chunk-process.php)

**File:** `ajax/chunk-process.php` lines 79-80

**Current Code:**
```php
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
```

**Fixed Code:**
```php
// ALWAYS verify SSL in production
$is_production = !defined('DEVELOPMENT_MODE') || DEVELOPMENT_MODE !== true;

if ($is_production) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
} else {
    // Only for local development
    error_log('WARNING: SSL verification disabled in development mode');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
}
```

---

### 3. Insecure File Permissions (chunk-create.php)

**File:** `ajax/chunk-create.php` line 28

**Current Code:**
```php
mkdir($tempDir, 0777, true);
```

**Fixed Code:**
```php
// Use secure permissions: owner read/write/execute, group read/execute
mkdir($tempDir, 0750, true);
```

---

## 🟠 HIGH PRIORITY - Fix This Week

### 4. XSS via SVG Content (create.php)

**File:** `ajax/create.php` lines 30-39

**Current Code:**
```php
if (class_exists('DOMDocument') && class_exists('XSLTProcessor')) {
    $xsl = new DOMDocument;
    $xsl->load('sanitize.xsl');
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xsl);
    $xml = simplexml_load_string($precontent);
    $content = $proc->transformToXML($xml);
} else {
    $content = $precontent;
}
```

**Fixed Code:**
```php
if (class_exists('DOMDocument') && class_exists('XSLTProcessor')) {
    $xsl = new DOMDocument;
    $xsl->load('sanitize.xsl');
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xsl);
    $xml = simplexml_load_string($precontent);
    $content = $proc->transformToXML($xml);
} else {
    // Fallback sanitization when XSL is not available
    // Remove script tags and event handlers
    $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $precontent);
    $content = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
    
    // If still concerned, reject the content
    if (stripos($content, 'javascript:') !== false || 
        stripos($content, 'data:') !== false) {
        error_log('Potentially malicious SVG content detected');
        $response = array('error' => 'Invalid SVG content');
        echo json_encode($response);
        exit;
    }
}
```

---

### 5. URL Validation Bypass (functions.php & process.php)

**File:** `lib/functions.php` lines 77-111
**File:** `ajax/process.php` line 199

**Current Code in functions.php:**
```php
public function validateUrl($url)
{
    if ($url) {
        $trim = trim($url);
        if (strlen($trim) >= 2) {
            $url = strpos($trim, 'http') !== 0 ? 'http://'.$trim : $trim;
            return urldecode($url);
        }
    }
    return false;
}
```

**Fixed Code:**
```php
public function validateUrl($url)
{
    if (!$url) {
        return false;
    }
    
    $trim = trim($url);
    
    // Try to parse as-is first
    if (!filter_var($trim, FILTER_VALIDATE_URL)) {
        // Try adding http:// prefix
        $trim = 'http://' . $trim;
    }
    
    // Validate URL format
    if (!filter_var($trim, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Parse URL components
    $parsed = parse_url($trim);
    
    if (!$parsed || !isset($parsed['scheme'])) {
        return false;
    }
    
    // Whitelist allowed schemes
    $allowed_schemes = ['http', 'https', 'ftp', 'ftps'];
    
    if (!in_array(strtolower($parsed['scheme']), $allowed_schemes)) {
        error_log('Blocked dangerous URL scheme: ' . $parsed['scheme']);
        return false;
    }
    
    // Additional security: block local/private IPs if needed
    if (isset($parsed['host'])) {
        // Block localhost, 127.0.0.1, private IPs, etc.
        $blocked_hosts = ['localhost', '127.0.0.1', '0.0.0.0'];
        if (in_array(strtolower($parsed['host']), $blocked_hosts)) {
            return false;
        }
    }
    
    return $trim;
}
```

**Also fix in process.php line 199:**
```php
// Remove urlencode, let validateUrl handle it
$output_data = isset($_POST['link']) ? qrcdr()->validateUrl($_POST['link']) : false;
```

---

## 🟡 MEDIUM PRIORITY - Fix This Month

### 6. Session Security Improvements (functions.php)

**File:** `lib/functions.php` lines 55-68

**Enhanced Code:**
```php
public function init()
{
    if (session_status() == PHP_SESSION_NONE) {
        if ($this->getConfig('session_name')) {
            session_name($this->getConfig('session_name'));
        }
        
        $params = [
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        // Enable secure flag for HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $params['secure'] = true;
        }
        
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params($params);
        }
        
        session_start();
        
        // Session regeneration to prevent fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
            session_regenerate_id(true);
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Session timeout (2 hours of inactivity)
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > 7200)) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }
}
```

---

### 7. CSRF Protection Implementation

**New File:** `lib/csrf.php`

```php
<?php
/**
 * CSRF Protection Helper
 */

class CSRF {
    
    public static function generateToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
```

**Usage in AJAX files:**

Add to the beginning of each AJAX endpoint:
```php
require_once dirname(dirname(__FILE__)).'/lib/csrf.php';

$csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_SPECIAL_CHARS);

if (!CSRF::validateToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}
```

---

### 8. Improve Base64 Decode Security (png.php)

**File:** `ajax/png.php` lines 36-46

**Enhanced Code:**
```php
if (!file_exists($imgdata)) {
    $b64_data = explode(',', $imgdata);
    if (count($b64_data) == 2) {
        $content = base64_decode($b64_data[1], true); // Strict mode
        
        if ($content === false) {
            exit('error: invalid base64');
        }
        
        // Validate it's actually an image
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);
        
        $allowed_types = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml'];
        if (!in_array($mime, $allowed_types)) {
            exit('error: invalid image type');
        }
        
        // Check file size (max 5MB)
        if (strlen($content) > 5 * 1024 * 1024) {
            exit('error: file too large');
        }
    } else {
        $content = file_get_contents($imgdata);
    }
}
```

---

### 9. Add File Locking (chunk-create.php)

**File:** `ajax/chunk-create.php` line 35

**Current Code:**
```php
file_put_contents($tempFile, $chunk, FILE_APPEND);
```

**Fixed Code:**
```php
file_put_contents($tempFile, $chunk, FILE_APPEND | LOCK_EX);
```

---

## 🔵 LOW PRIORITY - Future Improvements

### 10. Environment-Based API Key Configuration

**File:** `config.php`

Add a new file `.env.example`:
```env
GOOGLE_API_KEY=your_google_maps_api_key_here
APP_ENV=production
DEBUG_MODE=false
```

Update `config.php`:
```php
// Load environment variables
if (file_exists(dirname(__FILE__).'/.env')) {
    $env = parse_ini_file(dirname(__FILE__).'/.env');
} else {
    $env = [];
}

$_CONFIG = array(
    'lang' => 'en',
    'qrcodes_dir' => 'qrcodes',
    // ... other configs ...
    'google_api_key' => $env['GOOGLE_API_KEY'] ?? 'YOUR-API-KEY',
    'debug_mode' => ($env['DEBUG_MODE'] ?? 'false') === 'true',
);

// Validate API key is not placeholder in production
if ($_CONFIG['google_api_key'] === 'YOUR-API-KEY' && 
    (!isset($env['APP_ENV']) || $env['APP_ENV'] === 'production')) {
    error_log('WARNING: Using placeholder API key in production!');
}
```

Add `.env` to `.gitignore`:
```
.env
```

---

### 11. Stricter Filename Validation

**File:** `ajax/create.php` line 41

**Current Code:**
```php
$basename = filter_var($decoded->basename, FILTER_SANITIZE_SPECIAL_CHARS);
```

**Fixed Code:**
```php
// Only allow alphanumeric, dash, and underscore
$basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $decoded->basename);

// Ensure filename is not empty after sanitization
if (empty($basename)) {
    $basename = 'qrcode_' . uniqid();
}

// Limit filename length
$basename = substr($basename, 0, 255);
```

---

### 12. Add Security Headers

**New File:** `lib/security-headers.php`

```php
<?php
/**
 * Security Headers
 */

function setSecurityHeaders() {
    // Prevent clickjacking
    header("X-Frame-Options: SAMEORIGIN");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // XSS Protection (legacy browsers)
    header("X-XSS-Protection: 1; mode=block");
    
    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Content Security Policy
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "img-src 'self' data: https:",
        "font-src 'self' data: https://fonts.gstatic.com",
        "connect-src 'self'",
    ];
    header("Content-Security-Policy: " . implode("; ", $csp));
    
    // Permissions Policy (formerly Feature Policy)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

// Call this function at the beginning of index.php
```

**Add to index.php:**
```php
require dirname(__FILE__)."/lib/security-headers.php";
setSecurityHeaders();
```

---

## Testing Recommendations

After implementing fixes:

1. **Manual Testing:**
   - Test all QR code generation features
   - Verify file uploads work correctly
   - Test custom page loading with both valid and invalid inputs
   - Test URL validation with various malicious inputs

2. **Automated Testing:**
   - Run OWASP ZAP or Burp Suite for vulnerability scanning
   - Use sqlmap to test for SQL injection (though none detected)
   - Test CSRF protection with automated tools

3. **Code Review:**
   - Have another developer review security fixes
   - Conduct penetration testing

4. **Monitoring:**
   - Enable error logging
   - Monitor for suspicious activity
   - Set up alerts for failed validation attempts

---

## Additional Recommendations

1. **Regular Updates:**
   - Keep PHP version updated
   - Update all third-party libraries regularly
   - Subscribe to security advisories

2. **Documentation:**
   - Document security configurations
   - Create deployment checklist
   - Maintain security changelog

3. **Training:**
   - Train developers on secure coding practices
   - Conduct regular security reviews
   - Stay informed about new vulnerabilities

---

## Implementation Checklist

- [ ] Fix path traversal vulnerability
- [ ] Enable SSL certificate verification
- [ ] Fix file permissions
- [ ] Implement XSS prevention in SVG
- [ ] Improve URL validation
- [ ] Enhance session security
- [ ] Implement CSRF protection
- [ ] Improve base64 decode security
- [ ] Add file locking
- [ ] Move API keys to environment variables
- [ ] Improve filename validation
- [ ] Add security headers
- [ ] Test all fixes
- [ ] Deploy to production

---

**Last Updated:** January 24, 2026  
**Priority:** CRITICAL - Address immediately before production deployment
