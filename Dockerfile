FROM php:7.2-fpm
RUN apt-get update && apt-get install -y libz-dev libmemcached-dev libpng-dev supervisor

RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql


RUN apt-get install -y libmagickwand-dev imagemagick \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && echo "extension=$(find /usr/local/lib/php/extensions/ -name imagick.so)" > /usr/local/etc/php/conf.d/imagick.ini

RUN \
    apt-get update && \
    apt-get install libldap2-dev -y && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-install ldap && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install gd && \
    docker-php-ext-install intl



RUN docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-configure xml \
    && docker-php-ext-install \
        mbstring \
        xml \
  && docker-php-ext-configure gd \
    #--enable-gd-native-ttf \
    --with-jpeg-dir=/usr/lib \
    --with-freetype-dir=/usr/include/freetype2 && \
    docker-php-ext-install gd \
  && docker-php-ext-install opcache \
  && docker-php-ext-enable opcache

# install git
#RUN apt-get install -y git

# Install php-igbinary
RUN yes | pecl install igbinary \
    docker-php-ext-enable igbinary

# install xdebug
RUN yes | pecl install xdebug \
   && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.profiler_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini
# install xhrof
#RUN git clone https://github.com/longxinH/xhprof.git /var/xhprof \
#	&& cd /var/xhprof/extension/ \
#	&& phpize \
#	&& ./configure --with-php-config=/usr/local/bin/php-config \
#	&& make && make install \
#	&& apt-get install -y graphviz \
#	&& echo "extension=$(find /usr/local/lib/php/extensions/ -name xhprof.so)" > /usr/local/etc/php/conf.d/xhprof.ini \
#	&& echo "xhprof.output_dir=/var/xhprofreports" >> /usr/local/etc/php/conf.d/xhprof.ini \
#	&& mkdir /var/xhprofreports \
#	&& chmod 0777 /var/xhprofreports

RUN pecl install -o -f redis \
  &&  rm -rf /tmp/pear \
  &&  docker-php-ext-enable redis

COPY ./docker-env/extensions/eusphpe_extension/ /usr/local/lib/php/extensions/no-debug-non-zts-20170718/eusphpe_extension
COPY ./docker-env/eusphpe.ini /usr/local/etc/php/conf.d/eusphpe.ini
ENV LD_LIBRARY_PATH="/usr/local/lib/php/extensions/no-debug-non-zts-20170718/eusphpe_extension/"