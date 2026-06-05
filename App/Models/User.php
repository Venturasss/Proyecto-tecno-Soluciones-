<?php


declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    
    public function create(string $name, string $email, string $password): bool
    {
        $sql = 'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)';
        $stmt = Database::getConnection()->prepare($sql);

        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
     
            ]);
    }

    public function findByEmail(string $email): ?array

    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ? :null;
     } 

}