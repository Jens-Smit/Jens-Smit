# Vision_Gastro

Willkommen zu Vision_Gastro, einem Open-Source-Projekt basierend auf dem Symfony Framework.

## Übersicht

Vision_Gastro ist ein Gastronomie Managemnt Platform Dieses Projekt zielt darauf ab, Die Gastronomie zu Digitalisieren und Softwarlösungen auch für kleine Unternehmen erschwinglich und nutzbar zu machen.

## Installation

Um das Projekt lokal zu installieren, folgen Sie diesen Schritten:

1. Klonen Sie das Repository:
    ```bash
    git clone https://github.com/Jens-Smit/Vision_Gastro.git
    ```
2. Wechseln Sie in das Projektverzeichnis:
    ```bash
    cd Projektname
    ```
3. Installieren Sie die Abhängigkeiten mit Composer:
    ```bash
    composer install
    ```
4. Datenbank verbindung und Emial anpassen in der .env
     ```bash
   # DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
   #DATABASE_URL="mysql://user:PW@127.0.0.1:3306/db?serverVersion=8&charset=utf8mb4"
   # DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=14&charset=utf8"
    ```
5. Erstellen Sie die Datenbank:
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force
    ```
6. Notwendige Datensätze anlegen
    ```bash
    mysql -u deinBenutzername -p deineDatenbank
    INSERT INTO users (username, password) VALUES ('neuerBenutzer', 'geheimesPasswort');
    ```
7. Starten Sie den lokalen Server:
    ```bash
    symfony server:start
    ```

## Nutzung

anch der Installation kann über /Registrierung ein AdminUser angelegt werden.

## Beitrag

Beiträge sind willkommen! Bitte lesen Sie unsere CONTRIBUTING.md für Details zum Einreichen von Pull Requests.

## Lizenz

Dieses Projekt ist lizenziert unter der MIT-Lizenz - siehe die LICENSE Datei für Details.

## Kontakt

Bei Fragen oder Anregungen können Sie uns unter j.smit@hotmail.de erreichen.

## Danksagungen

Ein besonderer Dank geht an alle Mitwirkenden dieses Projekts.

