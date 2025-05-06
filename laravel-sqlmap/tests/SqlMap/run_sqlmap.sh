#!/bin/bash

# sqlmap
SQLMAP_DIR="/Users/abenevaut/Sites/sqlmap"

# Répertoire des templates
TEMPLATE_DIR="."

# Répertoire de sortie pour les logs
OUTPUT_DIR="$TEMPLATE_DIR/../../storage/logs"

# Créer le répertoire de sortie s'il n'existe pas
mkdir -p "$OUTPUT_DIR"

# Tableau des templates à exécuter
templates=("verify-email.template" "login.template")

# Options communes pour SQLMap
COMMON_OPTIONS="--batch --level=5 --risk=3 --flush-session --fresh-queries --threads=8 --dbms=SQLite"

# Boucle sur chaque template et exécute la commande SQLMap
for template in "${templates[@]}"; do
    echo "Exécution de SQLMap pour le template: $template"
    python3 "$SQLMAP_DIR/sqlmap.py" -v -r "$TEMPLATE_DIR/$template" $COMMON_OPTIONS --output-dir="$OUTPUT_DIR"
done
