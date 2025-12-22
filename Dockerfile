# Use PHP 8.3 with built-in server
FROM php:8.3-cli

# Install system dependencies and MySQL extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        mbstring \
        zip \
        xml \
        curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Verify PDO MySQL is installed (this will fail build if not installed)
RUN php -r "if (!extension_loaded('pdo_mysql')) { echo 'ERROR: PDO MySQL extension not loaded!\n'; echo 'Available PDO drivers: '; print_r(PDO::getAvailableDrivers()); echo '\nAll extensions: '; print_r(get_loaded_extensions()); exit(1); } else { echo 'SUCCESS: PDO MySQL extension is installed!\n'; echo 'Available PDO drivers: '; print_r(PDO::getAvailableDrivers()); }"

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
# Note: If Railway's root is set to 'public', files will be at /app directly
# If Railway's root is project root, we need to copy everything including public/
COPY . .

# Show directory structure for debugging
RUN echo "=== Contents of /app ===" && ls -la /app

# Install PHP dependencies (if composer.json exists)
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader || true; fi

# Expose port (Railway will set PORT env var)
EXPOSE 8080

# Start PHP built-in server
# If files are at /app (public as root), serve from /app
# If files are at /app/public (project root), serve from /app/public
CMD if [ -d "/app/public" ]; then \
        echo "Serving from /app/public (project root detected)"; \
        php -S 0.0.0.0:${PORT:-8080} -t /app/public; \
    else \
        echo "Serving from /app (public as root detected)"; \
        php -S 0.0.0.0:${PORT:-8080} -t /app; \
    fi

