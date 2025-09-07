INSERT INTO users (email, username, password_hash) VALUES
('alice@example.com','alice','$2y$10$xxxxxxxxxxxxxxxxxxxxxxx'), 
('bob@example.com','bob','$2y$10$xxxxxxxxxxxxxxxxxxxxxxx');

INSERT INTO user_roles (user_id, role) VALUES (1,'admin'),(1,'player'),(2,'player');

INSERT INTO games (title, platform, genre) VALUES
('Snake Classic','web','Arcade'),
('Tic Tac Toe','web','Board'),
('Breakout JS','web','Arcade');

INSERT INTO tournaments (name, game_id, starts_at, ends_at, status) VALUES
('Snake Sprint Cup',1,NOW(),DATE_ADD(NOW(),INTERVAL 1 DAY),'running');
