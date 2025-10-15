# BeatWager Troubleshooting Guide

## Redis Connection Errors

### Issue: `RedisException: Connection refused`

**Cause:** The `.env` file has `REDIS_PORT=6380` which is the **external** host port mapping. Inside the Docker network, Redis listens on the default port `6379`.

**Solution:**
```bash
# Update .env to use internal Docker port
REDIS_PORT=6379  # Not 6380!

# Restart containers
docker compose restart app queue scheduler
```

**Explanation:**
- **External access** (from host): `localhost:6380` → Redis container port `6379`
- **Internal access** (container-to-container): `redis:6379` → Redis container port `6379`

The docker-compose.yml maps:
```yaml
ports:
  - "6380:6379"  # host:container
```

So Laravel inside the `app` container should use `REDIS_PORT=6379`.

---

## Cache Issues

### Clear All Laravel Caches
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
```

---

## Container Issues

### Container Won't Start
```bash
# Check logs
docker compose logs app --tail=50
docker compose logs queue --tail=50

# Check container status
docker compose ps

# Rebuild and restart
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Permission Issues
```bash
# Fix Laravel storage permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

---

## Database Issues

### Connection Refused
```bash
# Check if Postgres is healthy
docker compose ps postgres

# Test connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Reset Database
```bash
docker compose exec app php artisan migrate:fresh
```

---

## Queue Not Processing

### Check Queue Worker
```bash
# View queue logs
docker compose logs queue -f

# Restart queue worker
docker compose restart queue

# Manually process jobs
docker compose exec app php artisan queue:work redis --once
```

---

## Port Conflicts

### Port Already in Use
```bash
# Find what's using the port
docker ps --filter publish=8000

# Change port in .env
APP_PORT=8001

# Update docker-compose.yml
ports:
  - "${APP_PORT:-8001}:8000"

# Restart
docker compose down && docker compose up -d
```

---

## Performance Issues

### Slow Container Startup
```bash
# Check Docker resource allocation
docker stats

# Increase Docker memory/CPU in Docker Desktop settings
```

### Optimize Composer
```bash
# Use optimized autoloader
docker compose exec app composer dump-autoload --optimize

# Clear Composer cache
docker compose exec app composer clear-cache
```

---

## Development Tools

### Access Container Shell
```bash
docker compose exec app sh
```

### Run Artisan Commands
```bash
docker compose exec app php artisan <command>
```

### View Real-time Logs
```bash
# All containers
docker compose logs -f

# Specific container
docker compose logs -f app
docker compose logs -f queue
```

### Test Redis Connection
```bash
# From Laravel
docker compose exec app php artisan tinker
>>> use Illuminate\Support\Facades\Cache;
>>> Cache::put('test', 'working');
>>> Cache::get('test');

# From Redis CLI
docker compose exec redis redis-cli
127.0.0.1:6379> PING
PONG
```

---

## Clean Slate Restart

If everything is broken, start fresh:

```bash
# Stop and remove everything
docker compose down -v

# Remove all images
docker compose rm -f

# Rebuild from scratch
docker compose build --no-cache

# Start fresh
docker compose up -d

# Check status
docker compose ps
```

---

## Common Laravel Issues

### APP_KEY Not Set
```bash
docker compose exec app php artisan key:generate
```

### Routes Not Found
```bash
docker compose exec app php artisan route:cache
```

### Config Cache Issues
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
```

---

## Health Check Failures

### Container Shows "unhealthy"
```bash
# Check what the health check is testing
docker compose exec app curl -f http://localhost:8000/api/health

# View health check config in docker-compose.yml
# Adjust timeout/retries if needed
```

---

## Still Having Issues?

1. **Check logs first**: `docker compose logs -f`
2. **Verify environment**: `docker compose exec app php artisan about`
3. **Test connectivity**: `docker compose exec app ping redis`
4. **Clean slate**: `docker compose down -v && docker compose up -d`

If none of these work, check:
- Docker Desktop is running
- Sufficient disk space
- No firewall blocking Docker
- WSL2 integration enabled (on Windows)
