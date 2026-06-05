<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Project;

final class ProjectController extends Controller
{
    private Project $projects;

    public function __construct()
    {
        $this->projects = new Project();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('projects/index', [
            'title'     => 'Proyectos',
            'projects'  => $this->projects->all(),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('projects/form', [
            'title'     => 'Nuevo proyecto',
            'project'   => null,
            'clients'   => (new Client())->all(),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $this->projects->create($_POST);

        $this->flash('success', 'Proyecto creado correctamente.');
        $this->redirect('proyectos');
    }

    public function edit(): void
    {
        $this->requireAuth();

        $project = $this->projects->find((int) ($_GET['id'] ?? 0));

        if (!$project) {
            http_response_code(404);
            exit('Proyecto no encontrado.');
        }

        $this->view('projects/form', [
            'title'     => 'Editar proyecto',
            'project'   => $project,
            'clients'   => (new Client())->all(),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $this->projects->update((int) ($_POST['id'] ?? 0), $_POST);

        $this->flash('success', 'Proyecto actualizado correctamente.');
        $this->redirect('proyectos');
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->projects->delete($id);
            $this->flash('success', 'Proyecto eliminado correctamente.');
        } else {
            $this->flash('error', 'No se pudo eliminar el proyecto.');
        }

        $this->redirect('proyectos');
    }
}