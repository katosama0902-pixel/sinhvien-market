FROM php:8.1-apache

# Bật mod_rewrite cho Apache (quan trọng để chạy .htaccess)
RUN a2enmod rewrite

# Cài đặt các extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql zip

# Copy toàn bộ code vào thư mục gốc của Apache
COPY . /var/www/html/

# Phân quyền cho Apache đọc/ghi
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Sửa lỗi port của Railway (Railway cấp port động qua biến môi trường PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Cài đặt Composer và chạy install
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Khởi động Apache
CMD ["apache2-foreground"]
