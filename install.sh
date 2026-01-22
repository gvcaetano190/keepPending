#!/bin/bash
# ============================================================================
# KeepPending Plugin - Installation Script for GLPI
# ============================================================================
#
# This script automatically downloads and installs the KeepPending plugin
# Usage: sudo bash install.sh
# or:    bash install.sh (if you have write permissions)
#
# ============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}KeepPending Plugin Installer${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Check if GLPI path is provided or use default
GLPI_PATH="${1:-/var/www/html/glpi}"
PLUGINS_DIR="$GLPI_PATH/plugins"

echo -e "${YELLOW}GLPI Path:${NC} $GLPI_PATH"
echo -e "${YELLOW}Plugins Directory:${NC} $PLUGINS_DIR\n"

# Verify GLPI exists
if [ ! -d "$GLPI_PATH" ]; then
    echo -e "${RED}✗ Error: GLPI directory not found at $GLPI_PATH${NC}"
    echo -e "${YELLOW}Usage: sudo bash install.sh /path/to/glpi${NC}"
    exit 1
fi

if [ ! -d "$PLUGINS_DIR" ]; then
    echo -e "${RED}✗ Error: Plugins directory not found at $PLUGINS_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}✓ GLPI directory found${NC}\n"

# Remove old installation if exists
if [ -d "$PLUGINS_DIR/keeppending" ]; then
    echo -e "${YELLOW}⚠ Removing old installation...${NC}"
    rm -rf "$PLUGINS_DIR/keeppending"
    echo -e "${GREEN}✓ Old installation removed${NC}\n"
fi

# Download plugin
echo -e "${YELLOW}⬇ Downloading KeepPending plugin...${NC}"
cd "$PLUGINS_DIR"

if command -v wget &> /dev/null; then
    wget -q https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz
elif command -v curl &> /dev/null; then
    curl -L -s https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -o keeppending.tar.gz
else
    echo -e "${RED}✗ Error: wget or curl not found${NC}"
    exit 1
fi

if [ ! -f "keeppending.tar.gz" ]; then
    echo -e "${RED}✗ Error: Failed to download plugin${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Plugin downloaded${NC}\n"

# Extract plugin
echo -e "${YELLOW}📦 Extracting plugin...${NC}"
tar -xzf keeppending.tar.gz

if [ -d "keepPending-main" ]; then
    mv keepPending-main keeppending
elif [ -d "keeppending-main" ]; then
    mv keeppending-main keeppending
fi

if [ ! -d "keeppending" ]; then
    echo -e "${RED}✗ Error: Failed to extract plugin${NC}"
    rm -f keeppending.tar.gz
    exit 1
fi

echo -e "${GREEN}✓ Plugin extracted${NC}\n"

# Set permissions
echo -e "${YELLOW}🔐 Setting permissions...${NC}"
chown -R www-data:www-data "$PLUGINS_DIR/keeppending"
chmod -R 755 "$PLUGINS_DIR/keeppending"
echo -e "${GREEN}✓ Permissions set${NC}\n"

# Clean up
echo -e "${YELLOW}🧹 Cleaning up...${NC}"
rm -f keeppending.tar.gz
echo -e "${GREEN}✓ Cleanup complete${NC}\n"

# Verify installation
echo -e "${YELLOW}✓ Verifying installation...${NC}"
if [ -f "$PLUGINS_DIR/keeppending/setup.php" ] && [ -f "$PLUGINS_DIR/keeppending/init.php" ]; then
    echo -e "${GREEN}✓ Plugin files verified${NC}\n"
else
    echo -e "${RED}✗ Error: Plugin files missing${NC}"
    exit 1
fi

# Final message
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✓ Installation Successful!${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "${YELLOW}Next Steps:${NC}"
echo -e "1. Open your browser and go to:"
echo -e "   ${BLUE}http://your-glpi-domain/front/plugin.php${NC}\n"
echo -e "2. Look for ${BLUE}KeepPending${NC} in the plugins list\n"
echo -e "3. Click ${BLUE}Install${NC}\n"
echo -e "4. Click ${BLUE}Activate${NC}\n"
echo -e "5. Done! The plugin is ready to use.\n"

echo -e "${YELLOW}Documentation:${NC}"
echo -e "- README: https://github.com/gvcaetano190/keepPending"
echo -e "- Issues: https://github.com/gvcaetano190/keepPending/issues\n"

exit 0
