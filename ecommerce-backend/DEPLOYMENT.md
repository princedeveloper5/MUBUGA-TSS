# Deployment Guide - Rwanda E-commerce System

## Production Deployment

### Prerequisites
- Node.js 18+ 
- MongoDB 5.0+
- Redis (optional, for caching)
- Nginx (recommended for reverse proxy)
- SSL certificate
- Domain name

### Environment Setup

1. **Server Requirements**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install MongoDB
wget -qO - https://www.mongodb.org/static/pgp/server-5.0.asc
sudo apt-get install gnupg
wget -qO - https://www.mongodb.org/static/pgp/server-5.0.asc
sudo apt-get install -y mongodb-org

# Install Redis (optional)
sudo apt-get install -y redis-server

# Install Nginx
sudo apt-get install -y nginx

# Install PM2
sudo npm install -g pm2
```

2. **Database Configuration**
```bash
# Enable and start MongoDB
sudo systemctl enable mongod
sudo systemctl start mongod

# Configure MongoDB for production
sudo nano /etc/mongod.conf
# Add security and performance settings
```

3. **Application Setup**
```bash
# Clone repository
git clone <your-repo-url>
cd rwanda-ecommerce

# Install dependencies
cd ecommerce-backend
npm ci --production

# Configure environment
cp .env.example .env
nano .env
# Set production values
```

### Production Environment Variables

```env
NODE_ENV=production
PORT=5000
MONGODB_URI=mongodb://localhost:27017/rwanda_ecommerce_prod
JWT_SECRET=<strong-random-64-character-string>
PAYPACK_CLIENT_ID=<your-paypack-client-id>
PAYPACK_CLIENT_SECRET=<your-paypack-client-secret>
UNSPLASH_ACCESS_KEY=<your-unsplash-access-key>

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM=RwandaShop <noreply@rwandashop.rw>

# SMS Configuration
SMS_API_KEY=<your-sms-api-key>
SMS_FROM=RwandaShop

# Security
ENCRYPTION_KEY=<strong-32-character-key>
TRUSTED_IPS=127.0.0.1,::1

# Frontend URL
FRONTEND_URL=https://yourdomain.com
```

### Nginx Configuration

```nginx
# /etc/nginx/sites-available/rwandashop
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Backend API
    location /api/ {
        proxy_pass http://localhost:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Frontend Static Files
    location / {
        root /path/to/ecommerce-frontend/dist;
        try_files $uri $uri/ /index.html;
        
        # Cache static files
        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }

    # PWA Service Worker
    location = /sw.js {
        root /path/to/ecommerce-frontend/dist;
        expires 1d;
        add_header Cache-Control "public, no-cache";
    }

    # Security
    location ~ /\. {
        deny all;
    }
}
```

### PM2 Configuration

```json
{
  "apps": [
    {
      "name": "rwanda-ecommerce-backend",
      "script": "./server.js",
      "cwd": "/path/to/ecommerce-backend",
      "instances": "max",
      "exec_mode": "cluster",
      "env": {
        "NODE_ENV": "production",
        "PORT": 5000
      },
      "error_file": "./logs/err.log",
      "out_file": "./logs/out.log",
      "log_file": "./logs/combined.log",
      "time": true,
      "max_memory_restart": "1G",
      "node_args": "--max-old-space-size=1024"
    }
  ]
}
```

### Deployment Steps

1. **Build Frontend**
```bash
cd ecommerce-frontend
npm run build
```

2. **Setup SSL Certificate**
```bash
# Using Let's Encrypt
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

3. **Deploy Application**
```bash
# Create necessary directories
sudo mkdir -p /var/www/rwandashop/{uploads,backups,logs}
sudo chown -R $USER:$USER /var/www/rwandashop

# Copy files
sudo cp -r ecommerce-frontend/dist/* /var/www/rwandashop/
sudo cp -r ecommerce-backend /var/www/rwandashop/backend/

# Install backend dependencies
cd /var/www/rwandashop/backend
npm ci --production
```

4. **Start Services**
```bash
# Start Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Start MongoDB
sudo systemctl enable mongod
sudo systemctl start mongod

# Start Nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Start application with PM2
cd /var/www/rwandashop/backend
pm2 start ecosystem.config.js
```

5. **Setup Monitoring**
```bash
# Install monitoring tools
npm install -g pm2-logrotate
pm2 install pm2-server-monit

# Setup log rotation
pm2 install pm2-logrotate
```

