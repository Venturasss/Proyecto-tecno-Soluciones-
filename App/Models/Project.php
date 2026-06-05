<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Project
{
    public function all(): array
    {
        $sql = 'SELECT projects.*, clients.name AS client_name
                FROM projects
                INNER JOIN clients ON clients.id = projects.client_id
                ORDER BY projects.created_at DESC';

        return Database::getConnection()->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT projects.*, clients.name AS client_name
                FROM projects
                INNER JOIN clients ON clients.id = projects.client_id
                WHERE projects.id = :id';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();

        return $project ?: null;
    }

    /** Todos los proyectos de un cliente específico */
    public function byClient(int $clientId): array
    {
        $sql = 'SELECT * FROM projects
                WHERE client_id = :client_id
                ORDER BY created_at DESC';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO projects (client_id, name, description, status, start_date, end_date, budget)
                VALUES (:client_id, :name, :description, :status, :start_date, :end_date, :budget)';

        return Database::getConnection()->prepare($sql)->execute($this->payload($data));
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE projects
                SET client_id = :client_id, name = :name, description = :description,
                    status = :status, start_date = :start_date, end_date = :end_date, budget = :budget
                WHERE id = :id';

        return Database::getConnection()->prepare($sql)->execute($this->payload($data, $id));
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM projects WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function payload(array $data, ?int $id = null): array
    {
        $payload = [
            'client_id'   => (int)   ($data['client_id']   ?? 0),
            'name'        => trim($data['name']             ?? ''),
            'description' => trim($data['description']      ?? ''),
            'status'      => trim($data['status']           ?? 'Planificado'),
            'start_date'  => !empty($data['start_date'])    ? $data['start_date'] : null,
            'end_date'    => !empty($data['end_date'])      ? $data['end_date']   : null,
            'budget'      => (float) ($data['budget']       ?? 0),
        ];

        if ($id !== null) {
            $payload['id'] = $id;
        }

        return $payload;
    }
}