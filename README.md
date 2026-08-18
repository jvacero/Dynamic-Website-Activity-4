# Dynamic_Website_Act4

A 2010-era social dashboard site, styled like a **Roblox pixel/stud** UI (chunky
blue-and-white pixel blocks, hard drop shadows, retro pixel font) built with
plain PHP + MySQLi + vanilla JS/CSS. Users can post blocky text/image status
updates, browse a music widget, search for other users, and admins get a
dedicated moderation panel with a live user-activity chart.

---

## 1. Tech Stack

| Layer      | Tech |
|------------|------|
| Backend    | PHP 8+ with `mysqli` (prepared statements everywhere) |
| Database   | MySQL / MariaDB (`DynamicWebDemoAct4`) |
| Frontend   | Vanilla CSS (pixel/stud theme) + vanilla JS |
| Google APIs| Material Icons font, Google Fonts (`Press Start 2P`), YouTube IFrame Player API, Google Charts (visualization) |

---

## 2. Folder Structure

```
Dynamic_Website_Act4/
├── database/
│   ├── DynamicWebDemoAct4.sql   # CREATE TABLE statements (schema only)
│   └── seed.php                 # One-time PHP seed script (2 admins + 10 users + 37 posts)
├── config/
│   ├── mysqli_connect.php       # Opens the shared $conn database connection
│   ├── session.php              # Secure session_start() wrapper
│   ├── cookies.php              # "Remember my username" cookie helpers
│   ├── auth.php                 # hash_email(), is_logged_in(), require_login(), require_admin()
│   ├── login_process.php        # Verifies login form POST, starts session
│   └── logout.php               # Destroys session + cookies
├── css/
│   ├── header.css                # Root theme variables + top nav bar
│   ├── footer.css                # Footer bar
│   ├── profile.css               # Profile card (missing dependency, added)
│   ├── dashboard.css             # Post feed + composer
│   ├── admin_dashboard.css       # Admin tables, chart container
│   ├── music_list.css            # Music widget
│   └── friend_lists.css          # User search widget
├── includes/
│   ├── header.php                # <head> + nav, loads Google Fonts/Icons
│   ├── footer.php                # Footer text: "Metang Inc. Corporated 2026"
│   ├── profile.php                # Current user's profile card + admin checkmark
│   ├── music_list.php            # YouTube playlist widget (play/pause/stop/next)
│   ├── friend_lists.php          # Search other users by username
│   ├── dashboard.php              # Post CRUD (create/edit/delete own posts) + feed
│   └── admin_dashboard.php       # Admin-only: remove any post, user list, posts-per-user chart
├── assets/
│   ├── img/                       # default_avatar.png, default_cover.png placeholders
│   └── uploads/                   # User-uploaded post images (.htaccess blocks script execution)
├── index.php                      # App shell: gates on login, wires includes together
├── login.php                      # Login form (posts to config/login_process.php)
├── register.php                   # Registration form + handler
└── README.md
```

---

## 3. Database

**Database name:** `DynamicWebDemoAct4`

| Table | Purpose |
|---|---|
| `users` | Login credentials + role (`admin` / `user`) |
| `profiles` | Display name, bio, cover photo, avatar (1-to-1 with `users`) |
| `posts` | Text + optional image wall posts |
| `friends` | Lightweight "added user" relationship (supports `friend_lists.php`) |
| `sessions_log` | Login timestamps, used for admin activity insight |

### Seed data
Running `database/seed.php` once creates:
- **2 admins**: `AdminRex` / `admin@dynamicweb.demo` / `Admin123!` and
  `AdminZee` / `zee.admin@dynamicweb.demo` / `Admin456!`
- **10 users**: `xXBuilderXx`, `pixel_princess`, `CoolKidz99`, `StudMaster2010`,
  `NoobSlayerXO`, `GlitchWizard`, `SkyBuilder21`, `RetroGamerX`, `PixelNova`,
  `BlockQueen88`
- **37 text posts** written in a casual 2010-social-network voice — every one
  of the 12 accounts already has at least one post published before you ever
  log in.

