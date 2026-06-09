<?php
declare(strict_types = 1);

define('APP_NAME', 'tecnoSoluciones S.A');
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost:8000/');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'tecnosoluciones');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
