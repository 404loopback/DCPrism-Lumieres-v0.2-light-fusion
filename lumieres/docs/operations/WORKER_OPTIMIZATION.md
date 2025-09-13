# Worker Optimization for Distributed Architecture

## Current Architecture
DCPrism uses **distributed processing** where:
- **Fresnel & Meniscus workers** = Lightweight coordination tasks
- **DCP-o-matic mirrors** = Heavy DCP processing (deployed separately)

## Resource Optimization

### Current Worker Tasks
```bash
# Fresnel Workers
- Festival notifications
- Email campaigns  
- PDF report generation
- Metadata synchronization
- QR code generation

# Meniscus Workers
- Mirror deployment (Terraform/OpenTofu)
- Health monitoring
- Load balancing
- Cleanup automation
```

### Recommended Resource Limits
Add these to docker-compose.yml for production:

```yaml
fresnel-worker:
  deploy:
    resources:
      limits:
        cpus: '0.25'
        memory: 256M
      reservations:
        cpus: '0.1'
        memory: 128M

meniscus-worker:
  deploy:
    resources:
      limits:
        cpus: '0.5'
        memory: 512M
      reservations:
        cpus: '0.2'
        memory: 256M
```

### Alternative: Single Container Pattern
If workloads remain very light, you could integrate workers into main containers:

```yaml
# In supervisord.conf
[program:queue-worker]
command=php artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
```

## Monitoring

### Key Metrics to Track
```bash
# Worker queue length
docker compose exec fresnel-app php artisan queue:monitor

# Resource usage
docker stats dcprism-fresnel-worker dcprism-meniscus-worker

# Failed jobs
docker compose exec fresnel-app php artisan queue:failed
```

### Performance Indicators
- Queue length consistently < 10
- Memory usage < 50% of allocation
- CPU usage spikes only during job execution
- Zero failed jobs over 24h period

## Scaling Strategy

### Horizontal Scaling
```yaml
# Scale workers independently
docker compose up --scale fresnel-worker=2 --scale meniscus-worker=3
```

### Vertical Scaling  
Increase resources only when:
- Consistent queue backlog > 50 jobs
- Memory usage > 80% sustained
- Job timeouts increasing

## Notes
- DCP processing happens on dedicated DCP-o-matic mirrors
- DCPrism workers handle coordination, not heavy computation
- Monitor queue metrics to optimize worker count vs resource allocation
