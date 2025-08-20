# Book Exchange (XAMPP PHP + MySQL)

A simple book exchange platform featuring listings, exchange requests, real-time-ish messaging, and user ratings. Built for XAMPP on Windows; uses PHP sessions for auth and MySQL.

## Quick start

1. Import the schema:
   - Open phpMyAdmin and run `backend/database_book.sql` (creates DB `book_exchange` and sample data).
2. Configure DB connection:
   - Edit `backend/_inc/config.php` with your DB host, user, password, name, and SQL port.
3. Start XAMPP (Apache + MySQL) and navigate to:
   - Home: `http://localhost/backend/list/index.php`
4. Login/Register:
   - Login: `http://localhost/backend/security/login.html`
   - Register: `http://localhost/backend/security/register.html`

## Key features

- Listings: add/view books; request exchanges
- Exchange requests: owner can approve/reject; either party can complete
- Messaging: conversation per exchange with unread counts
- Ratings: participants rate each other after completion
- Unified navigation: session-aware links across all pages

## Project structure (selected)

- `backend/_inc/` shared PHP includes: `db.php`, `auth.php`, `helpers.php`, `config.php`
- `backend/list/` listing UI and actions: `index.php`, `add-book.html`, `book-details.php`, `request_exchange.html`
- `backend/exchange/` requests UI/APIs: `requests.html`, `send_request.php`, `update_request.php`, `my_requests.php`
- `backend/messages/` messaging UI/APIs: `conversations.html`, `chat.html`, `send_message.php`, `get_messages.php`, `list_conversations.php`, `mark_read.php`
- `backend/ratings/` ratings UI/APIs: `rate.html`, `submit_rating.php`, `get_user_ratings.php`
- `backend/security/` auth and profile: `login.html`, `register.html`, `logout.php`, `profile.php`, `session_info.php`
- `backend/assets/` shared assets: `nav.js` (dynamic navbar)

## Navigation (dynamic)

A shared JS (`backend/assets/nav.js`) renders nav links based on session (via `backend/security/session_info.php`).

- Logged-in: Home, My Profile, Messages, Exchange Requests, Add Book, Logout
- Guest: Home, Login, Register

To use on a page, include a nav with `data-dyn-nav` and add the script:

```html
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="../list/index.php">📚 Book Exchange</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav" data-dyn-nav></div>
  </div>
</nav>
<script src="../assets/nav.js"></script>
```

## API overview

- Exchange
  - POST `backend/exchange/send_request.php` { listing_id, message? }
  - POST `backend/exchange/update_request.php` { request_id, action: approve|reject|cancel|complete }
  - GET  `backend/exchange/my_requests.php?role=owner|requester&status=pending|approved|rejected|completed`
- Messages
  - POST `backend/messages/send_message.php` { exchange_request_id, message }
  - GET  `backend/messages/get_messages.php?exchange_request_id`
  - POST `backend/messages/mark_read.php` { exchange_request_id }
  - GET  `backend/messages/list_conversations.php`
- Ratings
  - POST `backend/ratings/submit_rating.php` { request_id, rating 1..5, review? } – requires exchange status `completed`; only participants; prevents duplicates
  - GET  `backend/ratings/get_user_ratings.php?user_id` (omit to use current user)

All endpoints return JSON and require a logged-in session (`$_SESSION['user_id']`).

## Frontend pages

- Listings: `backend/list/index.php` (home), `add-book.html`, `book-details.php`, `request_exchange.html`
- Requests: `backend/exchange/requests.html` – manage owner/requester views
- Messages: `backend/messages/conversations.html`, `chat.html`
- Ratings: `backend/ratings/rate.html` – submit a rating post-completion
- Profile: `backend/security/profile.php` – shows messages preview, requests summaries, and ratings

## Notable behaviors and fixes

- Completion flow (`backend/exchange/update_request.php`):
  - Marks requested listing as exchanged; also marks offered listing if present
  - Archives into `exchange_requests_archive` only if that table exists (to avoid transaction failure)
- Ratings submit (`backend/ratings/submit_rating.php`):
  - Validates participants using `exchange_requests.owner_id` and `requester_id` directly (no listing join)

## Setup tips

- Ensure PHP sessions are working; login code must set `$_SESSION['user_id']` and optionally `$_SESSION['username']`.
- If you need archiving, create `exchange_requests_archive` table; otherwise it’s skipped.
- Update CORS or cookie settings only if hosting outside `localhost`.

## Development

- Static assets rely on Bootstrap 5 CDN.
- Keep relative paths aligned: pages under `backend/*` include `../assets/nav.js`.
- For new pages, add the `data-dyn-nav` container and include the nav script.

## License

Internal/student project. No warranty.