All 10 user passwords follow the pattern printed to screen when you run the
seed script (each is unique — see the script's final output for the full
list). Delete `seed.php` after running it once so it can't be re-run by accident.

---

## 4. Security

Per the project requirement, only **email** and **password** are hashed —
**username stays plain text** since it's public-facing and not sensitive:

- **Email** → `hash('sha256', strtolower(trim($email)))`. SHA-256 is used
  (not bcrypt) because it's deterministic, which lets `login_process.php`
  re-hash a typed email and look it up directly with a `WHERE email_hash = ?`
  query. Bcrypt can't be used here because it's randomized per call.
- **Password** → `password_hash($password, PASSWORD_BCRYPT)` on
  registration, verified with `password_verify()` on login. Bcrypt is
  salted automatically and is the PHP-recommended standard for passwords.
- **Username** → stored and displayed as plain text (`@username` everywhere).
- All queries use **mysqli prepared statements** (no raw string concatenation
  into SQL) to prevent SQL injection.
- Session cookies are `httponly` + `SameSite=Lax`; session ID is regenerated
  on every successful login to prevent session fixation.
- Uploaded post images are validated by MIME type, renamed to random
  filenames, and the `assets/uploads/` folder has script execution disabled
  via `.htaccess`.
- `require_admin()` re-checks the session role at the top of
  `admin_dashboard.php` itself, so the admin panel can't be reached even by
  guessing the URL.

---

## 5. Google APIs / Icons Used

| API | Where | Why |
|---|---|---|
| Google Fonts — Material Icons | `includes/header.php` (loaded site-wide) | Nav icons, admin verified checkmark, music controls, search icon |
| Google Fonts — Press Start 2P | `includes/header.php` | Pixel-style display font for the whole theme |
| YouTube IFrame Player API | `includes/music_list.php` | Powers the Play / Pause / Stop / Next music widget |
| Google Charts (visualization) | `includes/admin_dashboard.php` | Renders the "posts per user" bar graph |

---

## 6. Setup Instructions

1. Start Apache + MySQL (e.g. via XAMPP/WAMP/MAMP).
2. Create the database and import the schema:
   ```sql
   CREATE DATABASE DynamicWebDemoAct4;
   ```
   ```
   mysql -u root -p DynamicWebDemoAct4 < database/DynamicWebDemoAct4.sql
   ```
3. Edit `config/mysqli_connect.php` if your MySQL user/password differ from
   the defaults (`root` / empty password).
4. Visit `database/seed.php` once in your browser to create the 2 admins, 10
   demo users, and 37 posts. Delete the file afterwards.
5. Visit `index.php` — you'll be redirected to `login.php` since no session
   exists yet. Log in with the admin or any demo user's credentials above,
   or register a brand-new account via `register.php`.
6. Admins see an extra "Admin" nav link leading to `index.php?view=admin`.

---

## 7. Page Flow

```
login.php  <---------------------+
   |  (valid credentials)        | (not logged in)
   v                              |
config/login_process.php         |
   |                              |
   v                              |
index.php  ------------------------
   |
   |-- includes/header.php   (nav + Google Fonts/Icons)
   |-- includes/profile.php  (sidebar profile card)
   |-- includes/music_list.php (sidebar music widget)
   |-- includes/friend_lists.php (sidebar user search)
   |-- includes/dashboard.php        (default: post feed + composer)
   |     OR includes/admin_dashboard.php  (?view=admin, admins only)
   |-- includes/footer.php
   |
   v
config/logout.php  -->  back to login.php
```

---

## 8. Notes / Limitations (demo scope)

- The music playlist is a small hardcoded list of YouTube video IDs rather
  than pulled from the YouTube Data API, to avoid requiring an API key for
  this demo.
- `friends` table exists for future "add friend" functionality; the current
  `friend_lists.php` widget implements the required **search** feature only.
- Default avatar/cover images are simple generated pixel-art placeholders in
  `assets/img/`; swap them for real art assets as desired.
