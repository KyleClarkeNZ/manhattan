#!/bin/bash

# Manhattan Build Script
# This script packages the demo for deployment to manhattan.kyleclarke.co.nz

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================================"
echo "  Manhattan UI - Build Script"
echo "========================================================${NC}"
echo ""

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

ERRORS=0
BUILD_DIR="dist"
ZIP_NAME="manhattan-demo.zip"

# Step 1: Run PHP 7.4 compatibility check
echo -e "${BLUE}Step 1: Running PHP 7.4 compatibility check...${NC}"
if [ -f "scripts/check_php74_compatibility.php" ]; then
    if php scripts/check_php74_compatibility.php; then
        echo -e "${GREEN}✓ PHP 7.4 compatibility check passed${NC}"
    else
        echo -e "${RED}✗ PHP 7.4 compatibility check failed${NC}"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo -e "${YELLOW}⚠ Compatibility checker not found, skipping${NC}"
fi
echo ""

# Check if there were any errors before continuing
if [ $ERRORS -gt 0 ]; then
    echo -e "${RED}Build failed with $ERRORS error(s). Fix issues before packaging.${NC}"
    exit 1
fi

# Step 2: Install Composer dependencies (no dev, optimised autoloader)
echo -e "${BLUE}Step 2: Installing Composer dependencies (no-dev)...${NC}"
if command -v composer &>/dev/null; then
    COMPOSER_CMD="composer"
elif [ -f "/home/kyle/composer" ]; then
    COMPOSER_CMD="/home/kyle/composer"
elif [ -f "$SCRIPT_DIR/composer.phar" ]; then
    COMPOSER_CMD="php $SCRIPT_DIR/composer.phar"
else
    echo -e "${RED}✗ Composer not found. Install it from https://getcomposer.org${NC}"
    exit 1
fi

if $COMPOSER_CMD install --no-dev --optimize-autoloader --quiet; then
    echo -e "${GREEN}✓ Composer dependencies installed (vendor/ ready)${NC}"
else
    echo -e "${RED}✗ Composer install failed${NC}"
    exit 1
fi
echo ""

# Step 3: Create build directory
echo -e "${BLUE}Step 3: Preparing build directory...${NC}"
if [ -d "$BUILD_DIR" ]; then
    rm -rf "$BUILD_DIR"
fi
mkdir -p "$BUILD_DIR"
echo -e "${GREEN}✓ Build directory created${NC}"
echo ""

# Step 4: Package demo files
echo -e "${BLUE}Step 4: Packaging demo files...${NC}"

# Create demo package with production-required files
# NOTE: src/ is required because Composer autoloader maps Manhattan\ namespace to src/
zip -r "$BUILD_DIR/$ZIP_NAME" \
    index.php \
    demo/ \
    src/ \
    assets/ \
    vendor/ \
    .htaccess \
    -x "*.git*" \
    -x "*/.DS_Store" \
    -x "*/Thumbs.db" \
    -x "*.log" \
    -x "*.bak" \
    -x "*.backup" \
    -x "*/composer.json" \
    -x "*/composer.lock" \
    -x "*/tests/*" \
    -x "*/test/*" \
    -x "*/Tests/*" \
    -x "*/Test/*" \
    -x "*/.git/*" \
    -x "*/.github/*" \
    -x "*/scss/*" \
    -x "*/less/*" \
    -x "*/metadata/*" \
    2>&1 | grep -v "adding:" || true

if [ -f "$BUILD_DIR/$ZIP_NAME" ]; then
    SIZE=$(du -h "$BUILD_DIR/$ZIP_NAME" | cut -f1)
    echo -e "${GREEN}✓ Demo package created: $BUILD_DIR/$ZIP_NAME ($SIZE)${NC}"
else
    echo -e "${RED}✗ Failed to create package${NC}"
    exit 1
fi
echo ""

# Step 5: Checking production .htaccess...
echo -e "${BLUE}Step 5: Checking production .htaccess...${NC}"
if [ ! -f ".htaccess" ]; then
    cat > .htaccess << 'HTACCESS'
# Manhattan Demo .htaccess
DirectoryIndex demo/index.php

<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect root to demo page
    RewriteRule ^$ demo/index.php [L]
    
    # Serve assets directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Handle demo endpoints
    RewriteRule ^toggleTheme$ demo/index.php [L,QSA]
    RewriteRule ^nzpostSuggest$ demo/index.php [L,QSA]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory browsing
Options -Indexes

# Error pages (optional)
ErrorDocument 404 /demo/index.php
HTACCESS
    echo -e "${GREEN}✓ Created .htaccess for production${NC}"
else
    echo -e "${GREEN}✓ .htaccess already exists${NC}"
fi
echo ""

# Build summary
echo -e "${BLUE}========================================================"
echo "  Build Complete!"
echo "========================================================${NC}"
echo ""
echo -e "📦 Package:     ${GREEN}$BUILD_DIR/$ZIP_NAME${NC}"
echo -e "📏 Size:        ${GREEN}$SIZE${NC}"
echo ""
echo -e "${YELLOW}Deployment Instructions:${NC}"
echo "1. Upload to manhattan.kyleclarke.co.nz"
echo "2. Extract: unzip $ZIP_NAME"
echo "3. Set permissions: chmod -R 755 ."
echo "4. Verify .htaccess is in place"
echo "5. Test: https://manhattan.kyleclarke.co.nz"
echo ""
echo -e "${GREEN}✓ Ready for deployment!${NC}"
echo ""
