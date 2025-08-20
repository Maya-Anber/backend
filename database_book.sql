

DROP DATABASE IF EXISTS book_exchange;
CREATE DATABASE book_exchange;
USE book_exchange;

-- ================================================
-- CORE TABLES
-- ================================================

-- 1. Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    privacy_contact_info ENUM('public', 'private') DEFAULT 'private',
    email_verified BOOLEAN DEFAULT FALSE,
    notification_preferences JSON
);

-- 2. Books table
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    genre VARCHAR(50),
    publication_year SMALLINT,
    description TEXT,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Book listings
CREATE TABLE book_listings (
    listing_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    condition_rating ENUM('Poor', 'Fair', 'Good', 'Very Good', 'Excellent') NOT NULL,
    condition_notes TEXT,
    availability_status ENUM('available', 'pending', 'exchanged') DEFAULT 'available',
    listed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    exchange_preferences TEXT,
    is_featured BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- 4. Exchange requests
CREATE TABLE exchange_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    requester_id INT NOT NULL,
    owner_id INT NOT NULL,
    requested_listing_id INT NOT NULL,
    offered_listing_id INT NULL,
    request_message TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_date TIMESTAMP NULL,
    completion_date TIMESTAMP NULL,
    meeting_location VARCHAR(255),
    meeting_date TIMESTAMP NULL,
    
    FOREIGN KEY (requester_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (requested_listing_id) REFERENCES book_listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (offered_listing_id) REFERENCES book_listings(listing_id) ON DELETE SET NULL
);

-- 5. Messages
CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    exchange_request_id INT NULL,
    subject VARCHAR(255),
    message_content TEXT NOT NULL,
    sent_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (exchange_request_id) REFERENCES exchange_requests(request_id) ON DELETE SET NULL
);

-- 6. User ratings
CREATE TABLE user_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    rater_id INT NOT NULL,
    rated_user_id INT NOT NULL,
    exchange_request_id INT NOT NULL,
    rating TINYINT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    rating_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rater_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (rated_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (exchange_request_id) REFERENCES exchange_requests(request_id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (rater_id, exchange_request_id)
);

-- 7. Notifications
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('exchange_request', 'message', 'rating', 'status_update') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 8. User favorites
CREATE TABLE user_favorites (
    favorite_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES book_listings(listing_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, listing_id)
);

-- 9. System settings
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 10. User sessions
CREATE TABLE user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ================================================
-- INDEXES FOR PERFORMANCE
-- ================================================

-- Search functionality
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_books_genre ON books(genre);
CREATE INDEX idx_books_title_author ON books(title, author);

-- Listings
CREATE INDEX idx_listings_availability ON book_listings(availability_status);
CREATE INDEX idx_listings_user ON book_listings(user_id);
CREATE INDEX idx_listings_featured ON book_listings(is_featured);

-- Exchange requests
CREATE INDEX idx_exchange_requester ON exchange_requests(requester_id);
CREATE INDEX idx_exchange_owner ON exchange_requests(owner_id);
CREATE INDEX idx_exchange_status ON exchange_requests(status);

-- Messages
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_messages_unread ON messages(receiver_id, is_read);

-- Ratings
CREATE INDEX idx_ratings_user ON user_ratings(rated_user_id);

-- Notifications
CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read);

-- Favorites
CREATE INDEX idx_favorites_user ON user_favorites(user_id);

-- Sessions
CREATE INDEX idx_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_sessions_expires ON user_sessions(expires_at);

-- Settings
CREATE INDEX idx_settings_key ON system_settings(setting_key);

-- ================================================
-- USEFUL VIEWS (Create after inserting sample data)
-- ================================================

-- ================================================
-- SAMPLE DATA
-- ================================================

