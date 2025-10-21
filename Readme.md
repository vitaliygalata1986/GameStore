# Запустить контейнеры в режиме, когда они работают в фоне. При первом запуске скачает images и создаст контейнеры.
docker-compose up -d

# http://localhost:8380/  phpmyadmin


# Остановить все запущенные контейнеры
docker-compose stop

# mu-plugins 
Подгружаются по-умолчанию, их активировать ненужно


База данных MySQL сохраняется в ./.srv/database → она примонтирована к /var/lib/mysql у сервиса mysql.

./.srv/wordpress — это файлы сайта WordPress (ядро, темы, плагины, wp-content/uploads и т.п.), примонтированы к /var/www/html у сервиса wordpress.

То есть при пересоздании контейнеров:
    данные БД останутся в ./.srv/database;
    файлы сайта останутся в ./.srv/wordpress.