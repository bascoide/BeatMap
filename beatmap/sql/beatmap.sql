CREATE DATABASE IF NOT EXISTS beatmap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE beatmap;

CREATE TABLE IF NOT EXISTS artists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  genre VARCHAR(100) DEFAULT NULL,
  council VARCHAR(100) DEFAULT NULL,
  district VARCHAR(100) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  is_confirmed TINYINT(1) DEFAULT 0,
  moderation_status ENUM('pending', 'approved', 'rejected', 'banned') NOT NULL DEFAULT 'approved',
  confirmation_token VARCHAR(128) DEFAULT NULL,
  token_expires DATETIME DEFAULT NULL,
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expires DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  profile_picture VARCHAR(255) DEFAULT NULL,
  social_links TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adicionar colunas para previews de música
ALTER TABLE artists
ADD COLUMN IF NOT EXISTS preview1 VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS preview2 VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS preview3 VARCHAR(255) DEFAULT NULL;
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS artist_upvotes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  artist_id INT NOT NULL,
  voter_token VARCHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_artist_voter (artist_id, voter_token),
  KEY idx_artist_upvotes_artist (artist_id),
  CONSTRAINT fk_artist_upvotes_artist
    FOREIGN KEY (artist_id)
    REFERENCES artists(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS artist_reports (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  artist_id INT NOT NULL,
  reporter_token VARCHAR(64) NOT NULL,
  reason VARCHAR(600) DEFAULT NULL,
  status ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_artist_reporter (artist_id, reporter_token),
  KEY idx_artist_reports_artist (artist_id),
  KEY idx_artist_reports_status (status),
  CONSTRAINT fk_artist_reports_artist
    FOREIGN KEY (artist_id)
    REFERENCES artists(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS map_first_access (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  account_type ENUM('user', 'artist') NOT NULL,
  account_id INT NOT NULL,
  first_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  visit_count INT NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_map_account (account_type, account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS artist_moderation_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  artist_id INT NOT NULL,
  previous_status VARCHAR(20) NOT NULL,
  new_status VARCHAR(20) NOT NULL,
  changed_by ENUM('system', 'artist', 'admin') NOT NULL DEFAULT 'system',
  changed_by_id INT DEFAULT NULL,
  change_note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_artist_mod_history_artist (artist_id),
  KEY idx_artist_mod_history_new_status (new_status),
  KEY idx_artist_mod_history_created_at (created_at),
  CONSTRAINT fk_artist_mod_history_artist
    FOREIGN KEY (artist_id)
    REFERENCES artists(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
