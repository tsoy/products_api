## Installation

1. Prerequsites
composer

1. Clone project
git clone https://github.com/tsoy/products_api.git products.dev

1. Update env file
Copy .env.example file to .env and update following params to match your system
DB_DATABASE=products
DB_USERNAME=root
DB_PASSWORD=root

1. Add virtual host/server block for new site
Make sure to specify "public" directory as root

Apache example:
```
<VirtualHost products.dev:80>
    DocumentRoot "C:/xampp/htdocs/products.dev/public"
    ServerName subscribe.dev
    <Directory "C:/xampp/htdocs/products.dev/public">
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```
Nginx example:
```
server {
    listen       80;
    server_name  products.dev;
    root       /var/www/products.dev/public/;

    access_log  /usr/local/etc/nginx/logs/laravel.dev.access.log  main;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
     location ~ \.php$ {
        include   /usr/local/etc/nginx/conf.d/php-fpm;
    }
}
```
5. Update your hosts file to make products.dev url point to your local machine
 