# Do ensure to allow ufw status in VPS
```bash
ufw app list
ufw allow OpenSSH
ufw allow 80
ufw allow 443
ufw enable
```
***default.conf*** (It required to commend the related to certbot before using)
```bash
    listen 443 ssl http2;
    server_name pbroles.com;

    # SSL certificates from Certbot
    ssl_certificate /etc/letsencrypt/live/pbroles.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pbroles.com/privkey.pem;
```



# Remove & Install a new certbot
```bash
docker compose run --rm \
  -v $(pwd)/certbot/conf:/etc/letsencrypt \
  -v $(pwd)/certbot/www:/var/www/certbot \
  --entrypoint "" \
  certbot \
  certbot certonly \
  --webroot -w /var/www/certbot \
  -d pbroles.com \
  --agree-tos \
  --email pbrauser.help@gmail.com \
  --no-eff-email \
  --non-interactive

```

```bash
docker compose restart nginx
docker exec -it pbra_nginx ls /etc/letsencrypt/live/pbroles.com/
docker logs pbra_nginx

```

# Extra Information
```bash
docker exec pbra_nginx nginx -t
docker exec pbra_nginx nginx -s reload
docker stop pbra_nginx pbra_phpmyadmin pbra_db pbra_php
docker start pbra_nginx pbra_phpmyadmin pbra_db pbra_php
docker compose down -v
```