-- Insert sample users with all required fields
INSERT INTO users (username, email, password_hash, full_name, role, privacy_contact_info, email_verified, notification_preferences) VALUES
('sarah_reader', 'sarah.johnson@email.com', '$2y$10$example_hash_sarah', 'Sarah Johnson', 'user', 'public', TRUE, '{"email": true, "in_app": true}'),
('mike_books', 'mike.chen@email.com', '$2y$10$example_hash_mike', 'Mike Chen', 'user', 'private', TRUE, '{"email": true, "in_app": false}'),
('emma_novels', 'emma.davis@email.com', '$2y$10$example_hash_emma', 'Emma Davis', 'user', 'public', TRUE, '{"email": false, "in_app": true}'),
('alex_collector', 'alex.rodriguez@email.com', '$2y$10$example_hash_alex', 'Alex Rodriguez', 'user', 'private', TRUE, '{"email": true, "in_app": true}'),
('jenny_literature', 'jenny.wilson@email.com', '$2y$10$example_hash_jenny', 'Jenny Wilson', 'user', 'public', TRUE, '{"email": true, "in_app": true}'),
('omar_books', 'omar.hassan@email.com', '$2y$10$example_hash_omar', 'Omar Hassan', 'user', 'public', TRUE, '{"email": false, "in_app": false}'),
('fatma_reader', 'fatma.ali@email.com', '$2y$10$example_hash_fatma', 'Fatma Ali', 'user', 'private', TRUE, '{"email": true, "in_app": true}'),
('ahmed_novels', 'ahmed.mohamed@email.com', '$2y$10$example_hash_ahmed', 'Ahmed Mohamed', 'user', 'public', TRUE, '{"email": true, "in_app": true}');

-- Add an admin user with a high user_id to avoid conflicting with sample user_id references
-- Password is 'password'
INSERT INTO users (user_id, username, email, password_hash, full_name, role, privacy_contact_info, email_verified, notification_preferences)
VALUES (999, 'admin', 'admin@













example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEaElw72Jec5n3kqS6T7O/8WlHKu', 'Site Administrator', 'admin', 'private', TRUE, '{"email": true, "in_app": true}');

-- Insert sample books
INSERT INTO books (title, author, isbn, genre, publication_year, description, cover_image) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Classic Literature', 1925, 'A classic American novel about the Jazz Age and the American Dream', 'great_gatsby.jpg'),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'Fiction', 1960, 'A story of racial injustice and childhood innocence in the American South', 'mockingbird.jpg'),
('1984', 'George Orwell', '9780451524935', 'Dystopian Fiction', 1949, 'A dystopian social science fiction novel about totalitarianism', '1984.jpg'),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 'Romance', 1813, 'A romantic novel of manners set in Georgian England', 'pride_prejudice.jpg'),
('The Catcher in the Rye', 'J.D. Salinger', '9780316769174', 'Coming of Age', 1951, 'A controversial novel about teenage rebellion and alienation', 'catcher_rye.jpg'),
('Harry Potter and the Philosophers Stone', 'J.K. Rowling', '9780439708180', 'Fantasy', 1997, 'First book in the beloved Harry Potter series', 'harry_potter1.jpg'),
('The Hobbit', 'J.R.R. Tolkien', '9780547928227', 'Fantasy', 1937, 'A fantasy adventure novel that precedes The Lord of the Rings', 'hobbit.jpg'),
('Dune', 'Frank Herbert', '9780441172719', 'Science Fiction', 1965, 'Epic science fiction novel set in the distant future', 'dune.jpg'),
('The Handmaids Tale', 'Margaret Atwood', '9780385490818', 'Dystopian Fiction', 1985, 'Dystopian novel about womens rights and reproductive freedom', 'handmaids_tale.jpg'),
('Beloved', 'Toni Morrison', '9781400033416', 'Historical Fiction', 1987, 'Pulitzer Prize-winning novel about the lasting effects of slavery', 'beloved.jpg'),
('The Alchemist', 'Paulo Coelho', '9780062315007', 'Philosophy', 1988, 'A philosophical novel about following your dreams', 'alchemist.jpg'),
('One Hundred Years of Solitude', 'Gabriel García Márquez', '9780060883287', 'Magical Realism', 1967, 'A multi-generational saga of the Buendía family', 'hundred_years.jpg'),
('The Kite Runner', 'Khaled Hosseini', '9781594631931', 'Contemporary Fiction', 2003, 'A story of friendship, betrayal, and redemption set in Afghanistan', 'kite_runner.jpg'),
('Life of Pi', 'Yann Martel', '9780156027328', 'Adventure', 2001, 'A philosophical novel about survival and faith', 'life_of_pi.jpg'),
('The Book Thief', 'Markus Zusak', '9780375842207', 'Historical Fiction', 2005, 'A story narrated by Death during World War II Germany', 'book_thief.jpg');

