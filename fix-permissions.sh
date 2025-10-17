#!/bin/bash

echo "🔧 Fixing Docker permissions issue..."
echo ""

# Fix current file permissions
echo "1️⃣ Fixing file ownership..."
sudo chown -R ploi:ploi /home/xander/webapps/beatwager-app
echo "   ✅ Files now owned by xander:xander"
echo ""

# Rebuild containers with new user configuration
echo "2️⃣ Rebuilding Docker containers with user $USER_ID:$GROUP_ID..."
docker compose down
docker compose build --no-cache
docker compose up -d
echo "   ✅ Containers rebuilt"
echo ""

echo "3️⃣ Running composer install..."
docker compose exec -T app composer install
echo "   ✅ Composer dependencies installed"
echo ""

echo "✅ All done! Your containers now run as your host user (1001:1001)"
echo "   New files created will have correct permissions automatically."