### Monitoring and Logging

1. **Application Monitoring**
```bash
# PM2 Monitoring
pm2 monit

# View logs
pm2 logs rwanda-ecommerce-backend
pm2 logs --lines 100 --err
```

2. **Database Monitoring**
```bash
# MongoDB status
sudo systemctl status mongod

# MongoDB logs
sudo tail -f /var/log/mongodb/mongod.log
```

3. **System Monitoring**
```bash
# Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# System resources
htop
iostat -x 1
df -h
```

### Security Hardening

1. **Firewall Configuration**
```bash
# Configure UFW
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 27017/tcp  # MongoDB (if external access needed)
```

2. **Database Security**
```bash
# Enable MongoDB authentication
mongo
use admin
db.createUser({
  user: "admin",
  pwd: "strong-password",
  roles: ["userAdminAnyDatabase"]
})
```

3. **Application Security**
```bash
# Set proper file permissions
sudo chmod -R 755 /var/www/rwandashop
sudo chown -R www-data:www-data /var/www/rwandashop/uploads
```

### Backup Strategy

1. **Automated Backups**
```bash
# Create backup script
cat > /home/user/backup-script.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y-%m-%d)
BACKUP_DIR="/var/backups/rwandashop"

# Database backup
mongodump --uri="mongodb://admin:password@localhost:27017/rwanda_ecommerce_prod" --gzip --archive="$BACKUP_DIR/db-$DATE.gz"

# Files backup
tar -czf "$BACKUP_DIR/files-$DATE.tar.gz" /var/www/rwandashop/uploads

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

# Upload to cloud storage (optional)
# aws s3 cp $BACKUP_DIR/db-$DATE.gz s3://your-backup-bucket/
EOF

chmod +x /home/user/backup-script.sh

# Add to crontab
crontab -e
# Add: 0 2 * * * /home/user/backup-script.sh
```

2. **Testing Backups**
```bash
# Test restore process
mongorestore --uri="mongodb://admin:password@localhost:27017/rwanda_ecommerce_test" --gzip --archive="/var/backups/rwandashop/db-latest.gz"
```

### Performance Optimization

1. **Database Optimization**
```javascript
// MongoDB indexes
db.products.createIndex({ name: "text", description: "text" });
db.products.createIndex({ category: 1, price: 1 });
db.orders.createIndex({ createdAt: -1, status: 1 });
```

2. **Caching Strategy**
```bash
# Redis configuration
sudo nano /etc/redis/redis.conf
# Set maxmemory and eviction policy
maxmemory 256mb
maxmemory-policy allkeys-lru
```

3. **CDN Setup**
```nginx
# Add CDN for static assets
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header X-CDN "CloudFlare";
}
```

### Scaling Considerations

1. **Horizontal Scaling**
- Use load balancer for multiple app instances
- Database replication for high availability
- Redis cluster for distributed caching

2. **Vertical Scaling**
- Increase server resources (CPU, RAM, Storage)
- Optimize database queries
- Implement connection pooling

### Health Checks

```bash
# Create health check endpoint
curl -f https://yourdomain.com/api/health || echo "Application down"

# Database connectivity
mongo --eval "db.adminCommand('ismaster')" || echo "Database down"

# Service status
sudo systemctl status nginx mongod redis-server
```

### Troubleshooting

1. **Common Issues**
- **Port conflicts**: Check if ports 80, 443, 5000 are available
- **Permission denied**: Check file permissions and ownership
- **Database connection**: Verify MongoDB is running and accessible
- **SSL errors**: Check certificate paths and expiration

2. **Log Analysis**
```bash
# Application errors
grep "ERROR" /var/www/rwandashop/backend/logs/err.log

# Nginx errors
grep "error" /var/log/nginx/error.log

# Database errors
grep "ERROR" /var/log/mongodb/mongod.log
```

### Maintenance

1. **Regular Tasks**
- Update dependencies monthly
- Monitor disk space usage
- Review and rotate logs
- Test backup restoration
- Update SSL certificates before expiration

2. **Performance Monitoring**
- Set up alerts for high CPU/memory usage
- Monitor response times
- Track error rates
- Database performance metrics

This deployment guide ensures your Rwanda e-commerce platform is production-ready with proper security, monitoring, and scaling capabilities.