-- Insert sample book listings
INSERT INTO book_listings (user_id, book_id, condition_rating, condition_notes, exchange_preferences, is_featured, view_count) VALUES
-- Sarah's books (user_id: 1)
(1, 1, 'Very Good', 'Slight wear on cover, pages in excellent condition', 'Looking for fantasy or sci-fi novels', TRUE, 15),
(1, 2, 'Good', 'Some highlighting throughout, but readable', 'Any classic literature', FALSE, 8),
(1, 11, 'Excellent', 'Like new condition, never been read', 'Philosophy or self-help books', TRUE, 22),

-- Mike's books (user_id: 2)
(2, 3, 'Excellent', 'Perfect condition, first edition', 'Dystopian or thriller novels', TRUE, 31),
(2, 4, 'Fair', 'Cover has some damage, pages are fine', 'Romance or historical fiction', FALSE, 5),
(2, 12, 'Very Good', 'Minor shelf wear, great condition overall', 'Literary fiction', FALSE, 12),

-- Emma's books (user_id: 3)
(3, 5, 'Very Good', 'Minor shelf wear', 'Young adult or coming of age stories', FALSE, 18),
(3, 6, 'Good', 'Well-loved copy, all pages intact', 'Fantasy series books', TRUE, 45),
(3, 13, 'Very Good', 'Paperback in great shape', 'Contemporary fiction', FALSE, 9),

-- Alex's books (user_id: 4)
(4, 7, 'Excellent', 'Hardcover in pristine condition', 'Rare or collectible books', TRUE, 27),
(4, 8, 'Very Good', 'Paperback in great shape', 'Science fiction classics', FALSE, 16),
(4, 14, 'Good', 'Some page yellowing due to age', 'Adventure or survival stories', FALSE, 7),

-- Jenny's books (user_id: 5)
(5, 9, 'Good', 'Some page yellowing due to age', 'Contemporary fiction', FALSE, 11),
(5, 10, 'Very Good', 'Hardcover with dust jacket', 'Award-winning literature', FALSE, 14),
(5, 15, 'Excellent', 'Brand new condition', 'Historical fiction', TRUE, 33),

-- Omar's books (user_id: 6)
(6, 1, 'Fair', 'Well-read copy, some annotations', 'Any fiction', FALSE, 6),
(6, 7, 'Good', 'Paperback edition, good condition', 'Fantasy or adventure', FALSE, 10),

-- Fatma's books (user_id: 7)
(7, 4, 'Very Good', 'Hardcover edition, minimal wear', 'Classic literature', FALSE, 13),
(7, 6, 'Excellent', 'Collectors edition, never read', 'Fantasy or young adult', TRUE, 28),

-- Ahmed's books (user_id: 8)
(8, 8, 'Good', 'Standard paperback, readable condition', 'Science fiction', FALSE, 8),
(8, 13, 'Very Good', 'Great condition, no markings', 'Fiction or non-fiction', FALSE, 17);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'BookExchange Egypt', 'string', 'Website name', TRUE),
('site_description', 'Exchange books with people in your city', 'string', 'Website description', TRUE),
('max_listings_per_user', '50', 'integer', 'Maximum listings per user', FALSE),
('upload_max_size', '5242880', 'integer', 'Maximum upload size in bytes (5MB)', FALSE),
('allowed_image_types', '["jpg", "jpeg", "png", "gif"]', 'json', 'Allowed image file types', FALSE),
('email_verification_required', 'true', 'boolean', 'Require email verification for new accounts', FALSE),
('maintenance_mode', 'false', 'boolean', 'Enable maintenance mode', FALSE),
('contact_email', 'admin@bookexchange.eg', 'string', 'Contact email address', TRUE),
('max_exchange_radius', '100', 'integer', 'Maximum exchange radius in KM', TRUE),
('featured_listing_duration', '30', 'integer', 'Featured listing duration in days', FALSE),
('session_timeout', '7200', 'integer', 'Session timeout in seconds (2 hours)', FALSE),
('password_min_length', '8', 'integer', 'Minimum password length', TRUE),
('enable_ratings', 'true', 'boolean', 'Enable user rating system', TRUE),
('auto_approve_exchanges', 'false', 'boolean', 'Auto-approve exchange requests', FALSE);

