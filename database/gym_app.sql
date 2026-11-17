-- ============================================================================
-- Barla Gym: database schema and sample data (Milestone 2)
-- Import in phpMyAdmin (Import tab) or from a terminal:
--     mysql -u root -p < database/gym_app.sql
-- The script can be run again at any time: it drops and re-creates all tables,
-- so you always start from clean sample data.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS gym_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_app;

-- Drop in reverse order of dependencies (children first).
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS memberships;
DROP TABLE IF EXISTS trainers;
DROP TABLE IF EXISTS membership_plans;
DROP TABLE IF EXISTS users;

-- ----------------------------------------------------------------------------
-- users: people who log in (admin or member)
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(60)  NOT NULL,
    last_name     VARCHAR(60)  NOT NULL,
    email         VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(30)  NULL,
    role          ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- membership_plans: what a member can buy (Basic, Premium, ...)
-- ----------------------------------------------------------------------------
CREATE TABLE membership_plans (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(60)   NOT NULL,
    price            DECIMAL(8, 2) NOT NULL,
    duration_days    INT           NOT NULL,
    description      TEXT          NULL,
    includes_classes BOOLEAN       NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_plans_name UNIQUE (name),
    CONSTRAINT chk_plans_price    CHECK (price >= 0),
    CONSTRAINT chk_plans_duration CHECK (duration_days > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- memberships: a plan bought by a user for a period of time
-- Deleting a user deletes his memberships; a plan that is in use cannot be deleted.
-- ----------------------------------------------------------------------------
CREATE TABLE memberships (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT  NOT NULL,
    plan_id    INT  NOT NULL,
    start_date DATE NOT NULL,
    end_date   DATE NOT NULL,
    status     ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_memberships_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_memberships_plan FOREIGN KEY (plan_id) REFERENCES membership_plans(id) ON DELETE RESTRICT,
    CONSTRAINT chk_memberships_dates CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- trainers: gym trainers who teach classes
-- ----------------------------------------------------------------------------
CREATE TABLE trainers (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(120) NOT NULL,
    specialty  VARCHAR(120) NOT NULL,
    bio        TEXT         NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- classes: one scheduled group class taught by one trainer
-- A trainer who still has classes cannot be deleted.
-- ----------------------------------------------------------------------------
CREATE TABLE classes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id   INT          NOT NULL,
    name         VARCHAR(120) NOT NULL,
    description  TEXT         NULL,
    starts_at    DATETIME     NOT NULL,
    duration_min INT          NOT NULL,
    capacity     INT          NOT NULL,
    room         VARCHAR(60)  NOT NULL,
    CONSTRAINT fk_classes_trainer FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE RESTRICT,
    CONSTRAINT chk_classes_duration CHECK (duration_min > 0),
    CONSTRAINT chk_classes_capacity CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- bookings: a user's place in a class (many-to-many between users and classes)
-- A user can book a class only once; a class with bookings cannot be deleted.
-- ----------------------------------------------------------------------------
CREATE TABLE bookings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    class_id   INT NOT NULL,
    status     ENUM('confirmed', 'cancelled', 'attended') NOT NULL DEFAULT 'confirmed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user  FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_bookings_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    CONSTRAINT uq_bookings_user_class UNIQUE (user_id, class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- Sample data
-- Test accounts (passwords are stored as bcrypt hashes, never in plain text):
--     admin@example.com   /  Admin123!    (role admin)
--     every other user    /  Member123!   (role member)
-- ============================================================================

INSERT INTO users (id, first_name, last_name, email, password_hash, phone, role, created_at) VALUES
(1, 'Admin',   'User',    'admin@example.com',  '$2y$10$8sRWRHBuMnqTSeNHO3l3oOgcXpq0/b.obDQlIKt4L65fEg6Pa6kOW', '+387 61 000 001', 'admin',  '2026-09-01 09:00:00'),
(2, 'Ana',     'Kovač',   'ana@example.com',    '$2y$10$jT5uQuSWkc9P45SMY77hk.YoFvlEI/kPOG4PSoQp2v1gUc.LlMsfu', '+387 61 000 002', 'member', '2026-09-05 10:15:00'),
(3, 'Nermin',  'Šabić',   'nermin@example.com', '$2y$10$jT5uQuSWkc9P45SMY77hk.YoFvlEI/kPOG4PSoQp2v1gUc.LlMsfu', '+387 61 000 003', 'member', '2026-09-12 14:30:00'),
(4, 'Selma',   'Hadžić',  'selma@example.com',  '$2y$10$jT5uQuSWkc9P45SMY77hk.YoFvlEI/kPOG4PSoQp2v1gUc.LlMsfu', NULL,               'member', '2026-10-02 08:45:00'),
(5, 'Kenan',   'Delić',   'kenan@example.com',  '$2y$10$jT5uQuSWkc9P45SMY77hk.YoFvlEI/kPOG4PSoQp2v1gUc.LlMsfu', '+387 61 000 005', 'member', '2026-10-20 17:20:00'),
(6, 'Mia',     'Petrović','mia@example.com',    '$2y$10$jT5uQuSWkc9P45SMY77hk.YoFvlEI/kPOG4PSoQp2v1gUc.LlMsfu', '+387 61 000 006', 'member', '2026-11-02 11:00:00');

INSERT INTO membership_plans (id, name, price, duration_days, description, includes_classes) VALUES
(1, 'Basic',   30.00, 30, 'Gym floor access and locker room. No group classes.',              FALSE),
(2, 'Premium', 50.00, 30, 'Gym floor access, unlimited group classes and sauna.',             TRUE),
(3, 'VIP',     90.00, 30, 'Everything in Premium plus 2 personal training sessions per month.', TRUE),
(4, 'Student', 25.00, 30, 'Discounted Premium-style plan for students with a valid student card.', TRUE);

INSERT INTO memberships (id, user_id, plan_id, start_date, end_date, status) VALUES
(1, 2, 2, '2026-10-01', '2026-10-31', 'expired'),
(2, 2, 2, '2026-11-01', '2026-12-01', 'active'),
(3, 3, 1, '2026-10-01', '2026-10-31', 'expired'),
(4, 3, 1, '2026-11-01', '2026-12-01', 'active'),
(5, 4, 2, '2026-10-15', '2026-11-14', 'expired'),
(6, 5, 3, '2026-11-10', '2026-12-10', 'active'),
(7, 6, 4, '2026-11-03', '2026-12-03', 'active');

INSERT INTO trainers (id, full_name, specialty, bio, created_at) VALUES
(1, 'Amar Hodžić', 'HIIT, Strength',    '8 years of experience. Certified strength and conditioning coach.', '2026-09-01 09:00:00'),
(2, 'Lejla Begić', 'Yoga, Pilates',     'RYT-500 certified yoga instructor.',                                '2026-09-01 09:05:00'),
(3, 'Emir Kovač',  'Spinning, Cardio',  'Former competitive cyclist.',                                       '2026-09-01 09:10:00');

INSERT INTO classes (id, trainer_id, name, description, starts_at, duration_min, capacity, room) VALUES
(1,  1, 'HIIT Blast',            'High intensity interval training.',          '2026-10-20 18:00:00', 45, 20, 'Studio A'),
(2,  2, 'Morning Yoga',          'Gentle start of the day.',                   '2026-10-21 07:30:00', 60, 15, 'Studio B'),
(3,  3, 'Spinning Power',        'Indoor cycling with intervals.',             '2026-10-22 19:00:00', 50, 18, 'Cycle room'),
(4,  1, 'Strength Basics',       'Introduction to barbell lifts.',             '2026-11-05 17:00:00', 60, 12, 'Weight room'),
(5,  1, 'HIIT Blast',            'High intensity interval training.',          '2026-11-24 18:00:00', 45, 20, 'Studio A'),
(6,  2, 'Sunrise Yoga (small group)', 'Small group, only 3 places.',           '2026-11-25 07:30:00', 60,  3, 'Studio B'),
(7,  3, 'Spinning Power',        'Indoor cycling with intervals.',             '2026-11-26 19:00:00', 50, 18, 'Cycle room'),
(8,  1, 'Strength Basics',       'Introduction to barbell lifts.',             '2026-11-30 17:00:00', 60, 12, 'Weight room'),
(9,  2, 'Pilates Flow',          'Core strength and mobility.',                '2026-12-02 09:00:00', 55, 14, 'Studio B'),
(10, 3, 'Cardio Burn',           'Mixed cardio circuit.',                      '2026-12-03 18:30:00', 40, 20, 'Studio A');

INSERT INTO bookings (id, user_id, class_id, status, created_at) VALUES
(1,  2, 1,  'attended',  '2026-10-15 10:00:00'),
(2,  4, 1,  'attended',  '2026-10-16 12:30:00'),
(3,  2, 2,  'attended',  '2026-10-18 09:00:00'),
(4,  4, 3,  'attended',  '2026-10-19 20:00:00'),
(5,  4, 2,  'cancelled', '2026-10-17 08:00:00'),
(6,  2, 4,  'attended',  '2026-11-01 18:00:00'),
(7,  6, 4,  'attended',  '2026-11-03 13:15:00'),
(8,  2, 5,  'confirmed', '2026-11-10 09:00:00'),
(9,  5, 5,  'confirmed', '2026-11-11 19:00:00'),
(10, 6, 5,  'confirmed', '2026-11-12 10:30:00'),
(11, 2, 6,  'confirmed', '2026-11-12 16:00:00'),
(12, 5, 6,  'confirmed', '2026-11-13 08:20:00'),
(13, 6, 6,  'confirmed', '2026-11-13 21:10:00'),
(14, 5, 7,  'confirmed', '2026-11-14 12:00:00'),
(15, 6, 8,  'cancelled', '2026-11-15 15:45:00'),
(16, 2, 9,  'confirmed', '2026-11-16 09:30:00');
