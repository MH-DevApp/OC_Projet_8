# Utiliser l'image officielle de PHP 5.6
FROM ubuntu:22.04
RUN apt-get update \
    && apt-get install -y curl \
    && apt-get install -y software-properties-common \
    && apt-get update \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends tzdata \
    && apt-get install -y php5.6 \
    && apt-get install -y php5.6-mbstring php5.6-mcrypt php5.6-mysql php5.6-xml \
    && rm -rf /var/lib/apt/lists/* \
RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/local/bin
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN apt update && apt install -y zip git libicu-dev locales
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN locale-gen fr_FR.UTF-8
WORKDIR /app
COPY . .
RUN symfony composer install --no-scripts
RUN symfony server:ca:install
CMD ["symfony", "server:start"]
