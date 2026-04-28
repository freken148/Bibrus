CREATE TABLE Przedmioty (
    id_przedmiotu INT PRIMARY KEY AUTO_INCREMENT,
    nazwa VARCHAR(50)
);

CREATE TABLE Nauczyciele (
    id_nauczyciela INT PRIMARY KEY AUTO_INCREMENT,
    id_przedmiotu INT,
    imie VARCHAR(50),
    nazwisko VARCHAR(50),
    Haslo VARCHAR(100),
    FOREIGN KEY (id_przedmiotu) REFERENCES Przedmioty(id_przedmiotu)
);

CREATE TABLE Klasy (
    id_klasy INT PRIMARY KEY AUTO_INCREMENT,
    id_wychowawcy INT,
    nazwa VARCHAR(10),
    FOREIGN KEY (id_wychowawcy) REFERENCES Nauczyciele(id_nauczyciela)
);

CREATE TABLE Uczniowie (
    id_ucznia INT PRIMARY KEY AUTO_INCREMENT,
    id_klasy INT,
    imie VARCHAR(50),
    nazwisko VARCHAR(50),
    FOREIGN KEY (id_klasy) REFERENCES Klasy(id_klasy)
);

CREATE TABLE OcenyDictionary (
    wartosc FLOAT PRIMARY KEY,
    ocena VARCHAR(5)
);

INSERT INTO OcenyDictionary VALUES 
(1, '1'), (1.5, '1+'),
(2, '2'), (2.5, '2+'), (1.75, '2-'),
(3, '3'), (3.5, '3+'), (2.75, '3-'),
(4, '4'), (4.5, '4+'), (3.75, '4-'),
(5, '5'), (5.5, '5+'), (4.75, '5-'),
(6, '6'), (5.75, '6-');

CREATE TABLE Oceny (
    id_oceny INT PRIMARY KEY AUTO_INCREMENT,
    id_ucznia INT,
    id_przedmiotu INT,
    id_nauczyciela INT,
    data DATETIME(0),
    ocena FLOAT,
    waga INT CHECK (waga >= 1 && waga <= 5),
    komentarz VARCHAR(1000),
    FOREIGN KEY (id_ucznia) REFERENCES Uczniowie(id_ucznia),
    FOREIGN KEY (id_przedmiotu) REFERENCES Przedmioty(id_przedmiotu),
    FOREIGN KEY (id_nauczyciela) REFERENCES Nauczyciele(id_nauczyciela),
    FOREIGN KEY (ocena) REFERENCES OcenyDictionary(wartosc)
);

CREATE TABLE Frekwencja (
    id_obecnosci INT PRIMARY KEY AUTO_INCREMENT,
    id_ucznia INT,
    id_przedmiotu INT,
    data DATETIME(0),
    typ ENUM('Obecny', 'Usprawiedliwiony', 'Nieobecny', 'Zwolniony', 'Spóźniony') DEFAULT 'Obecny',
    FOREIGN KEY (id_ucznia) REFERENCES Uczniowie(id_ucznia),
    FOREIGN KEY (id_przedmiotu) REFERENCES Przedmioty(id_przedmiotu)
);

UPDATE nauczyciele SET Haslo = 'Sala332!';

CREATE TABLE LekcjeDictionary (
    numer_lekcji INT PRIMARY KEY AUTO_INCREMENT,
    godzina_lekcji TIME(0)
);

INSERT INTO LekcjeDictionary (godzina_lekcji)
VALUES
('07:10:00'),
('08:00:00'),
('08:50:00'),
('09:40:00'),
('10:40:00'),
('11:30:00'),
('12:20:00'),
('13:25:00'),
('14:15:00'),
('15:05:00'),
('15:55:00'),
('16:45:00'),
('17:35:00'),
('18:25:00');

CREATE TABLE dnitygodnia (
    numer_dnia INT PRIMARY KEY AUTO_INCREMENT,
    dzien ENUM('Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela')
);

INSERT INTO dnitygodnia (dzien) 
VALUES ('Poniedziałek'), ('Wtorek'), ('Środa'), ('Czwartek'), ('Piątek'), ('Sobota'), ('Niedziela');

CREATE TABLE planLekcji (
    id_lekcji INT PRIMARY KEY AUTO_INCREMENT,
    id_klasy INT,
    id_nauczyciela INT,
    id_przedmiotu INT,
    numer_lekcji INT,
    numer_sali INT,
    numer_dnia INT,
    FOREIGN KEY (id_klasy) REFERENCES klasy(id_klasy),
    FOREIGN KEY (id_nauczyciela) REFERENCES nauczyciele(id_nauczyciela),
    FOREIGN KEY (id_przedmiotu) REFERENCES przedmioty(id_przedmiotu),
    FOREIGN KEY (numer_lekcji) REFERENCES LekcjeDictionary(numer_lekcji),
    FOREIGN KEY (numer_dnia) REFERENCES dnitygodnia(numer_dnia)
);

CREATE TABLE terminarz (
    id_wydarzenia INT PRIMARY KEY AUTO_INCREMENT,
    id_klasy INT,
    id_nauczyciela INT,
    numer_dnia INT,
    typ_wydarzenia ENUM('sprawdzian', 'kartkówka', 'nieobecność', 'zastępstwo', 'informacja', 'inne', 'wywiadówka'),
    zakres_start DATETIME(0),
    zakres_end DATETIME(0),
    data_dodania DATETIME(0),
    FOREIGN KEY (id_klasy) REFERENCES klasy(id_klasy),
    FOREIGN KEY (id_nauczyciela) REFERENCES nauczyciele(id_nauczyciela),
    FOREIGN KEY (numer_dnia) REFERENCES dnitygodnia(numer_dnia)
);

SELECT nazwa, imie, nazwisko, numer_sali 
                        FROM planlekcji
                        INNER JOIN przedmioty ON planlekcji.id_przedmiotu = przedmioty.id_przedmiotu
                        INNER JOIN nauczyciele ON planlekcji.id_nauczyciela = nauczyciele.id_nauczyciela
                        WHERE numer_lekcji = 2 AND numer_dnia = 1 AND id_klasy = 1