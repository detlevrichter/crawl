# CRAWL  
## Content Retrieval and Analysis Workflow Layer

CRAWL ist ein wissenschaftliches Projekt der TH Köln zur automatisierten Erfassung, Analyse und strukturierten Extraktion von Webinhalten.

Das System verwendet einen Headless Browser, um vordefinierte Webseiten (in einer Datenbank hinterlegt) automatisiert aufzurufen und deren Inhalte auszulesen. Die extrahierten Inhalte werden an ein Large Language Model (LLM) übergeben, das strukturierte Angebote anhand konfigurierbarer Eigenschaften extrahiert.

Die Eigenschaften der zu extrahierenden Inhalte sind datenbankgestützt konfigurierbar und erlauben eine flexible Anpassung an unterschiedliche Webseitenstrukturen.

---

## Funktionsübersicht

- Headless Browser-basierte Webseitenerfassung  
- Datenbankgesteuerte Verwaltung der Zielseiten  
- Übergabe der Inhalte an ein LLM  
- Strukturierte Extraktion von Angeboten  
- Konfigurierbare Extraktionseigenschaften  
- PHP-Backend und Node.js-Komponenten  

---

## Systemarchitektur (Überblick)

1. Webseiten werden in der Datenbank hinterlegt  
2. Der Headless Browser ruft diese Seiten automatisiert ab  
3. Inhalte werden extrahiert und an das LLM übergeben  
4. Das LLM strukturiert die Angebote gemäß Konfiguration  
5. Ergebnisse werden gespeichert und weiterverarbeitet  

---

## Voraussetzungen

- PHP >= 8.x  
- Composer  
- Node.js >= 18.x  
- npm  
- MySQL oder MariaDB  
- Zugriff auf ein LLM (API-Key erforderlich)  

---

## Installation

### 1. Repository klonen

```bash
git clone <repository-url>
cd crawl
```
### 2. PHP-Abhängigkeiten installieren
```
composer install
```

Die benötigten Pakete werden anhand der composer.json und composer.lock installiert.

### 3. Node.js-Abhängigkeiten installieren
```
npm install
```

Die benötigten Module werden anhand der package.json und package-lock.json installiert.

### 4. Konfiguration
.env Datei

Eine .env Datei muss angelegt bzw. angepasst werden. Beispiel:

```
hostname=localhost
database=Datenbank
username=Benutzer
password=sicheresPasswort
```

Diese Datei darf nicht ins Repository committed werden.

config.php

```
<?php
define('ROOT_DIR',__DIR__);
define('API_KEY', 'api-key');
define('API_ENDPOINT', 'https://llm');
define('MAX_STRLEN', 2048000);

```

### 5. Datenbank einrichten

Datenbank erstellen

Eine dump.sql Datei liegt im root Verzeichnis.

### 6. Anwendung starten



### Sicherheitshinweise

.env niemals ins Repository committen

API-Keys nicht öffentlich machen

Produktionsserver mit restriktiven Zugriffsrechten betreiben

## Wissenschaftlicher Kontext

CRAWL wurde im Rahmen eines Forschungsprojekts der TH Köln entwickelt und dient der automatisierten, strukturierten Informationsgewinnung aus Webquellen mittels KI-gestützter Analyse.

## Entwicklungsstatus

Forschungs- und Entwicklungsprojekt.
Nicht als produktives Web-Scraping-Framework gedacht.



