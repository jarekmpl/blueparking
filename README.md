# Blueparking - System Rezerwacji Miejsc Parkingowych

Blueparking to dedykowana, lekka aplikacja webowa stworzona dla firmy Bluerank, służąca do zarządzania i rezerwacji miejsc parkingowych. Została zaprojektowana w nowoczesnym stylu (Dark Mode / Glassmorphism) z naciskiem na prostotę i szybkość działania.

## Główne Funkcjonalności

### Dla Użytkowników
* **Rezerwacja Miejsc:** Użytkownicy mogą rezerwować dostępne miejsca parkingowe na dzisiaj oraz maksymalnie **5 kolejnych dni roboczych** w przód.
* **Zwalnianie Miejsc (Właściciele):** Pracownicy posiadający przypisane stałe miejsca mogą zgłaszać swoje nieobecności (zwalniać miejsce do puli ogólnej) z wyprzedzeniem aż do **20 dni**.
* **Zmiana Hasła:** Każdy zalogowany użytkownik ma możliwość samodzielnej zmiany swojego hasła.
* **Kalendarz:** Przejrzysty widok kalendarza pozwalający łatwo zorientować się w dostępności miejsc. Wyszarzone daty oznaczają terminy, dla których rezerwacja nie jest jeszcze dostępna.

### Dla Administratorów
* **Zarządzanie Użytkownikami:** Dodawanie, edycja i usuwanie kont użytkowników. Przypisywanie stałych miejsc parkingowych oraz resetowanie haseł.
* **Zarządzanie Miejscami:** Tworzenie i edycja (zmiana nazwy) miejsc parkingowych w systemie.
* **Zarządzanie Uprawnieniami:** Możliwość nadawania uprawnień administracyjnych innym pracownikom.

## Technologia

Aplikacja nie wykorzystuje ciężkich frameworków (brak Node.js, NPM, React itp.). Została zbudowana na solidnych, klasycznych fundamentach, co czyni ją niezwykle łatwą we wdrożeniu na praktycznie każdym współczesnym hostingu.

* **Frontend:** Vanilla HTML5, CSS3, JavaScript (ES6+). Komunikacja asynchroniczna z użyciem `fetch API`.
* **Backend:** PHP 8.0+.
* **Baza Danych:** SQLite (lekka, plikowa baza danych wbudowana w PHP poprzez PDO).

## Wdrażanie na Serwer (Deployment)

Aby uruchomić aplikację na docelowym serwerze z obsługą PHP:

1. **Skopiuj pliki:** Prześlij pliki projektu na serwer (np. poprzez `git clone` lub `git pull`).
2. **Uprawnienia (CHMOD):** Upewnij się, że serwer WWW ma prawa zapisu do folderu `api/`. Jest to absolutnie kluczowe, aby skrypty PHP mogły utworzyć lub modyfikować plik bazy danych `database.sqlite`. (np. `chmod 775 api` na systemach Linux).
3. **Inicjalizacja Bazy Danych:**
   Przy pierwszym uruchomieniu, otwórz w przeglądarce skrypt inicjujący:
   `http://twoj-adres-strony.pl/api/init.php`
   *Skrypt ten utworzy plik bazy danych, zdefiniuje strukturę tabel i wygeneruje konta użytkowników na podstawie zdefiniowanej tablicy.*
4. **Zabezpieczenie pliku init:** Po poprawnej inicjalizacji zaleca się usunięcie pliku `api/init.php` z serwera produkcyjnego, aby uniknąć przypadkowego zresetowania bazy danych przez osoby trzecie!

## Bezpieczeństwo

* **Ochrona bazy:** Plik bazy SQLite (`api/database.sqlite`) jest chroniony przed bezpośrednim pobraniem z poziomu przeglądarki dzięki plikowi `api/.htaccess`.
* **Hashowanie Haseł:** Wszystkie hasła w bazie danych są solone i hashowane przy użyciu bezpiecznego algorytmu `bcrypt` (poprzez funkcję PHP `password_hash`).
* **Endpointy Administratora:** Operacje w plikach katalogu `api/admin/` są sprawdzane dedykowaną funkcją `requireAdmin()`, która weryfikuje status konta w sesji serwera.

## Zrzuty Ekranu / UI
Interfejs opiera się na efekcie matowego szkła (Glassmorphism), wykorzystując ciemne odcienie granatu i niebieskie akcenty, aby zmaksymalizować czytelność i nadać aplikacji wygląd "Premium".

## Rozwój (Kolejne Kroki)
W przypadku potrzeby dalszej rozbudowy, do systemu można dołączyć np.:
* Wysyłanie powiadomień e-mail (zapomniane hasło).
* Eksport danych/raportów do plików CSV z poziomu panelu administratora.
* Bardziej szczegółowe logi zdarzeń w panelu admina (tzw. audit log).
