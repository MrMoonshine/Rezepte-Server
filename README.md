# Rezepte-Server
Eine Webseite um Rezepte zu Speichern und Mengen ggf. umzurechenen. 

Was kann die Website?
- Rezepte werden im *Handyformat* angezeigt und können dort umgerechnet werden
- Pezepte ausdrucken
- Einfache Suche
- Advanced Suche
  - Dort kann man nach Zutaten, Zubereitungszeit, Speiseart, etc filtern.
- Masse kann in Volumenmaß (Liter) umgerechnet werden.
- Zufälliges Rezept für einen Filter
- Benutzerdefinierte Einheiten, Alergene, Zutat-Dichten können im Config UI konfiguriert werden.

# Backend
Aus "Ich lerne PHP für die Schule" wird over engineered garbage.

Style wird mit Bootstrap gemacht und das frontend mit VUE.

Rezepte werden in einer SQLite3 Datenbank gespeichert. Backups aufnehmen und einspielen geht über das Config UI im browser.

# Installation

**Was wird benötigt?**
- Apache Sever mit PHP mod
  - Richtige chmod berechtingungen!
  - `httpd_sys_rw_content` falls SELinux aktiv ist!
- vue-cli

Es ist keine weitere HTTP portfreigabe notwendig. Microsegmentierung ist optional.

Richtiges verzeichnis:

`cd /var/www`

Klonen:

`git clone https://github.com/MrMoonshine/Rezepte-Server.git`

Apache Konfigurieren:
```bash
cd Rezepte-Server
# Für Debian
sudo cp rezepte.conf /etc/apache/sites-available
# Seite benutzen
sudo a2ensite rezepte.conf
# Neustart
sudo systemctl restart apache2
```
Zum Schluss: VUE Bauen
```bash
cd /var/www/Rezepte-Server/rezepte
vue build
```
Die seite ist nun hier erreichbar:

`http://<host>/rezepte/`

HTTPs geht auch wenn es am Apache kanfiguriert ist.