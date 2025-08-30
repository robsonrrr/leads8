#!/bin/bash

echo "Copying fixed files to Docker container..."

# Copy fixed files to Docker container
docker cp /home/ubuntu/environment/Office/Apps/inProduction/leads/leads6/system/classes/Kohana/HTTP/Header.php leads6.1.j4hwuabpiysq2m8iuomgap196:/var/www/html/system/classes/Kohana/HTTP/Header.php
docker cp /home/ubuntu/environment/Office/Apps/inProduction/leads/leads6/system/classes/HTTP/Message.php leads6.1.j4hwuabpiysq2m8iuomgap196:/var/www/html/system/classes/HTTP/Message.php
docker cp /home/ubuntu/environment/Office/Apps/inProduction/leads/leads6/application/bootstrap.php leads6.1.j4hwuabpiysq2m8iuomgap196:/var/www/html/application/bootstrap.php
docker cp /home/ubuntu/environment/Office/Apps/inProduction/leads/leads6/application/classes/Kohana.php leads6.1.j4hwuabpiysq2m8iuomgap196:/var/www/html/application/classes/Kohana.php
docker cp /home/ubuntu/environment/Office/Apps/inProduction/leads/leads6/application/classes/Kohana/Log/File.php leads6.1.j4hwuabpiysq2m8iuomgap196:/var/www/html/application/classes/Kohana/Log/File.php

# Check PHP version
echo "\nPHP version:"
docker exec -it leads6.1.j4hwuabpiysq2m8iuomgap196 php -v

# Test the application
echo "\nTesting the application..."
docker exec -it leads6.1.j4hwuabpiysq2m8iuomgap196 curl -v http://localhost/lead/index/638050/1

# Check for errors in the log
echo "\nChecking for errors in the log:"
docker exec -it leads6.1.j4hwuabpiysq2m8iuomgap196 tail -n 50 /var/log/apache2/error.log

echo "\nDone!"