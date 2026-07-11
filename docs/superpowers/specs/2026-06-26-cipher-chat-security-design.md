# Cipher Chat Strengthening & Security Core — Design Spec

**Date:** 2026-06-26  
**Author:** ZCode  
**Status:** Approved  

## Overview

Rebuild cipher-chat into a powerful, secure messaging system and create a shared security core for all Cipher OS modules.

## Part 1: Shared Security Core (`cipher-core/security.php`)

A single file included by all modules providing:

| Feature | Implementation |
|---------|---------------|
| **CSRF Protection** | Random token per session; validate on all POST requests |
| **Input Sanitizer** | `sanitize($input)` — strips tags, trims, escapes for SQL |
| **Rate Limiter** | IP + Session based; configurable limits per endpoint |
| **Auth Guard** | Check `$_SESSION['user_authenticated']`; redirect to login if missing |
| **Secure Headers** | X-Content-Type-Options: nosniff, X-Frame-Options: DENY, CSP |
| **File Upload Guard** | MIME type check, max size, sanitized filename, extension whitelist |

## Part 2: Database Schema

Replace single `messages` table with normalized schema:

```sql
chat_users: id, username, display_name, avatar_color, last_seen, is_online
chat_rooms: id, name, slug, description, created_by, is_private, created_at
chat_room_members: id, room_id, user_id, role, joined_at
chat_messages: id, room_id, user_id, message, type, file_path, reply_to_id, edited_at, deleted_at, created_at
chat_reactions: id, message_id, user_id, emoji
chat_message_reads: message_id, user_id, read_at
chat_typing: room_id, user_id, last_typed_at
```

## Part 3: API Endpoints (`cipher-chat/api/`)

All endpoints return JSON, use prepared statements, CSRF validation, rate limiting:

- `api/send.php` — Send message
- `api/fetch.php` — Fetch new messages (by last_id, room_id)
- `api/rooms.php` — List rooms + create room
- `api/edit.php` — Edit message
- `api/delete.php` — Soft delete message
- `api/react.php` — Add/remove reaction
- `api/reply.php` — Save reply reference
- `api/typing.php` — Update typing status
- `api/online.php` — Update online status
- `api/search.php` — Search messages
- `api/upload.php` — Secure file upload (MIME, size, name sanitization)

## Part 4: Frontend UI

Full rewrite preserving Cipher OS dark theme:

- **Sidebar (left)**: Room list + online users
- **Messages area**: Bubbles with reply, reactions, edit/delete
- **Input bar**: Text, file, reply-to
- **Typing indicator**: "typing..." at bottom
- **Online/Seen status**: Green dot + read ticks
- **Search**: Ctrl+K to search messages
- **Polling**: Optimized with last_id (3-second interval)

## Part 5: Project-Wide Security Fixes

| Issue | Fix |
|-------|-----|
| Hardcoded password in login.php | `password_hash()` + store in DB |
| Root DB user without password | Limited DB user |
| Exposed credentials in cipher-chat/db.php | Merge with main DB |
| Session fixation | `session_regenerate_id()` after login |
| No CSRF protection | Token in all forms |
