# Book Exchange (XAMPP PHP + MySQL)

A simple book exchange platform featuring listings, exchange requests, per-exchange messaging, and post-exchange user ratings. Built for XAMPP on Windows, uses PHP sessions and MySQL (mysqli).

## Quick start

1) Import the schema and sample data
- Open phpMyAdmin and run `backend/database_book.sql` (creates DB `book_exchange`, tables, indexes, views, and sample data).

2) Configure DB connection
- Edit `backend/_inc/config.php` with your DB host, user, password, name, and SQL port. Note: This repo defaults to `SQL_PORT = 3309`. If your MySQL runs on 3306, change it here or update MySQL to listen on 3309.

3) Start and open the app
- Start Apache + MySQL in XAMPP.
- Home: `http://localhost/backend/list/index.php`
- Login: `http://localhost/backend/security/login.html`
- Register: `http://localhost/backend/security/register.html`

4) Try admin
- A sample admin user is created by the SQL: username `admin`, password `password`.
- Admin dashboard: `http://localhost/backend/admin/index.php` (after logging in as admin).

## Key features

- Listings: add/view books; filter by genre/author/availability
- Exchange requests: owner can approve/reject; either party can mark complete
- Messaging: conversation per exchange with unread counts and polling
- Ratings: participants rate each other after completion; one rating per exchange per user
- Session-aware navigation shared across all pages

## Architecture overview

- PHP includes (`backend/_inc`):
  - `config.php` (DB/env), `db.php` (mysqli connection), `auth.php` (login guard for APIs), `admin_auth.php` (admin guard + `esc()`), `helpers.php` (JSON responses, input parsing, sanitizers)
- Auth & profile (`backend/security`): login, register, logout, profile, and lightweight `session_info.php` used by the navbar
- Listings (`backend/list`): main browse page, add-book form and handler, book details, and exchange request launcher
- Exchanges (`backend/exchange`): requests UI/API to list/update/create requests
- Messages (`backend/messages`): conversations listing and chat UI; REST endpoints for list/get/send/mark read
- Ratings (`backend/ratings`): rate completed exchanges and view your ratings summary
- Admin (`backend/admin`): dashboard + CRUD-ish tables for users, books, listings, exchanges
- Assets (`backend/assets`): `nav.js` (dynamic navbar), `theme.css` (shared/admin theme)

## Database model (summary)

- `users` (username, email, password_hash, full_name, role user|admin, flags like is_active, last_login, JSON notification_preferences)
- `books` (title, author, isbn unique, genre, year, description, cover_image)
- `book_listings` (user_id, book_id, condition_rating, availability_status: available|pending|exchanged, listed_date, view_count, etc.)
- `exchange_requests` (requester_id, owner_id, requested_listing_id, optional offered_listing_id, status lifecycle, message + timestamps)
- `messages` (per-exchange chat: sender_id, receiver_id, content, sent_date, is_read)
- `user_ratings` (rater_id, rated_user_id, exchange_request_id, numeric rating 1..5, review)
- `notifications`, `user_favorites`, `system_settings`, `user_sessions` (available for extensions; minimal or no UI wiring yet)

The SQL file adds indexes for search/performance and creates helpful views like `available_books_view`, `user_stats_view`, `platform_stats_view`, `popular_genres_view`.

## User flows

1) Add books & list them
- Authenticated users add books via `add-book.html` → `add_book.php` inserts into `books` then creates a `book_listings` row.

2) Browse and request exchanges
- `list/index.php` shows “My Books” (if logged in) and “Available Books”.
- From details, a non-owner can open “Request Exchange” and send a request to the owner.

3) Manage exchange lifecycle
- Requests appear in `exchange/requests.html` under two panels: received (as owner) and sent (as requester), backed by `my_requests.php`.
- Actions: approve/reject (owner), cancel (requester), complete (either). Completing marks listing(s) as `exchanged`.

4) Message per exchange
- Conversations list (`messages/conversations.html`) shows last message and unread counts.
- Chat page (`messages/chat.html`) fetches messages, polls, and marks them read; sends new messages when status allows.

5) Rate after completion
- Participants of completed exchanges can rate each other once via `ratings/rate.html` → `submit_rating.php`.
- Profile (`security/profile.php`) shows your ratings summary, recent messages, and request snippets.

## API overview (selected)

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
  - POST `backend/ratings/submit_rating.php` { request_id, rating 1..5, review? }
  - GET  `backend/ratings/get_user_ratings.php?user_id` (omit to use current user)

All APIs return JSON and require a logged-in session (`$_SESSION['user_id']`).

## Frontend & navigation

- Bootstrap 5 via CDN.
- A shared dynamic navbar (`backend/assets/nav.js`) queries `backend/security/session_info.php` and renders:
  - Logged-in: Home, My Profile, Messages, Exchange Requests, Add Book, Logout
  - Guest: Home, Login, Register
- Add a placeholder with `data-dyn-nav` and include `../assets/nav.js` on any page to enable it.

## License

Internal/student project. 

