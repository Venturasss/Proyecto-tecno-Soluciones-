<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Services\PdfReport;

final class ReportController extends Controller
{
    private function signaturePath(): string
    {
        return dirname(__DIR__, 2) . '/public/signature.png';
    }

    public function clients(): void
    {
        $this->requireAuth();
        $pdf = new PdfReport();
        $pdf->title('Reporte de Clientes');

        foreach ((new Client())->all() as $i => $client) {
            $pdf->sectionHeader('Cliente #' . ($i + 1));
            $pdf->row('Nombre:',   $client['name']);
            $pdf->row('Empresa:',  $client['company'] ?: 'Sin empresa');
            $pdf->row('Email:',    $client['email']);
            $pdf->row('Telefono:', $client['phone'] ?: 'No registrado');
            $pdf->spacer();
            $pdf->divider();
            $pdf->spacer();
        }

        $pdf->signature($this->signaturePath(), 'Luis Ventura', 'Administrador del Sistema');
        $pdf->output('reporte-clientes.pdf');
    }

    public function client(): void
    {
        $this->requireAuth();

        $id     = (int) ($_GET['id'] ?? 0);
        $client = (new Client())->find($id);

        if (!$client) {
            http_response_code(404);
            exit('Cliente no encontrado.');
        }

        $projects    = (new Project())->byClient($id);
        $totalBudget = array_sum(array_column($projects, 'budget'));

        $pdf = new PdfReport();
        $pdf->title('Ficha de Cliente');

        $pdf->sectionHeader('Datos del cliente');
        $pdf->row('Nombre:',    $client['name']);
        $pdf->row('Empresa:',   $client['company'] ?: 'Sin empresa');
        $pdf->row('Email:',     $client['email']);
        $pdf->row('Telefono:',  $client['phone'] ?: 'No registrado');
        if (!empty($client['address'])) {
            $pdf->row('Direccion:', $client['address']);
        }
        $pdf->row('Registrado:', date('d/m/Y', strtotime($client['created_at'])));

        $pdf->spacer();
        $pdf->divider();
        $pdf->spacer();

        if (empty($projects)) {
            $pdf->sectionHeader('Proyectos');
            $pdf->spacer();
            $pdf->text('Este cliente no tiene proyectos registrados.');
        } else {
            $pdf->sectionHeader('Proyectos (' . count($projects) . ')');
            $pdf->spacer();

            $rows = [];
            foreach ($projects as $project) {
                $rows[] = [
                    $project['name'],
                    $project['status'],
                    !empty($project['start_date']) ? date('d/m/Y', strtotime($project['start_date'])) : '-',
                    !empty($project['end_date'])   ? date('d/m/Y', strtotime($project['end_date']))   : '-',
                    '$' . number_format((float) $project['budget'], 2),
                ];
            }

            $pdf->table(
                ['Proyecto', 'Estado', 'Inicio', 'Fin', 'Presupuesto'],
                $rows
            );

            $pdf->barChart($projects);

            $pdf->summaryBox([
                ['Total de proyectos', (string) count($projects)],
                ['Total generado',     '$' . number_format($totalBudget, 2)],
            ]);
        }

        $pdf->signature($this->signaturePath(), 'Luis Ventura', 'Administrador del Sistema');
        $pdf->output('cliente-' . $id . '.pdf');
    }

    public function projects(): void
    {
        $this->requireAuth();
        $projects = (new Project())->all();

        $pdf = new PdfReport();
        $pdf->title('Reporte de Proyectos');

        foreach ($projects as $i => $project) {
            $pdf->sectionHeader('Proyecto #' . ($i + 1));
            $pdf->row('Nombre:',      $project['name']);
            $pdf->row('Cliente:',     $project['client_name']);
            $pdf->row('Estado:',      $project['status']);
            $pdf->row('Inicio:',      !empty($project['start_date']) ? date('d/m/Y', strtotime($project['start_date'])) : 'No definido');
            $pdf->row('Fin:',         !empty($project['end_date'])   ? date('d/m/Y', strtotime($project['end_date']))   : 'No definido');
            $pdf->row('Presupuesto:', '$' . number_format((float) $project['budget'], 2));
            $pdf->spacer();
            $pdf->divider();
            $pdf->spacer();
        }

        $pdf->barChart($projects);

        $totalBudget = array_sum(array_column($projects, 'budget'));
        $pdf->summaryBox([
            ['Total de proyectos', (string) count($projects)],
            ['Presupuesto total',  '$' . number_format($totalBudget, 2)],
        ]);

        $pdf->signature($this->signaturePath(), 'Luis Ventura', 'Administrador del Sistema');
        $pdf->output('reporte-proyectos.pdf');
    }

    public function project(): void
    {
        $this->requireAuth();

        $id      = (int) ($_GET['id'] ?? 0);
        $project = (new Project())->find($id);

        if (!$project) {
            http_response_code(404);
            exit('Proyecto no encontrado.');
        }

        $totalDays   = 0;
        $elapsedDays = 0;
        $progress    = 0;

        if (!empty($project['start_date']) && !empty($project['end_date'])) {
            $startDate   = new \DateTime($project['start_date']);
            $endDate     = new \DateTime($project['end_date']);
            $today       = new \DateTime();
            $totalDays   = (int) $startDate->diff($endDate)->days;
            $elapsedDays = (int) $startDate->diff($today)->days;
            $elapsedDays = min($elapsedDays, $totalDays);
            $progress    = $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0;
        }

        $inicioStr = !empty($project['start_date']) ? date('d/m/Y', strtotime($project['start_date'])) : 'No definido';
        $finStr    = !empty($project['end_date'])   ? date('d/m/Y', strtotime($project['end_date']))   : 'No definido';

        $pdf = new PdfReport();
        $pdf->title('Ficha de Proyecto');

        $pdf->sectionHeader($project['name']);
        $pdf->row('Nombre:',      $project['name']);
        $pdf->row('Cliente:',     $project['client_name']);
        $pdf->row('Estado:',      $project['status']);
        $pdf->row('Inicio:',      $inicioStr);
        $pdf->row('Fin:',         $finStr);
        $pdf->row('Presupuesto:', '$' . number_format((float) $project['budget'], 2));

        if (!empty($project['description'])) {
            $pdf->spacer();
            $pdf->sectionHeader('Descripcion');
            $pdf->text($project['description']);
        }

        if ($totalDays > 0) {
            $pdf->spacer();
            $pdf->progressBar('Progreso de tiempo', $progress, $elapsedDays . ' de ' . $totalDays . ' dias');
        }

        $pdf->spacer();
        $pdf->summaryBox([
            ['Presupuesto asignado', '$' . number_format((float) $project['budget'], 2)],
            ['Estado actual',        $project['status']],
            ['Duracion total',       $totalDays > 0 ? $totalDays . ' dias' : 'No definida'],
        ]);

        $pdf->signature($this->signaturePath(), 'Luis Ventura', 'Administrador del Sistema');
        $pdf->output('proyecto-' . $id . '.pdf');
    }
}