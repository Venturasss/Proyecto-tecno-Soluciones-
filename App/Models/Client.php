<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Client
{
    public function all(): array
    {
        return Database::getConnection()
            ->query('SELECT * FROM clients ORDER BY created_at DESC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM clients WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        return $client ?: null;
    }

    public function create(string $name, string $email, string $phone, string $company = 'Particular', ?string $address = null): bool
    {
        $sql = 'INSERT INTO clients (name, email, phone, company, address)
                VALUES (:name, :email, :phone, :company, :address)';

        $data = [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'company' => $company,
            'address' => $address,
        ];

        return Database::getConnection()->prepare($sql)->execute($this->payload($data));
    }

    public function update(int $id, string $name, string $email, string $phone, string $company = 'Particular', ?string $address = null): bool
    {
        $sql = 'UPDATE clients
                SET name = :name, email = :email, phone = :phone, company = :company, address = :address
                WHERE id = :id';

        $data = [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'company' => $company,
            'address' => $address,
        ];

        $payload       = $this->payload($data);
        $payload['id'] = $id;

        return Database::getConnection()->prepare($sql)->execute($payload);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM clients WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function payload(array $data): array
    {
        return [
            'name'    => trim($data['name']    ?? ''),
            'email'   => trim($data['email']   ?? ''),
            'phone'   => trim($data['phone']   ?? ''),
            'company' => trim($data['company'] ?? '') ?: 'Particular',
            'address' => isset($data['address']) && $data['address'] !== ''
                         ? trim($data['address'])
                         : null,
        ];
    }
}