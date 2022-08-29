### Dev

phpstan:
	php -d memory_limit=-1 vendor/bin/phpstan.phar analyse -l max -c phpstan.neon src

test:
	php tests/TestCreateNewUser.php
	php tests/TestUpdateUser.php
	php tests/TestCreateOrder.php

