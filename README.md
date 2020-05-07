# bilemo

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/60dd9df809054e6cb7198551d7bf6777)](https://www.codacy.com/manual/imaneis/projet07oc?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=imaneis/projet07oc&amp;utm_campaign=Badge_Grade)

## About

A web service exposing an API.
Project 7 of the OpenClassrooms "Application Developer - PHP / Symfony" course.

## Requirements

* PHP: SnowTricks requires PHP version 7.1 or greater.
* MySQL: for the database.
* Composer: to install the dependencies. 

## Installation

### Git Clone

You can also download the bilemo source directly from the Git clone:

    git clone https://github.com/imaneis/projet07oc bilemo
    cd bilemo

Give write access to the /var directory

    chmod 777 -R var

Then

    composer update

Configure the application by completing the file /app/config/parameters.yml

    php bin/console doctrine:schema:update --dump-sql
    php bin/console doctrine:schema:update --force

If you want to use a data set

    php bin/console doctrine:fixtures:load

Configure the jwt authentication

    mkdir var/jwt
    openssl genrsa -out var/jwt/private.pem -aes256 4096
    openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem

Changes the jwt_key_pass_phrase parameter in the 'app/config/parameters.yml' file.



## Using the API

Use the documentation at the address:

    http://my.server/api/doc

## Author
Imane Issany
