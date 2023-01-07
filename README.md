- [Системные требования](#системные-требования)
    - [Windows](#windows)
    - [Linux](#linux)
        - [Установка Docker](#установка-docker)
        - [Установка docker-compose](#установка-docker-compose)
- [Развертывание проекта](#развертывание-проекта)
    - [Настройка проекта](#настройка-домена)
    - [Первый запуск приложения](#первый-запуск-приложения)
    - [Последующий запуск](#последующий-запуск)

# Системные требования
## Windows

Необходимо, чтобы на компьютере был настроен [WSL](https://docs.microsoft.com/en-us/windows/wsl/install) и установлен [Docker](https://docs.docker.com/desktop/windows/install/). С инструкциями по установке можно ознакомиться по ссылкам, приведенным в предыдущем предложении.

## Linux

### Установка Docker

С помощью следующих команд мы настроим репозиторий для установки Docker

    sudo apt update
    sudo apt install apt-transport-https ca-certificates curl software-properties-common
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu focal stable"

После успешного добавления репозитория, установим Docker

    sudo apt install docker-ce

Теперь Docker должен быть доступен в системе, проверить можно командой

    sudo systemctl status docker

### Установка docker-compose

Запустите следующую команду для скачивания текущей стабильной версии

    sudo curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

> Чтобы установить другую версию Compose, измените 1.29.2 на ту версию, которую хотите установить

Добавьте права исполнения для исходных файлов

    sudo chmod +x /usr/local/bin/docker-compose

# Настройка домена

Чтобы обращаться к проекту по домену, необходимо зарегистрировать его в локальном hosts-файле:

    sudo cat /etc/hosts

    127.0.0.1 diploma.local

# Развертывание проекта

Клонируйте репозиторий с проектом

    git clone ...

Запускаем docker-compose

    docker-compose up -d // на windows эта команда должна всегда отрабатывать
    или
    sudo docker-compose up -d // если на linux не прокатило с обычными правами
    или
    make up 

> Под linux возможно понадобится выдать права на папки `storage` и `vendor` командами:

    sudo chmod 777 -R vendor
    sudo chmod 777 -R storag

Если все прошло успешно, то выполняем следующую команду для проверки работы контейнеров

    [sudo] docker ps

Результат должен выглядеть примерно так

    CONTAINER ID   IMAGE             COMMAND                  CREATED        STATUS          PORTS                                   NAMES
    acea2edc0f97   nginx:alpine      "/docker-entrypoint.…"   46 hours ago   Up 28 minutes   0.0.0.0:8080->80/tcp, :::8080->80/tcp   diploma-hr-nginx
    4bfa9e71f2bd   diploma-hr-php    "docker-php-entrypoi…"   46 hours ago   Up 28 minutes   9000/tcp                                diploma-hr-php
    3495dd6523c5   postgres:alpine   "docker-entrypoint.s…"   46 hours ago   Up 28 minutes   127.0.0.1:5431->5432/tcp                diploma-hr-postgres

## Первый запуск приложения

Заходим в контейнер с `php`

    [sudo] docker exec -it diploma-hr-php sh

Выполняем внутри контейнера следующие команды

    composer install
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    php bin/console dcotrine:fixtures:load

Затем устанавливаем зависимости `frontend`

    npm install

Пробуем запустить `frontend`

    npm run watch

Если все прошло успешно, то приложение будет доступно на `localhost:8080` или `diploma.local:8080`

## Последующий запуск

Запускаем контейнеры командой

    [sudo] docker-compose up -d 
    или 
    make up

И запускаем в контейнере приложения `frontend`

    [sudo] docker exec -it diploma-hr-php npm run watch
    или 
    make watch