-- Insert sample exchange requests
INSERT INTO exchange_requests (requester_id, owner_id, requested_listing_id, offered_listing_id, request_message, status, response_date, meeting_location, meeting_date, completion_date) VALUES
(2, 1, 1, 4, 'Hi Sarah! I would love to exchange my 1984 for your Great Gatsby. Both are in great condition!', 'completed', '2024-01-15 14:30:00', 'Cairo Public Library', '2024-01-20 14:00:00', '2024-01-20 14:15:00'),
(3, 2, 5, 7, 'Would you be interested in trading Pride and Prejudice for my Catcher in the Rye?', 'pending', NULL, NULL, NULL, NULL),
(4, 3, 8, 9, 'I have a Hobbit that I would trade for your Harry Potter book.', 'rejected', '2024-01-16 09:15:00', NULL, NULL, NULL),
(1, 4, 10, 2, 'Interested in your Dune book. I can offer To Kill a Mockingbird in return.', 'completed', '2024-01-14 16:45:00', 'Luxor City Center', '2024-01-18 16:00:00', '2024-01-18 16:20:00'),
(5, 1, 3, 13, 'Would you like to exchange my Handmaids Tale for your Alchemist?', 'approved', '2024-01-17 10:30:00', 'Aswan Library', '2024-01-25 15:00:00', NULL),
(8, 3, 8, 17, 'Your Harry Potter book looks great! Want to trade for my Kite Runner?', 'completed', '2024-01-13 13:10:00', 'Giza Book Café', '2024-01-19 17:00:00', '2024-01-19 17:10:00');

-- Insert sample messages
INSERT INTO messages (sender_id, receiver_id, exchange_request_id, subject, message_content, is_read) VALUES
(2, 1, 1, 'Re: Book Exchange Request', 'Thanks for accepting my request! When would be a good time to meet up?', TRUE),
(1, 2, 1, 'Re: Book Exchange Request', 'How about this Saturday at the downtown library? 2 PM works for me.', TRUE),
(1, 2, 1, 'Meeting Confirmation', 'Perfect! See you at Cairo Public Library on Saturday at 2 PM. Ill bring The Great Gatsby.', FALSE),
(4, 3, 3, 'Re: Exchange Rejection', 'No problem! Let me know if you change your mind about the Hobbit trade.', TRUE),
(1, 4, 4, 'Exchange Complete', 'Great doing business with you! The Dune book is exactly as described. Pleasure trading!', TRUE),
(5, 1, 5, 'Exchange Interest', 'Hi! I saw your listing for The Alchemist. Still available for trade?', FALSE),
(8, 3, 6, 'Trade Completed', 'Thanks for the Harry Potter book! My daughter will love it. Great condition as promised.', TRUE);

-- Insert sample user ratings
INSERT INTO user_ratings (rater_id, rated_user_id, exchange_request_id, rating, review_text) VALUES
(2, 1, 1, 5, 'Excellent communication and the book was exactly as described. Very punctual for meetup. Highly recommend!'),
(1, 2, 1, 5, 'Great person to trade with. Very friendly and professional. Book was in perfect condition. Will trade again!'),
(1, 4, 4, 4, 'Good exchange overall. Book condition was as described, though meetup was slightly delayed. Still recommended.'),
(4, 1, 4, 5, 'Very professional and honest about book condition. Quick responses and smooth transaction. A+!'),
(8, 3, 6, 5, 'Amazing! Book was even better than described. Super friendly and made the process very easy. Perfect trader!'),
(3, 8, 6, 4, 'Good experience overall. Ahmed was honest about the book condition and communication was clear.');

