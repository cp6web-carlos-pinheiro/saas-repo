# INICIAR O CONTAINER DO MYSQL
docker run --name mysql-mrp -p 3306:3306 -e MYSQL_ROOT_PASSWORD=i14lij69i14lij69 -d mysql:latest

# CRIAR O BANCO DE DADOS E CREDENCIAS DE ACESSO
create database beyond_mrp;
CREATE USER 'beyond_mrp'@'%' IDENTIFIED BY 'i14lij69i14lij69';
GRANT ALL PRIVILEGES ON beyond_mrp.* TO 'beyond_mrp'@'%';
FLUSH PRIVILEGES;

# CRIAR O ARQUIVO .env E ATUALIZAR AS CREDENCIAIS DE ACESSO AO BANCO DE DADOS MYSQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beyond_mrp
DB_USERNAME=beyond_mrp
DB_PASSWORD=i14lij69i14lij69

# INICIAR O CONTAINER DO REDIS
docker run --name meu-redis -p 6379:6379 -d redis:latest redis-server --requirepass "i14lij69i14lij69"

# CRIAR O ARQUIVO .env E ATUALIZAR AS CREDENCIAIS DE ACESSO AO REDIS
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=i14lij69i14lij69
REDIS_PORT=6379

# COMPOSER INSTALL
cd www
composer install

# NPM INSTALL
cd www
npm install

# CRIAR A CHAVE NO LARAVEL
php artisan key:generate

# RODAR MIGRATIONS
php artisan migrate

# INSERIR DADOS INICIAIS
php artisan db:seed

# CREDENCIAIS DO ADMIN
URL: http://127.0.0.1:8000/global-admin/login
EMAIL: admin@beyondgroup.com.br 
SENHA: i14lij69M!@#