# Security Audit Report - QR Code Generator

**Date:** January 24, 2026  
**Repository:** MrShadowRIFAT/QR-Gen  
**Audit Type:** Full Repository Security Scan  

## Executive Summary

This report documents a comprehensive security audit of the QR Code Generator (QRcdr) repository. The audit involved scanning all code files for security vulnerabilities, suspicious code patterns, and potential security risks.

## Findings Summary

### Critical Issues: 3
### High Severity: 2
### Medium Severity: 4
### Low Severity: 3
### Informational: 5

---

## Detailed Security Findings

### 🔴 CRITICAL ISSUES

#### 1. Path Traversal Vulnerability in index.php (CVE Risk)
**File:** `/index.php` (Lines 58-62)  
**Severity:** CRITICAL  
**CWE:** CWE-22 (Improper Limitation of a Pathname to a Restricted Directory)

**Description:**
```php
if (isset($_GET['p'])) {
    $load_page = $absolute.'/template/'.$_GET['p'].'.html';
    if (file_exists($load_page)) {
        $custom_page = file_get_contents($load_page);
    }
}
```

**Risk:**
User-controlled input from `$_GET['p']` is concatenated directly into a file path without proper validation. An attacker could use path traversal sequences (e.g., `../`) to read arbitrary files from the server.

**Attack Example:**
```
/?p=../../../../etc/passwd
/?p=../config
```

**Recommendation:**
- Validate and sanitize the `$_GET['p']` parameter
- Use a whitelist of allowed page names
- Implement proper path validation to prevent directory traversal

**Suggested Fix:**
```php
if (isset($_GET['p'])) {
    $page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_SPECIAL_CHARS);
    // Whitelist allowed pages
    $allowed_pages = ['about', 'help', 'contact'];
    if (in_array($page, $allowed_pages)) {
        $load_page = $absolute.'/template/'.$page.'.html';
        if (file_exists($load_page)) {
            $custom_page = file_get_contents($load_page);
        }
    }
}
```

---

#### 2. Insecure SSL Certificate Verification Disabled
**File:** `/ajax/chunk-process.php` (Lines 79-80)  
**Severity:** CRITICAL  
**CWE:** CWE-295 (Improper Certificate Validation)

**Description:**
```php
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
```

**Risk:**
SSL certificate verification is completely disabled, making the application vulnerable to Man-in-the-Middle (MITM) attacks. An attacker could intercept and modify traffic between the server and external services.

**Impact:**
- Exposure to MITM attacks
- Data interception
- Session hijacking

**Recommendation:**
- Enable SSL certificate verification
- Use proper CA certificates
- Only disable in development environments with proper safeguards

**Suggested Fix:**
```php
// In production, ALWAYS verify SSL certificates
if (getenv('APP_ENV') !== 'development') {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
} else {
    // Only in development
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
}
```

---

#### 3. Insecure File Permissions on Temporary Directory
**File:** `/ajax/chunk-create.php` (Line 28)  
**Severity:** CRITICAL  
**CWE:** CWE-732 (Incorrect Permission Assignment for Critical Resource)

**Description:**
```php
mkdir($tempDir, 0777, true);
```

**Risk:**
Setting directory permissions to `0777` allows all users (owner, group, and others) full read, write, and execute permissions. This is a serious security risk that could allow unauthorized access to sensitive temporary files.

**Impact:**
- Any user on the system can read, write, or execute files in this directory
- Potential for arbitrary file uploads
- Exposure of sensitive data in temporary files

**Recommendation:**
Use restrictive permissions (0750 or 0755) instead of 0777.

**Suggested Fix:**
```php
mkdir($tempDir, 0750, true);
```

---

### 🟠 HIGH SEVERITY ISSUES

#### 4. Cross-Site Scripting (XSS) Potential via SVG Content
**File:** `/ajax/create.php` (Lines 26-38)  
**Severity:** HIGH  
**CWE:** CWE-79 (Cross-site Scripting)

**Description:**
While there's an XSL transformation for sanitization, the fallback when DOMDocument or XSLTProcessor is not available writes unsanitized SVG content:

```php
if (class_exists('DOMDocument') && class_exists('XSLTProcessor')) {
    // Sanitized path
} else {
    $content = $precontent; // Unsanitized!
}
```

**Risk:**
If the server doesn't have the required PHP extensions, malicious SVG content (including JavaScript) could be written to files, leading to XSS attacks when these SVGs are loaded.