-- Insert sample notifications
INSERT INTO notifications (user_id, type, title, content, is_read, related_id) VALUES
(1, 'exchange_request', 'New Exchange Request', 'Mike wants to exchange 1984 for your Great Gatsby', TRUE, 1),
(2, 'status_update', 'Exchange Approved', 'Sarah approved your exchange request for The Great Gatsby', TRUE, 1),
(3, 'exchange_request', 'New Exchange Request', 'Alex wants to exchange The Hobbit for your Harry Potter', TRUE, 3),
(4, 'message', 'New Message', 'You have a new message from Sarah about Dune exchange', FALSE, 5),
(1, 'rating', 'New Rating', 'Mike left you a 5-star rating for your recent exchange', FALSE, 1),
(5, 'status_update', 'Exchange Approved', 'Sarah approved your request for The Alchemist', FALSE, 5);

-- Insert sample favorites (using valid listing_ids)
INSERT INTO user_favorites (user_id, listing_id) VALUES
(1, 8), -- Sarah favorites Emma's Harry Potter
(1, 10), -- Sarah favorites Alex's Hobbit  
(2, 15), -- Mike favorites Jenny's Book Thief
(3, 4), -- Emma favorites Mike's 1984
(3, 16), -- Emma favorites Omar's Great Gatsby
(4, 1), -- Alex favorites Sarah's Great Gatsby
(5, 9), -- Jenny favorites Alex's Hobbit
(6, 8), -- Omar favorites Emma's Harry Potter
(7, 3), -- Fatma favorites Sarah's Alchemist
(8, 15); -- Ahmed favorites Jenny's Book Thief

-- ================================================
-- CREATE VIEWS AFTER DATA INSERTION
-- ================================================

-- Available books with owner info
CREATE VIEW available_books_view AS
SELECT 
    bl.listing_id,
    bl.condition_rating,
    bl.condition_notes,
    bl.listed_date,
    bl.exchange_preferences,
    bl.is_featured,
    bl.view_count,
    b.title,
    b.author,
    b.isbn,
    b.genre,
    b.publication_year,
    b.cover_image,
    u.username,
    u.full_name,
    CASE 
        WHEN u.privacy_contact_info = 'public' THEN u.email
        ELSE NULL 
    END as contact_email
FROM book_listings bl
JOIN books b ON bl.book_id = b.book_id
JOIN users u ON bl.user_id = u.user_id
WHERE bl.availability_status = 'available' AND u.is_active = TRUE;

-- User statistics
CREATE VIEW user_stats_view AS
SELECT 
    u.user_id,
    u.username,
    u.full_name,
    u.registration_date,
    COUNT(DISTINCT bl.listing_id) as total_listings,
    COUNT(DISTINCT CASE WHEN bl.availability_status = 'available' THEN bl.listing_id END) as available_books,
    COUNT(DISTINCT er.request_id) as total_exchanges,
    COALESCE(AVG(ur.rating), 0) as average_rating,
    COUNT(DISTINCT ur.rating_id) as total_ratings
FROM users u
LEFT JOIN book_listings bl ON u.user_id = bl.user_id
LEFT JOIN exchange_requests er ON u.user_id = er.owner_id AND er.status = 'completed'
LEFT JOIN user_ratings ur ON u.user_id = ur.rated_user_id
WHERE u.is_active = TRUE
GROUP BY u.user_id;

-- Platform statistics view
CREATE VIEW platform_stats_view AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE is_active = TRUE) as active_users,
    (SELECT COUNT(*) FROM book_listings WHERE availability_status = 'available') as available_books,
    (SELECT COUNT(*) FROM exchange_requests WHERE status = 'completed') as completed_exchanges,
    (SELECT COUNT(*) FROM exchange_requests WHERE status = 'pending') as pending_requests,
    (SELECT COUNT(*) FROM messages WHERE sent_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as messages_this_week,
    (SELECT AVG(rating) FROM user_ratings) as average_platform_rating,
    (SELECT COUNT(*) FROM book_listings WHERE listed_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_listings_month;

-- Popular genres view
CREATE VIEW popular_genres_view AS
SELECT 
    b.genre,
    COUNT(bl.listing_id) as total_listings,
    COUNT(CASE WHEN bl.availability_status = 'available' THEN 1 END) as available_listings,
    SUM(bl.view_count) as total_views,
    AVG(bl.view_count) as avg_views_per_book
FROM books b
JOIN book_listings bl ON b.book_id = bl.book_id
WHERE b.genre IS NOT NULL
GROUP BY b.genre
ORDER BY total_listings DESC;