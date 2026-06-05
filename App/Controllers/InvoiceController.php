<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\PdfReport;

final class InvoiceController extends Controller
{
    private Invoice $invoices;

    public function __construct()
    {
        $this->invoices = new Invoice();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('invoices/index', [
            'title'    => 'Facturas',
            'invoices' => $this->invoices->all(),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $projectId = (int) ($_GET['proyecto'] ?? 0);

        if ($projectId === 0) {
            $this->flash('error', 'Proyecto no válido.');
            $this->redirect('proyectos');
        }

        // Si ya existe factura para este proyecto, ir directo al PDF
        $existing = $this->invoices->findByProject($projectId);
        if ($existing) {
            $this->redirect('facturas/pdf?id=' . $existing['id']);
        }

        $id = $this->invoices->create($projectId);
        $this->flash('success', 'Factura generada correctamente.');
        $this->redirect('facturas/pdf?id=' . $id);
    }

    public function pdf(): void
{
    $this->requireAuth();

    $id      = (int) ($_GET['id'] ?? 0);
    $invoice = $this->invoices->find($id);

    if (!$invoice) {
        http_response_code(404);
        exit('Factura no encontrada.');
    }

    $subtotal = (float) $invoice['budget'];
    $igv      = round($subtotal * 0.18, 2);
    $total    = round($subtotal + $igv, 2);

    // Función para convertir tildes y caracteres especiales
    $safe = static fn(string $s): string =>
        iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s) ?? $s;

    $pdf = new PdfReport();
    $pdf->title($safe('Factura ' . $invoice['number']));

    // ── Datos empresa ──
    $pdf->sectionHeader('tecnoSoluciones S.A.');
    $pdf->row('Factura N:', $invoice['number']);
    $pdf->row('Emision:',   date('d/m/Y', strtotime($invoice['issued_at'])));
    $pdf->row('Vence:',     $invoice['due_at']
                                ? date('d/m/Y', strtotime($invoice['due_at']))
                                : 'Sin vencimiento');
    $pdf->row('Estado:',    $invoice['status']);

    $pdf->spacer();
    $pdf->divider();
    $pdf->spacer();

    // ── Datos cliente ──
    $pdf->sectionHeader('Cliente');
    $pdf->row('Nombre:',   $safe($invoice['client_name']));
    $pdf->row('Empresa:',  $safe($invoice['client_company'] ?: 'Particular'));
    $pdf->row('Email:',    $invoice['client_email']);
    $pdf->row('Telefono:', $invoice['client_phone'] ?: 'No registrado');

    $pdf->spacer();
    $pdf->divider();
    $pdf->spacer();

    // ── Detalle del proyecto ──
    $pdf->sectionHeader('Detalle del servicio');
    $pdf->spacer();

 $pdf->table(
    ['Descripcion', 'Cant.', 'Precio unit.', 'Total'],
    [
        [
            $safe($invoice['project_name']),
            '1',
            '$' . number_format($subtotal, 2),
            '$' . number_format($subtotal, 2),
        ],
    ],
    -1  // ← sin badge en ninguna columna
);

    $pdf->spacer();
    $pdf->spacer();

    // ── Totales ──
    $pdf->summaryBox([
        ['Subtotal',  '$' . number_format($subtotal, 2)],
        ['IGV (18%)', '$' . number_format($igv, 2)],
        ['Total',     '$' . number_format($total, 2)],
    ]);

    if (!empty($invoice['notes'])) {
        $pdf->spacer();
        $pdf->sectionHeader('Notas');
        $pdf->text($safe($invoice['notes']));
    }

    $pdf->output('factura-' . $invoice['number'] . '.pdf');
}
    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->invoices->delete($id);
            $this->flash('success', 'Factura eliminada.');
        }

        $this->redirect('facturas');
    }
}