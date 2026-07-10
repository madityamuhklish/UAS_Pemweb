-- =====================================================================
-- SubsPilot - Skema PostgreSQL (untuk Neon / Vercel Postgres)
-- Dikonversi dari database/subspilot.sql (MySQL/MariaDB)
-- =====================================================================

BEGIN;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  fullname VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  photo VARCHAR(255) DEFAULT 'default.png',
  role VARCHAR(10) DEFAULT 'user' CHECK (role IN ('admin','user')),
  dark_mode SMALLINT DEFAULT 0,
  status VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active','inactive')),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: payment_methods
-- --------------------------------------------------------
CREATE TABLE payment_methods (
  id SERIAL PRIMARY KEY,
  method_name VARCHAR(100) NOT NULL,
  provider VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: subscriptions
-- --------------------------------------------------------
CREATE TABLE subscriptions (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  category_id INT DEFAULT NULL REFERENCES categories(id) ON DELETE SET NULL,
  payment_method_id INT DEFAULT NULL REFERENCES payment_methods(id) ON DELETE SET NULL,
  service_name VARCHAR(100) NOT NULL,
  logo VARCHAR(255) DEFAULT 'default.png',
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'IDR',
  billing_cycle VARCHAR(10) DEFAULT 'Monthly' CHECK (billing_cycle IN ('Weekly','Monthly','Quarterly','Yearly')),
  start_date DATE DEFAULT NULL,
  next_payment DATE DEFAULT NULL,
  auto_renew SMALLINT DEFAULT 1,
  status VARCHAR(10) DEFAULT 'Active' CHECK (status IN ('Active','Cancelled','Paused')),
  note TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: activity_logs
-- --------------------------------------------------------
CREATE TABLE activity_logs (
  id SERIAL PRIMARY KEY,
  user_id INT DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE,
  activity TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: payment_history
-- --------------------------------------------------------
CREATE TABLE payment_history (
  id SERIAL PRIMARY KEY,
  subscription_id INT NOT NULL REFERENCES subscriptions(id) ON DELETE CASCADE,
  payment_date DATE DEFAULT NULL,
  amount DECIMAL(10,2) DEFAULT NULL,
  status VARCHAR(10) DEFAULT 'Paid' CHECK (status IN ('Paid','Pending','Failed')),
  note TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: reminders
-- --------------------------------------------------------
CREATE TABLE reminders (
  id SERIAL PRIMARY KEY,
  subscription_id INT NOT NULL REFERENCES subscriptions(id) ON DELETE CASCADE,
  reminder_date DATE DEFAULT NULL,
  reminder_type VARCHAR(10) DEFAULT NULL CHECK (reminder_type IN ('H-7','H-3','H-1','Today')),
  is_sent SMALLINT DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: wishlist
-- --------------------------------------------------------
CREATE TABLE wishlist (
  id SERIAL PRIMARY KEY,
  user_id INT DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE,
  service_name VARCHAR(100) DEFAULT NULL,
  estimated_price DECIMAL(10,2) DEFAULT NULL,
  priority VARCHAR(10) DEFAULT 'Medium' CHECK (priority IN ('Low','Medium','High')),
  note TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: sessions (dipakai oleh DbSessionHandler.php)
-- --------------------------------------------------------
CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY,
  data TEXT,
  last_activity INTEGER NOT NULL
);

-- --------------------------------------------------------
-- Trigger: auto-update kolom updated_at (pengganti "ON UPDATE
-- current_timestamp()" milik MySQL, karena Postgres tidak punya fitur ini)
-- --------------------------------------------------------
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated_at
  BEFORE UPDATE ON users
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER trg_subscriptions_updated_at
  BEFORE UPDATE ON subscriptions
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- --------------------------------------------------------
-- Data awal
-- --------------------------------------------------------

INSERT INTO categories (category_name, description, created_at) VALUES
('Streaming', 'Layanan hiburan film dan video', '2026-07-09 09:13:52'),
('Music', 'Layanan musik digital', '2026-07-09 09:13:52'),
('AI Tools', 'Layanan artificial intelligence', '2026-07-09 09:13:52'),
('Cloud Storage', 'Penyimpanan cloud', '2026-07-09 09:13:52'),
('Design', 'Tools desain digital', '2026-07-09 09:13:52');

INSERT INTO payment_methods (method_name, provider, created_at) VALUES
('Bank Transfer', 'BCA', '2026-07-09 09:14:21'),
('E-Wallet', 'DANA', '2026-07-09 09:14:21'),
('Credit Card', 'Visa', '2026-07-09 09:14:21'),
('E-Wallet', 'Gopay', '2026-07-09 09:14:21');

INSERT INTO users (fullname, email, password, photo, role, dark_mode, status, created_at, updated_at) VALUES
('Kahlil Gibran', 'admin@gmail.com', '$2y$10$FVnbuc09nCm9EnsTf1BDfO4x1hQ4mGz3KxmIfoJMZF669gzacmPLW', 'default.png', 'user', 0, 'active', '2026-07-09 07:22:52', '2026-07-09 07:22:52');

INSERT INTO subscriptions (user_id, category_id, payment_method_id, service_name, logo, amount, currency, billing_cycle, start_date, next_payment, auto_renew, status, note, created_at, updated_at) VALUES
(1, 1, 1, 'Netflix', 'default.png', 186000.00, 'IDR', 'Monthly', '2026-06-15', '2026-07-15', 1, 'Active', 'Premium streaming', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(1, 2, 2, 'Spotify', 'default.png', 54000.00, 'IDR', 'Monthly', '2026-06-20', '2026-07-20', 1, 'Active', 'Music subscription', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(1, 3, 1, 'ChatGPT Plus', 'default.png', 300000.00, 'IDR', 'Monthly', '2026-06-25', '2026-07-25', 1, 'Active', 'AI assistant', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(1, 4, 2, 'Google Drive', 'default.png', 26000.00, 'IDR', 'Monthly', '2026-06-10', '2026-07-10', 1, 'Active', 'Cloud storage', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(1, 5, 3, 'Canva Pro', 'default.png', 95000.00, 'IDR', 'Monthly', '2026-06-30', '2026-07-30', 1, 'Paused', 'Design tool', '2026-07-09 09:15:05', '2026-07-09 09:15:05');

INSERT INTO activity_logs (user_id, activity, created_at) VALUES
(1, 'Menambahkan subscription Netflix', '2026-07-09 09:16:14'),
(1, 'Menambahkan subscription Spotify', '2026-07-09 09:16:14'),
(1, 'Mengubah data Canva Pro', '2026-07-09 09:16:14'),
(1, 'Login ke sistem', '2026-07-09 09:16:14');

INSERT INTO reminders (subscription_id, reminder_date, reminder_type, is_sent, created_at) VALUES
(1, '2026-07-08', 'H-7', 0, '2026-07-09 09:15:38'),
(2, '2026-07-17', 'H-3', 0, '2026-07-09 09:15:38'),
(3, '2026-07-24', 'H-1', 0, '2026-07-09 09:15:38'),
(1, '2026-07-08', 'H-7', 0, '2026-07-09 09:15:52'),
(2, '2026-07-17', 'H-3', 0, '2026-07-09 09:15:52'),
(3, '2026-07-24', 'H-1', 0, '2026-07-09 09:15:52');

INSERT INTO wishlist (user_id, service_name, estimated_price, priority, note, created_at) VALUES
(1, 'Adobe Creative Cloud', 800000.00, 'High', 'Untuk editing profesional', '2026-07-09 09:15:17'),
(1, 'YouTube Premium', 59000.00, 'Medium', 'Bebas iklan', '2026-07-09 09:15:17');

-- Samakan sequence auto-increment supaya ID berikutnya tidak bentrok
-- dengan data yang baru saja dimasukkan.
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories));
SELECT setval('payment_methods_id_seq', (SELECT MAX(id) FROM payment_methods));
SELECT setval('subscriptions_id_seq', (SELECT MAX(id) FROM subscriptions));
SELECT setval('activity_logs_id_seq', (SELECT MAX(id) FROM activity_logs));
SELECT setval('reminders_id_seq', (SELECT MAX(id) FROM reminders));
SELECT setval('wishlist_id_seq', (SELECT MAX(id) FROM wishlist));

COMMIT;
