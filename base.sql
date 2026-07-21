CREATE TABLE prefixes (
    num VARCHAR(3) UNIQUE,
    operateur VARCHAR(20)
)

CREATE TABLE numeros (
    num VARCHAR(10) UNIQUE
);

CREATE TABLE cles (
    num VARCHAR(10) UNIQUE,
    operateur VARCHAR(20)
);

CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    num VARCHAR(10),
    montant DECIMAL(10, 2),
    frais DECIMAL(10, 2),
    commission DECIMAL(10, 2),
    dateTransaction TIMESTAMP
);

CREATE TABLE transferts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idTransactionE INT,
    idTransactionD INT,
    Foreign Key (idTransactionE) REFERENCES transactions(id),
    Foreign Key (idTransactionD) REFERENCES transactions(id)
);

CREATE TABLE frais (
    montantMin DECIMAL(10, 2),
    montantMax DECIMAL(10, 2),
    montantFrais DECIMAL(10, 2)
);

CREATE TABLE commissions (
    pourcentage DECIMAL(4, 2)
);

CREATE TABLE promotion (
    pourcentage DECIMAL(4, 2)
);

INSERT INTO prefixes VALUES
('032', 'Orange'),
('033', 'Airtel'),
('034', 'Yas'),
('037', 'Orange'),
('038', 'Yas');

INSERT INTO cles VALUES
('0000000000', 'Orange'),
('1111111111', 'Yas'),
('2222222222', 'Airtel');

INSERT INTO frais VALUES
(100, 1000, 50),
(1001, 5000, 50),
(5001, 10000, 100),
(10001, 25000, 200),
(25001, 50000, 400),
(50001, 100000, 800),
(100001, 250000, 1500),
(250001, 500000, 1500),
(500001, 1000000, 2500),
(1000001, 2000000, 3000);

INSERT INTO numeros VALUES
('0321122334'),
('0325566778'),
('0334455667'),
('0338899001'),
('0342233445'),
('0347788990'),
('0371112233'),
('0388887766');

INSERT INTO transactions (num, montant, dateTransaction, idFrais) VALUES
('0321122334', 5000.00, '2026-07-20 08:00:00'),
('0334455667', -5000.00, '2026-07-20 08:02:00'),

('0342233445', 12000.00, '2026-07-20 09:15:00'),
('0325566778', -12000.00, '2026-07-20 09:16:00'),

('0388887766', 60000.00, '2026-07-20 10:30:00'),
('0347788990', -60000.00, '2026-07-20 10:31:00');

-- Dépôt
('0321122334', 15000.00, '2026-07-20 11:00:00'),
('0338899001', 50000.00, '2026-07-20 11:15:00'),
('0371112233', 150000.00, '2026-07-20 12:00:00'),

-- Retrait
('0342233445', -2500.00, '2026-07-20 13:45:00'),
('0334455667', -10000.00, '2026-07-20 14:20:00'),
('0388887766', -45000.00, '2026-07-20 15:10:00');

INSERT INTO transferts (idTransactionE, idTransactionD) VALUES
(2, 1), -- Le numéro 0334455667 a envoyé 5000 Ar au 0321122334
(4, 3), -- Le numéro 0325566778 a envoyé 12000 Ar au 0342233445
(6, 5); -- Le numéro 0347788990 a envoyé 60000 Ar au 0388887766

INSERT INTO commissions VALUES (1);

INSERT INTO promotion VALUES (10);