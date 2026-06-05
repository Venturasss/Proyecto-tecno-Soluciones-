<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Project;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $projects = (new Project())->all();

        // Proyectos por estado
        $byStatus = ['Planificado' => 0, 'En progreso' => 0, 'En pausa' => 0, 'Finalizado' => 0, 'Cancelado' => 0];
        foreach ($projects as $p) {
            $s = $p['status'] ?? 'Planificado';
            if (isset($byStatus[$s])) {
                $byStatus[$s]++;
            } else {
                $byStatus[$s] = 1;
            }
        }

        // Presupuesto total
        $totalBudget = array_sum(array_column($projects, 'budget'));

        // Proyectos por mes (últimos 6 meses)
        $byMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = date('M', strtotime("-$i months"));
            $byMonth[$key] = 0;
        }
        foreach ($projects as $p) {
            if (!empty($p['start_date'])) {
                $month = date('M', strtotime($p['start_date']));
                if (isset($byMonth[$month])) {
                    $byMonth[$month]++;
                }
            }
        }

        $this->view('dashboard/index', [
            'title'         => 'Panel',
            'clientsCount'  => count((new Client())->all()),
            'projectsCount' => count($projects),
            'totalBudget'   => $totalBudget,
            'byStatus'      => $byStatus,
            'byMonth'       => $byMonth,
        ]);
    }
}