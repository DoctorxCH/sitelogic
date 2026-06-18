Hier ist die vollständige, bereinigte und um die getroffenen Entscheidungen ergänzte Projektdokumentation für das Repository. Die KI-Funktionen sind wie vereinbart im Ausblick platziert, und der finale Produktname **SiteLogic** ist durchgängig integriert.

---

# Projektdokumentation: SiteLogic

## 1. Projektübersicht & Zielsetzung

**SiteLogic** ist eine mandantenfähige Qualitätscheck-Plattform, die speziell für Unternehmen im Bereich Netzinfrastruktur, Tiefbau und Inhouse-Installationen entwickelt wird. Die Anwendung wird als Progressive Web App (PWA) bereitgestellt.

Das System optimiert den Qualitätssicherungsprozess im Feld: Monteure dokumentieren Arbeitsschritte direkt vor Ort mittels vordefinierter Checklisten und Fotobelegen, während Bau- und Projektleiter die Qualitätsabnahme digital, ortsunabhängig und effizient auf Ebene der einzelnen Checklisten durchführen.

### Hauptmerkmale:

* **Flexibles Deployment:** Cloud-Betrieb (SaaS) oder als vollständig isolierte On-Premise-Installation (Docker-basiert) auf eigenen Servern für Kunden mit strengen Datenschutzauflagen.
* **Offline-First:** Vollständige Datenerfassung, Statusänderung und Bildzwischenspeicherung im Feld ohne aktive Internetverbindung.
* **Granulare Relevanz-Steuerung:** Flexibles Aktivieren/Deaktivieren kompletter Checklisten oder einzelner Prüfpunkte über On/Off-Schalter je nach den realen Vor-Ort-Gegebenheiten.

---

## 2. Zielgruppen & Rollenkonzept

Das System unterscheidet strikt zwischen drei Benutzerrollen mit spezifischen Rechten und Workflows:

| Rolle | Kernaufgaben in der App | Erlaubte Status-Aktionen |
| --- | --- | --- |
| **User (Monteur / Subunternehmer)** | Datenerfassung im Feld, Foto-Upload, Behebung von Mängeln bei Rückweisung. | `open`, `in work`, `completed` |
| **Projektleiter / Bauleiter** | Überwachung des Baufortschritts, Prüfung der Foto-Dokumentation, Freigabe oder Ablehnung von Checklisten. | Alle Statuswerte + `approved`, `rejected` |
| **Admin** | Systemkonfiguration, Template-Design, Benutzerverwaltung, manueller Datenimport sowie manuelle Auftragserstellung. | Alle Statuswerte + manuelle Auftragserstellung |

---

## 3. Systemarchitektur & Technologie-Stack

Die Architektur sichert maximale Performance im Offline-Betrieb und gewährleistet eine einfache Portierbarkeit auf On-Premise-Infrastrukturen.

* **Backend & Admin-Panel:** **Laravel 11+** in Kombination mit **Filament PHP**. Filament steuert das gesamte Backoffice für Administratoren und Bauleiter (Listenansichten, Filter, Bulk-Actions).
* **Frontend (Feld-App):** **PWA (Progressive Web App)** basierend auf HTML5, CSS3 und JavaScript.
* **Datenbank:** **PostgreSQL** (relationale Struktur mit nativer `JSONB`-Unterstützung für flexible Asset-Metadaten).
* **Lokaler Speicher (Client):** **IndexedDB** zur persistenten Speicherung von Aufträgen, Checklisten und Strukturdaten direkt im Browser des Endgeräts.
* **Datei- / Bildspeicher:** S3-kompatibler Objektspeicher (**AWS S3** für die Cloud-Variante / **MinIO** für On-Premise-Szenarien).

---

## 4. Prozess- und Status-Logik

### 4.1. Lebenszyklus einer Checkliste

Jede einer Auftragskartei zugewiesene Checkliste durchläuft ein definiertes Zustandssystem:

$$[open] \longrightarrow [in\ work] \longrightarrow [completed] \longrightarrow [approved]$$

$$\uparrow \qquad\qquad\qquad\qquad\qquad \mid$$

$$\mathrel{\llcorner} \text{───────── } [rejected] \text{ ◄─────────┘}$$

* **`open`:** Die Checkliste wurde generiert; die Arbeit vor Ort hat noch nicht begonnen.
* **`in work`:** Der Monteur erfasst Daten. Die Arbeit kann jederzeit unterbrochen und lokal gespeichert werden. Die Liste verbleibt so lange im Status `in work`, bis sie aktiv vom Monteur abgeschlossen wird.
* **`completed`:** Der Monteur hat alle aktiven Pflichtpunkte ausgefüllt. Die Editierrechte für den Monteur werden gesperrt. Die Checkliste ist bereit zur Prüfung durch den Bauleiter.
* **`approved`:** Vom Bauleiter erfolgreich abgenommen.
* **`rejected`:** Vom Bauleiter unter Angabe eines Pflichtkommentars abgelehnt. Die Liste geht zurück an den Monteur in den Status `in work` zur Mängelbehebung.
* **`storno` / `error`:** Administrative Statuswerte bei Projektabbruch oder unvorhersehbaren Problemen (z. B. Rohrblockade im Tiefbau).

### 4.2. Logik der Relevanz-Steuerung (On/Off-Schalter)

