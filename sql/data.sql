USE ticket_api;

INSERT INTO users (email, password, first_name, last_name, role) VALUES
('santisu05@gmail.com', '$2y$12$DGaTVBjsUYw6f0.10ivwqe3uQBxYSYawrzZ0g7h9qivHUh7li8juq', 'Santiago', 'Suarez', 'user'),
('admin@ticketapp.pe', '$2y$12$4iecCDG9Um9A2KRZgsJXFeFh0MlhHY.SQgTrMGsuwo1/T0qB8S9k.', 'System', 'Admin', 'admin');

-- EVENTS TABLE
INSERT INTO events (
    id, title, description, event_date, event_time, location, venue,
    price, category, available_tickets, sold_tickets
) VALUES
('evt-001', 'Shadys 45 Years - Symphonic Gala', 'Celebrate 45 years of Shadys with symphonic music', '2026-02-13', '20:00:00', 'Lima', 'National Grand Theater', 150.00, 'concerts', 800, 234),
('evt-002', 'THE HOUSE - Electronic Music Event', 'Electronic music party with international DJs', '2026-01-29', '22:00:00', 'Lima', 'Peru Arena', 80.00, 'electronic', 2000, 450),
('evt-003', 'Le Paris - Elegant Dinner Experience', 'French gastronomic experience', '2026-01-31', '19:30:00', 'Lima', 'Westin Hotel', 120.00, 'gastronomy', 150, 89),
('evt-004', 'Valentines Plans 2026', 'Romantic Valentine event', '2026-02-14', '18:00:00', 'Lima', 'Exposition Park', 10.00, 'romantic', 500, 123),
('evt-005', 'Montecarlo Circus Huanuco 2026', 'Family circus show', '2026-01-28', '16:00:00', 'Huanuco', 'Heraclio Tapia Stadium', 20.00, 'family', 3000, 1456),
('evt-006', 'Peruvian Rock Festival Vol 02', 'Rock festival with 10 bands', '2026-02-15', '14:00:00', 'Trujillo', 'Mansiche Stadium', 50.00, 'concerts', 5000, 2340),
('evt-007', 'SUU RABANAL The Law Tour', 'Concert tour event', '2026-02-08', '21:00:00', 'Callao', 'Convention Center', 50.00, 'concerts', 1200, 678),
('evt-008', 'SALSA CUMBIA Yaipen Brothers', 'Salsa and cumbia concert', '2026-02-22', '20:00:00', 'Chiclayo', 'Gran Chimu Coliseum', 30.00, 'concerts', 2500, 890);

-- TICKETS TABLE
INSERT INTO tickets (user_id, event_id, quantity, total_price) VALUES
(1, 'evt-005', 1, 60.00),
(1, 'evt-002', 1, 180.00),
(1, 'evt-003', 4, 480.00),
(1, 'evt-004', 1, 25.00),
(1, 'evt-001', 2, 300.00),
(1, 'evt-006', 1, 50.00),
(1, 'evt-007', 2, 100.00),
(1, 'evt-008', 1, 30.00);

-- AUDIT LOGS TABLE
INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES
(2, 'CREATE', 'events', 'evt-001'),
(2, 'CREATE', 'events', 'evt-002'),
(2, 'CREATE', 'events', 'evt-003'),
(2, 'CREATE', 'events', 'evt-004'),
(2, 'CREATE', 'events', 'evt-005'),
(2, 'CREATE', 'events', 'evt-006'),
(2, 'CREATE', 'events', 'evt-007'),
(2, 'CREATE', 'events', 'evt-008'),
(1, 'CREATE', 'tickets', '1'),
(1, 'CREATE', 'tickets', '2'),
(1, 'CREATE', 'tickets', '3'),
(1, 'CREATE', 'tickets', '4');