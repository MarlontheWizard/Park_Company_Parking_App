CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    locale VARCHAR(50),
    picture TEXT
);


CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    parking_spot VARCHAR(50),  -- The ID or name of the parking spot
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10, 2),  -- Amount paid
    payment_method VARCHAR(50),  -- e.g. "credit_card", "paypal"
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);