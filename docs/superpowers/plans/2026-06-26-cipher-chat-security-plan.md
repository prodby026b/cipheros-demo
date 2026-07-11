# Cipher Chat + Security Core Implementation Plan

> **For agentic workers:** Use subagent-driven-development to implement this plan.

**Goal:** Rebuild cipher-chat into a powerful, secure messaging system with rooms, reactions, online/typing/seen status, and create a shared security core for all Cipher OS modules.

**Architecture:** PHP backend with optimized AJAX polling (last_id pattern), normalized MySQL schema, single shared security.php included by all modules. Frontend is vanilla JS with Cipher OS dark theme.

**Tech Stack:** PHP 8, MySQL (mysqli), vanilla JavaScript, CSS3

---

## File Structure

```
cipher-core/security.php          — Shared security core (CSRF, sanitizer, rate limiter, auth guard, secure headers)
cipher-chat/db.php                 — Updated DB connection (merge with main DB)
cipher-chat/schema.sql             — Database schema creation
cipher-chat/index.php              — Full rewrite: chat UI
cipher-chat/api/send.php           — Send message
cipher-chat/api/fetch.php          — Fetch new messages (optimized polling)
cipher-chat/api/rooms.php          — List/create rooms
cipher-chat/api/edit.php           — Edit message
cipher-chat/api/delete.php         — Soft delete message
cipher-chat/api/react.php          — Add/remove reaction
cipher-chat/api/reply.php          — Save reply reference
cipher-chat/api/typing.php         — Update typing status
cipher-chat/api/online.php         — Update online status
cipher-chat/api/search.php         — Search messages
cipher-chat/api/upload.php         — Secure file upload
login.php                           — Fix: session_regenerate_id + password_hash
```

---

### Task 1: Create Security Core

**Files:**
- Create: `cipher-core/security.php`

- [ ] **Step 1: Write security.php with CSRF, sanitizer, rate limiter, auth guard, secure headers, file upload guard**

Complete implementation including:
- `generate_csrf_token()` / `verify_csrf()` — token in $_SESSION
- `sanitize($input, $type='string')` — strip tags, trim, escape
- `rate_limit($key, $max=30, $seconds=60)` — file-based rate limiting
- `require_auth()` — check session, redirect if not authenticated
- `send_secure_headers()` — X-Content-Type-Options, X-Frame-Options, CSP
- `secure_upload($file, $allowed_types, $max_size)` — MIME check, sanitize filename

### Task 2: Create Chat Database Schema

**Files:**
- Create: `cipher-chat/schema.sql`
- Modify: `cipher-chat/db.php`

- [ ] **Step 1: Write schema.sql with all tables (chat_users, chat_rooms, chat_room_members, chat_messages, chat_reactions, chat_message_reads, chat_typing)**
- [ ] **Step 2: Update cipher-chat/db.php to use main cipher_os database and include security.php**

### Task 3: Create Chat API Endpoints

**Files:**
- Create: `cipher-chat/api/send.php`
- Create: `cipher-chat/api/fetch.php`
- Create: `cipher-chat/api/rooms.php`
- Create: `cipher-chat/api/edit.php`
- Create: `cipher-chat/api/delete.php`
- Create: `cipher-chat/api/react.php`
- Create: `cipher-chat/api/reply.php`
- Create: `cipher-chat/api/typing.php`
- Create: `cipher-chat/api/online.php`
- Create: `cipher-chat/api/search.php`
- Create: `cipher-chat/api/upload.php`

All endpoints: JSON response, CSRF verified, sanitized input, prepared statements, rate limited.

### Task 4: Rebuild Chat Frontend

**Files:**
- Create: `cipher-chat/index.php` (full rewrite)

UI components: sidebar with rooms + online users, message area with bubbles/reactions/reply, input bar, typing indicator, search (Ctrl+K), optimized polling with last_id.

### Task 5: Fix Login Security

**Files:**
- Modify: `login.php`

- [ ] **Step 1: Add session_regenerate_id() after successful login, add password_hash/verify, add CSRF to form**

### Task 6: Integration Test & Verify

- [ ] **Step 1: Verify schema imports correctly, all API endpoints respond, frontend loads, auth guard works**
