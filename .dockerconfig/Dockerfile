FROM php:8.1.8-apache

RUN /bin/cp /usr/share/zoneinfo/Africa/Blantyre /etc/localtime && \
echo "Africa/Blantyre" > /etc/timezone


COPY ./conf/000-default.conf /etc/apache2/sites-available/
RUN ln -s ../mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

RUN apt-get update
RUN apt-get install -y \
      git \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
      libzip-dev\
      zip \
      unzip \
      nano \
      vim 
RUN apt-get install libldap2-dev -y && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
RUN docker-php-ext-install gd pdo zip pdo_mysql ldap
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ENV LESSCHARSET=utf-8 

COPY ./php/php-local.ini /usr/local/etc/php/php.ini

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs