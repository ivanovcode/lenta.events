#!/bin/bash
function db-export-dump {
    /opt/homebrew/opt/mysql-client/bin/mysqldump -h 127.0.0.1 -uuser -p events > ./data/dump.sql --no-tablespaces --column-statistics=0
}
function ssl-get {
    #https://codex.so/wildcard-ssl
    ./acme.sh --register-account -m support@lenta.events
    ./acme.sh --issue --force -d lenta.events -d *.lenta.events --dns --yes-I-know-dns-manual-mode-enough-go-ahead-please
    ./acme.sh --renew -d lenta.events -d *.lenta.events --dns --yes-I-know-dns-manual-mode-enough-go-ahead-please
}
function docker-down {
    docker-compose down
    docker stop $(docker ps -a -q)
}
function docker-remove {
    docker rm -vf $(docker ps -a -q)
    docker rmi -f $(docker images -a -q)
}
function docker-mysql-init {
    docker exec mysql mysql -uroot -e "CREATE DATABASE db CHARACTER SET utf8 COLLATE utf8_general_ci;"
}
function docker-mysql-config {
    docker exec -it mysql sh -c "mysql -uroot db < ./tmp/data/mysql-config.sql"
}
function docker-mysql-dump {
    docker exec -it mysql sh -c "mysql -uroot db < ./tmp/data/dump.sql"
}
function migrate {
    docker exec -it php script /dev/null -c "./bin/migrate migrate:init dev"
    docker exec -it php script /dev/null -c "./bin/migrate migrate:up dev"
}
function docker-build {
    docker-compose up -d --build
}
function composer() {
    docker exec -u 0 php composer install
}
function docker-bash {
    docker exec -it php bash
}
function docker-run {
    docker exec -u 0 php php extraction.php
}
function docker-up {
    docker-compose down
    docker-compose up --build -d
}
function docker-bash {
    docker exec -it php bash
}
function docker-list {
    docker ps --format "table {{.ID}}\t{{.Names}}\t{{.Ports}}"
}
function selenoid-up {
    ./cm selenoid start --vnc # curl -s https://aerokube.com/cm/bash | bash
}
function browsers-install {
    docker pull sskorol/selenoid_chromium_vnc:100.0
}
function files-reset {
    sudo rm -rf ./src/vendor/
    sudo rm -rf ./src/bin/
    sudo rm ./src/composer.lock
    sudo rm -rf ./mysql/
    sudo rm -rf ./logs/
    sudo rm -rf ./selenoid/
}
argument="$1"
    case $argument in
      "--bash" )
            docker-bash
      ;;
      "--db" )
            docker-mysql-init
            docker-mysql-config
            docker-mysql-dump
      ;;
      "--db-export" )
            db-export-dump
      ;;
      "--db-init" )
            docker-mysql-init
      ;;
      "--db-config" )
            docker-mysql-config
      ;;
      "--db-dump" )
            docker-mysql-dump
      ;;
      "--install" )
            docker-down
            files-reset
            #docker-remove
            browsers-install
            docker-build
            composer
            sleep 5
            docker-mysql-init
            #docker-mysql-config
            docker-mysql-dump
            #migrate
            clear
            docker-list
      ;;
      "--build" )
            docker-down
            docker-build
            clear
            docker-list
      ;;
      "--up" )
            docker-up
      ;;
      "--down" )
            docker-down
      ;;
      "--reset" )
            docker-down
            files-reset
      ;;
      "--list" )
            docker-list
      ;;
      "--restart" )
            docker-down
            docker-up
      ;;
      "--clear" )
            files-reset 
      ;;
      "--php" )
            docker-bash
      ;;
  esac