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
# Railway might build from 'public' directory, so we need to handle both cases
COPY . .

# If Railway built from 'public', we need to copy parent directories
# Check if we're in public directory and copy parent structure if needed
RUN if [ ! -d "/app/database" ] && [ -d "/app/../database" ]; then \
        echo "Copying parent directories..."; \
        cp -r /app/../database /app/ 2>/dev/null || true; \
        cp -r /app/../config /app/ 2>/dev/null || true; \
        cp -r /app/../app /app/ 2>/dev/null || true; \
    fi

# Show directory structure for debugging
RUN echo "=== Contents of /app ===" && ls -la /app && \
    echo "=== Checking for database directory ===" && \
    (test -d "/app/database" && echo "✓ database directory exists" || echo "✗ database directory NOT found") && \
    (test -d "/app/config" && echo "✓ config directory exists" || echo "✗ config directory NOT found") && \
    (test -d "/app/app" && echo "✓ app directory exists" || echo "✗ app directory NOT found")

# Install PHP dependencies (if composer.json exists)
# Don't fail silently - we need Cloudinary SDK
RUN if [ -f "composer.json" ]; then \
        echo "=== Installing Composer dependencies ==="; \
        echo "Composer.json contents:"; \
        cat composer.json || true; \
        composer install --no-dev --optimize-autoloader --verbose 2>&1 || { \
            echo "=== Composer install failed, trying with --ignore-platform-reqs ==="; \
            composer install --no-dev --optimize-autoloader --ignore-platform-reqs --verbose 2>&1 || { \
                echo "=== Composer install still failed ==="; \
                exit 1; \
            }; \
        }; \
        echo "=== Composer install completed ==="; \
        if [ -f "vendor/autoload.php" ]; then \
            echo "✓ vendor/autoload.php exists"; \
            echo "=== Checking vendor directory contents ==="; \
            ls -la vendor/ || true; \
            echo "=== Checking for Cloudinary ==="; \
            if [ -d "vendor/cloudinary" ]; then \
                echo "✓ vendor/cloudinary directory exists"; \
                ls -la vendor/cloudinary/ || true; \
            else \
                echo "✗ vendor/cloudinary directory NOT found"; \
                echo "=== Attempting to install Cloudinary directly ==="; \
                composer require cloudinary/cloudinary_php --no-dev --optimize-autoloader --verbose 2>&1 || true; \
            fi; \
            if [ -f "vendor/cloudinary/cloudinary_php/src/Cloudinary.php" ]; then \
                echo "✓ Cloudinary.php found"; \
            else \
                echo "✗ Cloudinary.php NOT found"; \
                echo "Searching for Cloudinary files..."; \
                find vendor -name "*Cloudinary*" -type f 2>/dev/null | head -5 || true; \
            fi; \
        else \
            echo "✗ vendor/autoload.php NOT found"; \
        fi; \
    else \
        echo "No composer.json found, skipping Composer install"; \
    fi

# Create upload directories structure (will be mounted as volume in Railway)
# These directories will be created if volume is not mounted, or Railway will mount volume here
RUN mkdir -p /app/public/uploads/profile_pics \
    && mkdir -p /app/public/uploads/events/gallery \
    && mkdir -p /app/public/uploads/subcategories \
    && mkdir -p /app/public/uploads/venues \
    && mkdir -p /app/public/uploads/categories \
    && chmod -R 755 /app/public/uploads

# Expose port (Railway will set PORT env var)
EXPOSE 8080

# Start PHP built-in server with router
# If files are at /app (public as root), serve from /app
# If files are at /app/public (project root), serve from /app/public with router
CMD if [ -d "/app/public" ]; then \
        echo "Serving from /app/public (project root detected)"; \
        php -S 0.0.0.0:${PORT:-8080} -t /app/public /app/public/router.php; \
    else \
        echo "Serving from /app (public as root detected)"; \
        php -S 0.0.0.0:${PORT:-8080} -t /app /app/router.php; \
    fi

