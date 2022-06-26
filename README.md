# Rezepte-Server
Ein Webserver um Rezepte zu Speichern und Mengen ggf. umzurechenen. 

# Backend
Aus "Ich lerne PHP für die Schule" wird over engineered garbage.

Style wird mit Bootstrap gemacht und das frontend mit VUE.

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

Für HTTPs müssen im code die XHR requests ausgebessert werden.