* **Auf Checklisten-Ebene:** Komplette Kontrollblöcke (z. B. "Q-Check Tiefbau") können per Hauptschalter deaktiviert werden, wenn das Gewerk am Standort entfällt.
* **Auf Prüfpunkt-Ebene:** Einzelne Kriterien (z. B. "Einstiegsschacht prüfen") können ausgeschaltet werden, wenn sie vor Ort nicht existieren.
* **Berechnung Erfüllungsgrad (0–100%):** Deaktivierte Elemente (Status N/A) fließen **nicht** negativ in die Fortschrittsberechnung ein. Sie verringern die Gesamtzahl der geforderten Pflichtpunkte, sodass die 100% weiterhin erreicht werden können.
* **Keine Gesamtabnahme:** Bauleiter nehmen Checklisten **immer einzeln** ab. Es gibt keine pauschale Freigabe des gesamten Auftrags. Eine Abnahme ist zudem erst möglich, wenn die jeweilige Checkliste den Status `completed` aufweist.

---

## 5. Zukünftige Erweiterung: KI-Funktionen (Ausblick)

*In einer späteren Projektphase wird ein asynchroner Python-KI-Dienst an das Laravel-Backend angebunden. Diese Funktionen sind für die aktuelle Umsetzung explizit ausgeklammert und dienen nur als strategischer Ausblick:*

* **Automatische Bildvalidierung:** Prüfung unmittelbar nach dem Upload des Fotos im Feld (z. B. über WebSockets).
* **Biegeradius-Erkennung:** Visuelle Analyse von Glasfaser- und Kupferkabeln auf Einhaltung der Mindestbiegeradien (z. B. 14 cm bei 72er LWL Minikabeln).
* **OCR-Abgleich:** Automatisches Auslesen von Kabelschildern (Asset-ID), OTO-Labels (Flat-ID) und Nummern im BEP zur Validierung gegen die Soll-Daten der API.
* **Montagekontrolle:** KI-gestützte Verifikation, ob Fasern korrekt in der Kassette abgelegt und Zugentlastungen richtig montiert sind.

---

## 6. To-Do-Liste / Implementierungs-Roadmap für Jules

Diese Liste enthält ausschließlich die Kernfunktionen für die anstehende Umsetzung (Phase 1). **KI-Features sind hier nicht enthalten.**

### Sprint 1: Fundament & API-Datenstruktur

* [ ] PostgreSQL-Datenbankstruktur gemäß Spezifikation aufsetzen (Tabellen für `jobs`, `job_assets`, `checklists`, `checklist_items`, `attachments`, `audit_logs`).
* [ ] REST-API-Endpunkte in Laravel implementieren für die automatische Auftragserstellung (Import von PID, Adresse, Projekt-Typ, Bauleiter, Technologie, Asset-IDs, Flat-IDs, Kabel-/BEP-/Muffentypen).
* [ ] Manuelle Import-Schnittstelle für Admins im Backend bereitstellen (Excel/CSV-Mapping und manuelles Formular).

### Sprint 2: Admin- & Bauleiter-Dashboard (Filament PHP)

* [ ] Erstellung des Backoffice-Dashboards mit Filament PHP.
* [ ] Globale Suche implementieren (Suche nach PID und Adresse).
* [ ] Schnellfilter „Assigned to me“ für zugewiesene Bauleiter umsetzen.
* [ ] Listenansichten für Bauleiter erstellen (Fokus auf Checklisten im Status `completed`).
* [ ] Massenabnahme (Bulk Freigabe) für Bauleiter in den Tabellenansichten implementieren (Aktion: Markierte Checklisten auf `approved` setzen).
* [ ] Zentrales Kommentarfeld pro Checkliste/Kartei für Bauleiter einrichten (inkl. Pflichtfeld-Zwang bei Statuswechsel auf `rejected`).

### Sprint 3: PWA Client & Offline-First Logik

* [ ] Basis-PWA aufsetzen (Service Worker für App-Caching registrieren).
* [ ] Lokale Datenbank-Infrastruktur im Browser aufbauen (`IndexedDB`).
* [ ] Synchronisations-Mechanismus entwickeln: Erkennung des Online-Status (`navigator.onLine`) und sequenzieller Abgleich von Textdaten und Statusänderungen.
* [ ] Datei-Upload-Warteschlange (Photo Queue) im Client implementieren: Lokale Zwischenspeicherung aufgenommener Fotos im Browser-Dateisystem und automatischer Upload in den S3/MinIO-Speicher bei stabiler Verbindung.

### Sprint 4: Feld-Workflow & UI-Komponenten

* [ ] UI für die Auftragskartei und die darunterliegenden Checklisten-Blöcke (QS1 / QS2) bauen.
* [ ] On/Off-Schalter für komplette Checklisten auf der Kartei implementieren.
* [ ] On/Off-Schalter für einzelne Prüfpunkte innerhalb einer Checkliste umsetzen (inkl. Anpassung der 0–100% Fortschritts-Berechnungslogik).
* [ ] Foto-Erfassungskomponente mit Multi-Upload und optionalem Textfeld pro Prüfpunkt erstellen.
* [ ] Status-Umschaltung für den Monteur umsetzen (`in work` Zwischenspeicherung und `completed` Abschluss).
* [ ] Protokollierung aller Aktionen in der Tabelle `audit_logs` (Wer hat wann welchen Punkt deaktiviert oder Status geändert).
