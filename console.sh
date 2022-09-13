#!/bin/bash
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
function docker-entrypoint-initdb {
    docker exec mysql mysql -uroot -e "create schema db;"
    docker exec -it mysql script /dev/null -c "mysql -uroot db < ./tmp/data/mysql-dump.sql"
}
function migrate {
    docker exec -it php script /dev/null -c "./bin/migrate migrate:init dev"
    docker exec -it php script /dev/null -c "./bin/migrate migrate:up dev"
}
function docker-build {
    docker-compose up -d --build 
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
    docker-compose up -d
}
function docker-list {
    docker ps --format "table {{.ID}}\t{{.Names}}\t{{.Ports}}"
}
function selenoid-up {
    ./cm selenoid start --vnc # curl -s https://aerokube.com/cm/bash | bash
}
function browsers-install {
    docker pull selenoid/vnc:chrome_76.0
}
function files-reset {
    sudo rm -rf ./src/vendor/
    sudo rm -rf ./src/bin/
    sudo rm ./src/composer.lock
    sudo rm -rf ./mysql/
    sudo rm -rf ./logs/
}
argument="$1"
    case $argument in
      "--reset" )
            docker-down
            files-reset
      ;;
      "--stop" )
            docker-down
      ;;
      "--l" )         
            docker-list
      ;;
      "--d" )
        docker-down
        docker-up
      ;;
      "--s" )         
            docker-down
            docker-up
            docker-list
            #docker-bash
            #clear
      ;;
      "--r" )            
            docker-down
            files-reset
            docker-build
            docker-up
            clear
            docker-list
            #sleep 5
            #docker-entrypoint-initdb   
            #migrate
            #docker-bash
      ;;
      "--c" )
            docker-down
            docker-remove
      ;;  
      "--e" )
            docker-entrypoint-initdb
      ;;  
      "--f" )
            files-reset 
      ;;          
  esac