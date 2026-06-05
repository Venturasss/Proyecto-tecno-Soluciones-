<?php

declare(strict_types=1);

namespace App\Core;
use PDO;
use PDOException;

final class Database
{
   private static ?PDO $connection = null;

   public static function getConnection(): PDO
   {
    if (self::$connection instanceof PDO){ 
      return self::$connection;
    }

    $dsn = sprintf(
      'mysql:host=%s;dbname=%s;charset=%s', 
      DB_HOST,
      DB_NAME,
      DB_CHARSET
    );

    try{
      self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
    }catch (PDOException $exception) {
      http_response_code(500);
      exit('no se puede conectar con la base de datos.');
    }

    return self::$connection;
   }
}