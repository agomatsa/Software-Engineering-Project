CREATE DATABASE IF NOT EXISTS vintage_cars CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vintage_cars;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS subscribers;
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE cars (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    make VARCHAR(120) NOT NULL,
    model VARCHAR(120) NOT NULL,
    year SMALLINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    history TEXT NULL,
    image_url VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cars_make (make),
    INDEX idx_cars_model (model),
    INDEX idx_cars_year (year),
    INDEX idx_cars_make_model_year (make, model, year),
    CONSTRAINT fk_cars_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_cars_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    date_subscribed DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subscribers_date (date_subscribed)
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read') NOT NULL DEFAULT 'new',
    date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_messages_date (date),
    INDEX idx_messages_status (status)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20) NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_email (customer_email),
    INDEX idx_orders_status (status),
    INDEX idx_orders_date (created_at),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    car_id INT UNSIGNED NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_car (car_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_car FOREIGN KEY (car_id) REFERENCES cars(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$Q9Sm2JqmFnA4AO25YQZZw.M3CM6olOnAqPU6SlvTAsJRIlB41tGF2', 'admin'),
('editor', '$2y$10$wGgGsn4O9GhQmMMsO9sKAeCbUoSBfZJG29Y8c93zd5rEM2tVhA5iy', 'editor');

INSERT INTO cars (make, model, year, description, history, image_url, price, created_by, updated_by) VALUES
('Ford', 'Mustang Boss 302', 1969, 'A high-revving homologation special built for Trans-Am dominance.', 'Engineered as a direct response to Chevrolet on the racetrack, the Boss 302 became a muscle-era icon.', 'Ford Mustang boss 302.jpeg', 85000.00, 1, 1),
('Ford', 'GT40', 1966, 'A low-slung endurance racer transformed into road-legal legend status.', 'The GT40 famously delivered consecutive Le Mans victories and reshaped racing history.', 'Ford GT40.jpeg', 7500000.00, 1, 1),
('Jaguar', 'XJ40', 1986, 'Executive comfort with unmistakable British restraint.', 'The XJ40 ushered Jaguar into a more technically modern period with fresh design and electronics.', 'Jaguar XJ40.jpeg', 45000.00, 1, 1),
('Cadillac', 'Seville', 1975, 'American luxury refined for a changing world market.', 'Seville arrived as Cadillac\'s answer to European luxury sedans and set a new segment standard in the US.', 'Cadillac Seville.jpeg', 52000.00, 1, 1),
('Lincoln', 'Continental', 1961, 'A presidential silhouette recognized worldwide.', 'The fourth-generation Continental became iconic for its slab-sided profile and rear-hinged doors.', 'Lincoln continental.jpeg', 95000.00, 1, 1),
('Mercury', 'Cyclone', 1970, 'Fastback muscle with aggressive period styling.', 'Cyclone models blended NASCAR influence with street performance credibility.', 'Mercury Cyclone.jpeg', 65000.00, 1, 1),
('Tucker', '48', 1948, 'A visionary sedan loaded with safety-first thinking.', 'Though production was short, Tucker\'s ideas influenced automotive design for decades.', 'Tucker 48.jpeg', 2800000.00, 1, 1),
('Aston Martin', 'Lagonda', 1976, 'Angular luxury that embraced futuristic interior technology.', 'Lagonda stood out in the 1970s for radical wedge styling and digital dashboard experimentation.', 'Aston Martin Lagonda.jpeg', 750000.00, 1, 1),
('Ford', 'Thunderbird', 1957, 'A personal luxury coupe with jet-age confidence.', 'The Thunderbird helped define post-war American prestige motoring beyond the sports car formula.', 'Ford Thunderbird.jpeg', 98000.00, 1, 1);

-- Default credentials after importing:
-- admin / admin123
-- editor / editor123
