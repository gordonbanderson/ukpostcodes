FROM php:7.3-cli-buster

RUN apt -y update && apt -y upgrade
RUN apt -y install figlet git zip unzip libxml2-dev
RUN apt-get -y autoremove && apt-get -y clean
RUN docker-php-ext-install soap

# alter bash prompt
ENV PS1A="\u@ukpostcodes.dev:\w> "
RUN echo 'PS1=$PS1A' >> ~/.bashrc

# intro message when attaching to shell
RUN echo 'figlet -w 120 PHP Client UK Postcodes API Dev' >> ~/.bashrc

# install composer - see https://medium.com/@c.harrison/speedy-composer-installs-in-docker-builds-41eea6d0172b
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && \
    composer global require hirak/prestissimo --no-plugins --no-scripts

# Prevent the container from exiting
CMD tail -f /dev/null
