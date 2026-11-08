# 13 — Security Specification: Novixa WorkOS

## 1. Compliance Standard: OWASP ASVS 5.0 Alignment

Novixa WorkOS applies the OWASP Application Security Verification Standard (ASVS) Level 2 controls suitable for sensitive internal business applications.

---

## 2. Authentication & Session Architecture

1. **SPA Session Security:**
   - Sessions utilize cryptographically strong, 256-bit random tokens stored in database or secure storage.
   - Session cookies are marked `HttpOnly` (inaccessible to JavaScript), `Secure` (transmitted exclusively over HTTPS), and `SameSite=Lax` (preventing cross-site request forgery in top-level navigations).
   - Absolute session timeout is set to 7 days, with idle timeout at 24 hours.
2. **Brute-Force & Rate Limiting:**
   - Login endpoint (`POST /api/v1/auth/login`) is strictly rate-limited using Laravel's throttle middleware: 5 attempts per minute per IP/username tuple.
   - Exceeding the threshold triggers exponential lockouts and logs an audit security event.
3. **No Credential Harvesting:**
   - Authentication errors return generic responses: `"Invalid credentials"` / `"بيانات الدخول غير صحيحة"`, preventing account enumeration.
4. **Password Security:**
   - Passwords must be at least 10 characters long and satisfy complexity checks (uppercase, lowercase, numbers, symbols).
   - Passwords are hashed using bcrypt or Argon2id with calibrated work factors.

---

## 3. Authorization & IDOR Defenses

1. **Policy Gateways:**
   - Every controller route is guarded by `Authorize` middleware and calls `$this->authorize('action', $model)`.
   - Insecure Direct Object Reference (IDOR) attacks are defended by enforcing tenant and user context on all database lookups.
2. **File Download Authorization:**
   - File uploads are placed in `storage/app/private/attachments/` outside the web root.
   - Direct public HTTP access to uploads is blocked at the Nginx configuration level.
   - Downloads occur via `/api/v1/attachments/{id}/download`, which verifies:
     - Is the user authenticated?
     - Does the user have permission to view the task or project owning the attachment?
     - If verified, the server streams the file with sanitized headers.

---

## 4. Input Validation & Content Security

1. **Server-Side Validation:**
   - 100% of API inputs pass through dedicated Form Request classes.
   - HTML inputs are stripped or sanitized using allow-listed tag parsers before storage.
2. **SQL Injection Prevention:**
   - All queries use Eloquent ORM or parameterized PDO statements. Raw concatenated SQL strings are strictly prohibited.
3. **HTTP Security Headers (Nginx / Middleware):**
   ```http
   X-Frame-Options: DENY
   X-Content-Type-Options: nosniff
   X-XSS-Protection: 1; mode=block
   Referrer-Policy: strict-origin-when-cross-origin
   Permissions-Policy: geolocation=(), camera=(), microphone=()
   Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self';
   ```

---

## 5. File Upload Governance

- File extension whitelist: PDF, DOCX, XLSX, PNG, JPG, JPEG, WEBP, ZIP.
- Executable files (`.exe`, `.sh`, `.php`, `.js`, `.bat`) are strictly rejected.
- Maximum file size: 25 MB per upload.
- MIME type verification inspects magic bytes via PHP's `fileinfo` extension, not relying on client-supplied headers alone.
- Uploaded files are stored with random UUID filenames to eliminate path traversal vulnerabilities.
