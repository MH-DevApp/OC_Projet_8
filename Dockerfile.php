FROM ubuntu:22.04
RUN apt-get update && apt-get install -y zip git libicu-dev locales
RUN locale-gen fr_FR.UTF-8
RUN apt-get update \
    && apt-get install -y curl \
    && apt-get install -y software-properties-common \
    && apt-get update \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends tzdata \
    && apt-get install -y php5.6 \
    && apt-get install -y php5.6-mbstring php5.6-mcrypt php5.6-mysql php5.6-xml php5.6-curl php5.6-intl php5.6-xdebug \
    && rm -rf /var/lib/apt/lists/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN sed -ri -e 's!;date.timezone =!date.timezone = "Europe/Paris"!g' /etc/php/5.6/cli/php.ini
RUN echo "zend_extension=xdebug.so" >> /etc/php/5.6/cli/php.ini \
    && echo "xdebug.remote_enable=1" >> /etc/php/5.6/cli/php.ini \
    && echo "xdebug.remote_autostart=1" >> /etc/php/5.6/cli/php.ini
WORKDIR /app
COPY . .
RUN symfony composer install --no-scripts
RUN symfony server:ca:install
CMD ["symfony", "server:start"]
