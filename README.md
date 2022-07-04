# Rezepte-Server
Ein Webserver um Rezepte zu Speichern und Mengen ggf. umzurechenen. 

Was kann die Website?
- Rezepte werden im *Handyformat* angezeigt und können dort umgerechnet werden
- Pezepte ausdrucken
- Einfache Suche
- Advanced Suche
  - Dort kann man nach Zutaten, Zubereitungszeit, Speiseart, etc filtern.

# Backend
Aus "Ich lerne PHP für die Schule" wird over engineered garbage.

Style wird mit Bootstrap gemacht und das frontend mit VUE.

Rezepte werdein in JSON files gespeichert anstatt einer Datenbank. Da der Server für einen Raspberry Pi konzepiert ist, müssen die Rezepte leicht zum exportieren/importieren sein (Tote SD Katre oder Raspberry passiert schnell ⚰️). Zusätzlich muss man sich nicht um Collations kümmern.

# Installation

**Was wird benötigt?**
- Apache Sever mit PHP mod
  - Richtige chmod berechtingungen!
- vue-cli

Es ist keine weitere HTTP portfreigabe notwendig. Microsegmentierung ist optional.

Richtiges verzeichnis:

`cd /var/www`

Klonen:

`git clone https://github.com/MrMoonshine/Rezepte-Server.git`

Apache Ko0nfigurieren:
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