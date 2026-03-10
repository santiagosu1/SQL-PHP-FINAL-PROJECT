USE ticket_api;

-- USERS TABLE

INSERT INTO users (email,password,first_name,last_name,role) VALUES
('santisu05@gmail.com','changeme','Santiago','Suarez','user'),
('admin@ticketapp.pe','admin123','System','Admin','admin');

-- EVENTS TABLE

INSERT INTO events VALUES
('evt-001','Shadys 45 Years - Symphonic Gala',
'Celebrate 45 years of Shadys with symphonic music',
'2026-02-13','20:00','Lima','National Grand Theater',
150,'concerts',800,234),

('evt-002','THE HOUSE - Electronic Music Event',
'Electronic music party with international DJs',
'2026-01-29','22:00','Lima','Peru Arena',
80,'electronic',2000,450),

('evt-003','Le Paris - Elegant Dinner Experience',
'French gastronomic experience',
'2026-01-31','19:30','Lima','Westin Hotel',
120,'gastronomy',150,89),

('evt-004','Valentines Plans 2026',
'Romantic Valentine event',
'2026-02-14','18:00','Lima','Exposition Park',
10,'romantic',500,123),

('evt-005','Montecarlo Circus Huanuco 2026',
'Family circus show',
'2026-01-28','16:00','Huanuco','Heraclio Tapia Stadium',
20,'family',3000,1456),

('evt-006','Peruvian Rock Festival Vol 02',
'Rock festival with 10 bands',
'2026-02-15','14:00','Trujillo','Mansiche Stadium',
50,'concerts',5000,2340),

('evt-007','SUU RABANAL The Law Tour',
'Concert tour event',
'2026-02-08','21:00','Callao','Convention Center',
50,'concerts',1200,678),

('evt-008','SALSA CUMBIA Yaipen Brothers',
'Salsa and cumbia concert',
'2026-02-22','20:00','Chiclayo','Gran Chimu Coliseum',
30,'concerts',2500,890);

-- TICKETS TABLE

INSERT INTO tickets (user_id,event_id,quantity,total_price) VALUES
(1,'evt-005',1,60),
(1,'evt-002',1,180),
(1,'evt-003',4,480),
(1,'evt-004',1,25);

-- AUDIT LOGS

INSERT INTO audit_logs (user_id,action,entity,entity_id) VALUES
(2,'CREATE','events','evt-001'),
(2,'CREATE','events','evt-002'),
(2,'CREATE','events','evt-003'),
(2,'CREATE','events','evt-004');