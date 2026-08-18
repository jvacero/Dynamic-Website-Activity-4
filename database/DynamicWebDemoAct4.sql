-- DynamicWebDemoAct4.sql - schema for Dynamic_Website_Act4.
-- Usage: 1) CREATE DATABASE DynamicWebDemoAct4; 2) import this file;
-- 3) run database/seed.php once (passwords/emails must be hashed in PHP).

CREATE DATABASE IF NOT EXISTS DynamicWebDemoAct4
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE DynamicWebDemoAct4;

-- users: login credentials + role. email -> SHA-256 hash, password -> bcrypt,
-- username stays plain text (public-facing, not sensitive).
CREATE TABLE IF NOT EXISTS users (
    user_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email_hash      CHAR(64)     NOT NULL UNIQUE COMMENT 'SHA-256 hash of the lowercased email address',
    password_hash   VARCHAR(255) NOT NULL COMMENT 'bcrypt hash produced by PHP password_hash()',
    role            ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- profiles: one-to-one extension of users (cover photo, avatar, bio).
CREATE TABLE IF NOT EXISTS profiles (
    profile_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    display_name    VARCHAR(100) NOT NULL,
    bio             VARCHAR(255) DEFAULT '',
    cover_photo     VARCHAR(255) DEFAULT 'assets/img/default_cover.png',
    profile_photo   VARCHAR(255) DEFAULT 'assets/img/default_avatar.png',
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- posts: status/wall posts, with optional image attachment.
CREATE TABLE IF NOT EXISTS posts (
    post_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    content         TEXT NOT NULL,
    image           VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- friends: supports friend_lists.php search + a lightweight follow relationship.
CREATE TABLE IF NOT EXISTS friends (
    friend_row_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL COMMENT 'the user who added a friend',
    friend_user_id  INT UNSIGNED NOT NULL COMMENT 'the user being added',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_friend_pair (user_id, friend_user_id),
    CONSTRAINT fk_friends_user   FOREIGN KEY (user_id)        REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_friends_friend FOREIGN KEY (friend_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- sessions_log: feeds the admin dashboard's activity chart.
CREATE TABLE IF NOT EXISTS sessions_log (
    log_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    login_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