**Recommendation:**
- Always sanitize SVG content regardless of available extensions
- Implement alternative sanitization methods as fallback
- Add Content Security Policy headers

---

#### 5. URL Validation Bypass Vulnerability
**File:** `/lib/functions.php` (Lines 77-111)  
**File:** `/ajax/process.php` (Line 199)  
**Severity:** HIGH  
**CWE:** CWE-20 (Improper Input Validation)

**Description:**
```php
// In process.php
$output_data = isset($_POST['link']) ? qrcdr()->validateUrl(urlencode($_POST['link'])) : false;

// In functions.php  
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

**Risk:**
1. The URL is encoded before being passed to `validateUrl()`, then decoded inside
2. Minimal validation - only checks if string length >= 2
3. No validation against dangerous protocols (javascript:, data:, file:, etc.)
4. Could allow malicious URLs in QR codes

**Attack Examples:**
```
javascript:alert(document.cookie)
data:text/html,<script>alert('XSS')</script>
file:///etc/passwd
```

**Recommendation:**
```php
public function validateUrl($url)
{
    if ($url) {
        $trim = trim($url);
        
        // Properly validate URL format
        if (!filter_var($trim, FILTER_VALIDATE_URL)) {
            // Try adding http:// prefix
            $trim = 'http://' . $trim;
        }
        
        // Re-validate
        if (!filter_var($trim, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check for dangerous protocols
        $parsed = parse_url($trim);
        $allowed_schemes = ['http', 'https', 'ftp', 'ftps'];
        
        if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), $allowed_schemes)) {
            return false;
        }
        
        return $trim;
    }
    return false;
}
```

---

### 🟡 MEDIUM SEVERITY ISSUES

#### 6. Session Fixation Vulnerability
**File:** `/lib/functions.php` (Lines 55-68)  
**Severity:** MEDIUM  
**CWE:** CWE-384 (Session Fixation)

**Description:**
```php
public function init()
{
    if (session_status() == PHP_SESSION_NONE) {
        if ($this->getConfig('session_name')) {
            session_name($this->getConfig('session_name'));
        }
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params(
                ['httponly' => true, 'samesite' => 'strict']
            );
        }
        session_start();
    }
}
```

**Risk:**
- No session regeneration after authentication/privilege changes
- Missing `secure` flag for HTTPS-only cookies
- No session timeout mechanism

**Recommendation:**
```php
public function init()
{
    if (session_status() == PHP_SESSION_NONE) {
        if ($this->getConfig('session_name')) {
            session_name($this->getConfig('session_name'));
        }
        
        $params = ['httponly' => true, 'samesite' => 'strict'];
        
        // Add secure flag for HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $params['secure'] = true;
        }
        
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params($params);
        }
        
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
            session_regenerate_id(true);
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate after 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}
```

---

#### 7. Potential Information Disclosure via Debug Mode
**Files:** Multiple files  
**Severity:** MEDIUM  
**CWE:** CWE-209 (Information Exposure Through an Error Message)

**Description:**
When `debug_mode` is enabled in config, full error reporting is displayed:

```php
if (qrcdr()->getConfig('debug_mode')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

**Risk:**
If debug mode is accidentally left enabled in production, sensitive information could be exposed through error messages, including:
- File paths
- Database credentials
- Internal application structure

**Recommendation:**
- Never enable debug mode in production
- Add environment-based configuration
- Log errors instead of displaying them

---

#### 8. Base64 Decode Usage
**File:** `/ajax/png.php` (Line 38)  
**Severity:** MEDIUM  
**CWE:** CWE-502 (Deserialization of Untrusted Data)

**Description:**
```php
$content = base64_decode($b64_data[1]);
```

**Risk:**
While `base64_decode` itself isn't inherently dangerous, the decoded content is written directly to a file without validation. If combined with other vulnerabilities, this could be exploited.

**Recommendation:**
- Validate the decoded content before writing to file
- Check file size limits
- Validate image format using proper image validation functions

---

#### 9. Missing CSRF Protection
**Files:** All AJAX endpoints  
**Severity:** MEDIUM  
**CWE:** CWE-352 (Cross-Site Request Forgery)

**Description:**
AJAX endpoints only check for the `X-Requested-With` header but don't implement CSRF tokens:

```php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
) {
    exit;
}
```

**Risk:**
The `X-Requested-With` header can be forged in certain scenarios. Proper CSRF tokens should be used for state-changing operations.

**Recommendation:**
Implement CSRF token validation for all POST requests.

---

### 🔵 LOW SEVERITY ISSUES

#### 10. Hardcoded API Key Placeholder
**File:** `/config.php` (Line 43)  
**Severity:** LOW  
**CWE:** CWE-798 (Use of Hard-coded Credentials)

**Description:**
```php
'google_api_key' => 'YOUR-API-KEY',
```

**Risk:**
Users might forget to replace the placeholder, or accidentally commit real API keys.

**Recommendation:**
- Use environment variables for API keys
- Add clear documentation about API key configuration
- Add validation to check if placeholder is still in use

---

#### 11. Insufficient Input Validation on File Names
**File:** `/ajax/create.php` (Line 41)  
**Severity:** LOW  

**Description:**
```php
$basename = filter_var($decoded->basename, FILTER_SANITIZE_SPECIAL_CHARS);
```

**Risk:**
`FILTER_SANITIZE_SPECIAL_CHARS` may not be sufficient for file name validation.

**Recommendation:**
Use a stricter validation pattern for file names:
```php
$basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $decoded->basename);
```

---

#### 12. Potential Race Condition in File Operations
**File:** `/ajax/chunk-create.php` (Lines 34-35)  
**Severity:** LOW  
**CWE:** CWE-362 (Concurrent Execution using Shared Resource)

**Description:**
```php
file_put_contents($tempFile, $chunk, FILE_APPEND);
```

**Risk:**
Missing `LOCK_EX` flag could lead to race conditions in concurrent requests.

**Recommendation:**
```php
file_put_contents($tempFile, $chunk, FILE_APPEND | LOCK_EX);
```

---

### ℹ️ INFORMATIONAL FINDINGS

#### 13. Missing Security Headers
**Recommendation:** Implement the following HTTP security headers:
- `Content-Security-Policy`
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: no-referrer-when-downgrade`

#### 14. No Rate Limiting
**Recommendation:** Implement rate limiting on AJAX endpoints to prevent abuse.

#### 15. File Upload Without Type Validation
**Recommendation:** Add strict file type validation for logo uploads.

#### 16. No Input Length Limits
**Recommendation:** Implement maximum length validation for all text inputs.

#### 17. Commented Out Code
Several files contain large blocks of commented code that should be removed for clarity.

---

## Positive Security Practices Found

✅ **Good Practices Identified:**
1. Use of `filter_input()` for input sanitization in many places
2. XSL transformation for SVG sanitization (when available)
3. HTTPOnly and SameSite cookie flags
4. AJAX request verification (though not CSRF-proof)
5. Email validation using `FILTER_VALIDATE_EMAIL`
6. Bitcoin address validation implementation
7. Session ID in temporary file names (prevents some collision attacks)

---

## Recommendations Priority

### Immediate Actions Required:
1. ✅ Fix path traversal vulnerability in index.php
2. ✅ Fix SSL verification in chunk-process.php
3. ✅ Fix insecure file permissions in chunk-create.php
4. ✅ Improve URL validation to prevent dangerous protocols

### Short-term Improvements:
1. Implement proper CSRF protection
2. Add security headers
3. Fix session management issues
4. Improve error handling in production

### Long-term Enhancements:
1. Implement comprehensive input validation library
2. Add rate limiting
3. Implement proper logging and monitoring
4. Add automated security testing

---

## Conclusion

The QR Code Generator application has several security vulnerabilities that need to be addressed, particularly the critical path traversal vulnerability and disabled SSL verification. While the application uses some security best practices like input filtering, there are significant gaps in security controls that could be exploited by attackers.

**Overall Security Rating:** ⚠️ REQUIRES IMMEDIATE ATTENTION

The development team should prioritize fixing the critical issues before deploying to production environments.

---

**Auditor Notes:**
- No malicious code or backdoors were detected
- No hardcoded production credentials found
- Code appears to be from a legitimate commercial product (CodeCanyon)
- Vulnerabilities appear to be due to oversight rather than malicious intent

**Next Steps:**
1. Review and validate findings with development team
2. Create security patches for critical issues
3. Run automated security scanners (CodeQL, etc.)
4. Perform penetration testing after fixes
5. Establish secure development lifecycle practices
