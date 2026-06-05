<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;

final class Clientcontroller extends Controller
{
    private Client $clients;

    public function __construct()
    {
        $this->clients = new Client();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('clients/index', [
            'title'     => 'Clientes',
            'clients'   => $this->clients->all(),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('clients/form', [
            'title'     => 'Nuevo cliente',
            'client'    => null,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $this->clients->create(
            $_POST['name']    ?? '',
            $_POST['email']   ?? '',
            $_POST['phone']   ?? '',
            $_POST['company'] ?? 'Particular',
            $_POST['address'] ?? null
        );

        $this->flash('success', 'Cliente creado correctamente.');
        $this->redirect('clientes');
    }

    public function edit(): void
    {
        $this->requireAuth();

        $client = $this->clients->find((int) ($_GET['id'] ?? 0));

        if (!$client) {
            http_response_code(404);
            exit('Cliente no encontrado.');
        }

        $this->view('clients/form', [
            'title'     => 'Editar cliente',
            'client'    => $client,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $this->clients->update(
            (int) ($_POST['id'] ?? 0),
            $_POST['name']    ?? '',
            $_POST['email']   ?? '',
            $_POST['phone']   ?? '',
            $_POST['company'] ?? 'Particular',
            $_POST['address'] ?? null
        );

        $this->flash('success', 'Cliente actualizado correctamente.');
        $this->redirect('clientes');
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->clients->delete($id);
            $this->flash('success', 'Cliente eliminado correctamente.');
        } else {
            $this->flash('error', 'No se pudo eliminar el cliente.');
        }

        $this->redirect('clientes');
    }
}