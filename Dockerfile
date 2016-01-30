FROM ubuntu:trusty
MAINTAINER Chris McKnight <cmckni3@gmail.com>

## Prepare
RUN apt-get clean all && apt-get update && apt-get upgrade -y

# Build Tools
RUN apt-get update && \
    apt-get install -y build-essential zlib1g-dev libssl-dev libreadline6-dev libyaml-dev libicu-dev libxslt-dev pkg-config && \
    apt-get install -y make wget tar git curl && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

## Install libsodium
ENV LIBSODIUM_VERSION 1.0.8

RUN wget https://github.com/jedisct1/libsodium/releases/download/$LIBSODIUM_VERSION/libsodium-$LIBSODIUM_VERSION.tar.gz && \
  tar xzvf libsodium-$LIBSODIUM_VERSION.tar.gz && \
  cd libsodium-$LIBSODIUM_VERSION && \
  ./configure && \
  make && make check && sudo make install && \
  cd .. && rm -rf libsodium-$LIBSODIUM_VERSION && \
  sudo ldconfig

# Install PHP
RUN apt-get update && \
    apt-get install -y php5 php5-dev php-pear && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew && \
chmod +x phpbrew && \
mv phpbrew /usr/bin/phpbrew && \
phpbrew init

RUN /bin/bash -c "source ~/.phpbrew/bashrc && \
phpbrew update && \
echo '[[ -e ~/.phpbrew/bashrc ]] && source ~/.phpbrew/bashrc' >> ~/.bashrc"

RUN /bin/bash -c "source ~/.phpbrew/bashrc && \
phpbrew install 5.3.29 +default -mcrypt -bz2 && \
phpbrew install 5.4.45 +default -mcrypt -bz2 && \
phpbrew install 5.5.31 +default -mcrypt -bz2 && \
phpbrew install 5.6.17 +default -mcrypt -bz2 && \
phpbrew install 7.0.2  +default -mcrypt -bz2"

# Install libsodium-php
RUN /bin/bash -c "source ~/.phpbrew/bashrc && \
phpbrew use 5.3.29 && \
phpbrew ext install https://github.com/jedisct1/libsodium-php.git && \
phpbrew use 5.4.45 && \
phpbrew ext install https://github.com/jedisct1/libsodium-php.git && \
phpbrew use 5.5.31 && \
phpbrew ext install https://github.com/jedisct1/libsodium-php.git && \
phpbrew use 5.6.17 && \
phpbrew ext install https://github.com/jedisct1/libsodium-php.git && \
phpbrew use 7.0.2 && \
phpbrew ext install https://github.com/jedisct1/libsodium-php.git && \
phpbrew clean php-5.3.29 && \
phpbrew clean php-5.4.45 && \
phpbrew clean php-5.5.31 && \
phpbrew clean php-5.6.17 && \
phpbrew clean php-7.0.2"

WORKDIR /usr/src

# Install composer
RUN curl -sS https://getcomposer.org/installer | php && \
mv composer.phar /usr/bin/composer && \
# Install phpunit
wget https://phar.phpunit.de/phpunit-4.8.9.phar && \
chmod +x phpunit-4.8.9.phar && \
mv phpunit-4.8.9.phar /usr/bin/phpunit

# Add source
ADD . /usr/src

CMD ['/bin/bash', '-c', 'source ~/.phpbrew/bashrc']
