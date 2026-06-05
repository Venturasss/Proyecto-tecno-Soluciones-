<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Invoice
{
    public function all(): array
    {
        $sql = 'SELECT invoices.*, projects.name AS project_name,
                       clients.name AS client_name
                FROM invoices
                INNER JOIN projects ON projects.id = invoices.project_id
                INNER JOIN clients  ON clients.id  = projects.client_id
                ORDER BY invoices.created_at DESC';

        return Database::getConnection()->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT invoices.*, projects.name AS project_name,
                       projects.budget, projects.start_date, projects.end_date,
                       projects.description AS project_description,
                       clients.name AS client_name, clients.email AS client_email,
                       clients.phone AS client_phone, clients.company AS client_company
                FROM invoices
                INNER JOIN projects ON projects.id = invoices.project_id
                INNER JOIN clients  ON clients.id  = projects.client_id
                WHERE invoices.id = :id';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByProject(int $projectId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM invoices WHERE project_id = :pid ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $projectId, ?string $notes = null): int
    {
        $pdo = Database::getConnection();

        $count = (int) $pdo->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
        $number = 'FAC-' . date('Y') . '-' . str_pad((string)($count + 1), 3, '0', STR_PAD_LEFT);

        $sql = 'INSERT INTO invoices (project_id, number, issued_at, due_at, status, notes)
                VALUES (:project_id, :number, :issued_at, :due_at, :status, :notes)';

        $pdo->prepare($sql)->execute([
            'project_id' => $projectId,
            'number'     => $number,
            'issued_at'  => date('Y-m-d'),
            'due_at'     => date('Y-m-d', strtotime('+30 days')),
            'status'     => 'Pendiente',
            'notes'      => $notes,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM invoices WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}