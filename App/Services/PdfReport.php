<?php

declare(strict_types=1);

namespace App\Services;

use function imagecreatefrompng;
use function imagealphablending;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function imagecolorat;
use function imagedestroy;

final class PdfReport
{
    /** @var array<int, string> */
    private array $objects = [];

    private string $content   = '';
    private string $graphics  = '';
    private int    $line      = 700;
    private int    $itemCount = 0;

    /** @var array<string, int> */
    private array $imageObjects = [];
    private int   $imageCounter = 0;

    private const PAGE_W    = 612;
    private const PAGE_H    = 792;
    private const MARGIN_L  = 50;
    private const MARGIN_R  = 562;
    private const COL_VALUE = 210;

    // Paleta de colores corporativa refinada
    private const C_HEADER_BG    = '0.07 0.14 0.38';
    private const C_HEADER_STRIP = '0.12 0.30 0.65';
    private const C_ACCENT       = '0.20 0.55 0.90';
    private const C_WHITE        = '1 1 1';
    private const C_WHITE_DIM    = '0.75 0.88 1.0';
    private const C_SECTION_BG   = '0.93 0.96 1.0';
    private const C_SECTION_BAR  = '0.10 0.25 0.60';
    private const C_ROW_ALT      = '0.96 0.97 0.99';
    private const C_LABEL        = '0.35 0.40 0.50';
    private const C_VALUE        = '0.08 0.10 0.16';
    private const C_DIVIDER      = '0.78 0.84 0.93';
    private const C_FOOTER_BG    = '0.07 0.14 0.38';
    private const C_FOOTER_TEXT  = '0.72 0.84 0.97';

    private const C_STATUS = [
        'Completado'  => ['bg' => '0.88 0.97 0.92', 'fg' => '0.05 0.50 0.25'],
        'Finalizado'  => ['bg' => '0.88 0.97 0.92', 'fg' => '0.05 0.50 0.25'],
        'En progreso' => ['bg' => '0.99 0.96 0.85', 'fg' => '0.55 0.42 0.05'],
        'En pausa'    => ['bg' => '0.95 0.90 0.98', 'fg' => '0.45 0.20 0.60'],
        'Planificado' => ['bg' => '0.90 0.93 0.98', 'fg' => '0.20 0.30 0.55'],
        'Cancelado'   => ['bg' => '0.98 0.90 0.90', 'fg' => '0.60 0.10 0.10'],
    ];

    public function title(string $text): void { $this->drawHeader($text); }

    public function text(string $text): void
    {
        $this->setColor(self::C_VALUE);
        $this->writeText('F1', $this->safe($text), 8, self::MARGIN_L, $this->line);
        $this->line -= 16;
    }

    public function spacer(): void { $this->line -= 8; }

    public function divider(): void
    {
        $this->graphics .= sprintf("%s RG\n0.3 w\n%d %d m %d %d l S\n",
            self::C_DIVIDER, self::MARGIN_L, $this->line + 3, self::MARGIN_R, $this->line + 3);
        $this->line -= 12;
    }

    public function sectionHeader(string $text): void
    {
        $y = $this->line - 2;
        $h = 17;
        // Fondo de sección
        $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n",
            self::C_SECTION_BG, self::MARGIN_L, $y, self::MARGIN_R - self::MARGIN_L, $h);
        // Barra lateral izquierda
        $this->graphics .= sprintf("%s rg\n%d %d 4 %d re f\n0 0 0 rg\n",
            self::C_SECTION_BAR, self::MARGIN_L, $y, $h);
        $this->setColor(self::C_SECTION_BAR);
        $this->writeText('F2', $this->safe($text), 8, self::MARGIN_L + 10, $this->line + 3);
        $this->setColor('0 0 0');
        $this->line -= 22;
        $this->itemCount = 0;
    }

    public function row(string $label, string $value): void
    {
        $y = $this->line - 2;
        $h = 15;
        if ($this->itemCount % 2 === 0) {
            $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n0 0 0 rg\n",
                self::C_ROW_ALT, self::MARGIN_L, $y, self::MARGIN_R - self::MARGIN_L, $h);
        }
        $this->setColor(self::C_LABEL);
        $this->writeText('F2', $this->safe($label), 7, self::MARGIN_L + 6, $this->line + 1);
        $this->setColor(self::C_VALUE);
        $this->writeText('F1', $this->safe($value), 8, self::COL_VALUE, $this->line + 1);
        $this->setColor('0 0 0');
        $this->line -= 16;
        $this->itemCount++;
    }

    public function table(array $columns, array $rows, int $statusCol = 1): void
    {
        if (empty($rows)) return;
        $w    = self::MARGIN_R - self::MARGIN_L;
        $x    = self::MARGIN_L;
        $colW = $this->calcColWidths($columns, $w);
        $rowH = 15;
        $hdrH = 17;

        // Cabecera de tabla
        $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n0 0 0 rg\n",
            self::C_HEADER_BG, $x, $this->line - $hdrH + 4, $w, $hdrH);
        $cx = $x + 5;
        foreach ($columns as $ci => $col) {
            $this->setColor(self::C_WHITE);
            $this->writeText('F2', $this->safe($col), 7, $cx, $this->line - 1);
            $cx += $colW[$ci];
        }
        $this->line -= $hdrH + 2;

        // Filas
        foreach ($rows as $ri => $row) {
            if ($ri % 2 === 0) {
                $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n0 0 0 rg\n",
                    self::C_ROW_ALT, $x, $this->line - $rowH + 4, $w, $rowH);
            }
            $cx = $x + 5;
            foreach ($row as $ci => $cell) {
                if ($statusCol >= 0 && $ci === $statusCol) {
                    $this->drawStatusBadge((string)$cell, $cx, $this->line - 1);
                } else {
                    $this->setColor(self::C_VALUE);
                    $this->writeText('F1', $this->safe((string)$cell), 7, $cx, $this->line);
                }
                $cx += $colW[$ci];
            }
            $this->graphics .= sprintf("%s RG\n0.2 w\n%d %d m %d %d l S\n",
                self::C_DIVIDER, $x, $this->line - $rowH + 4, $x + $w, $this->line - $rowH + 4);
            $this->line -= $rowH;
        }
        $this->setColor('0 0 0');
        $this->line -= 6;
    }

    public function summaryBox(array $items): void
    {
        $this->spacer();
        $w = self::MARGIN_R - self::MARGIN_L;
        $h = count($items) * 18 + 16;
        $y = $this->line - $h + 10;

        // Fondo
        $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n",
            self::C_HEADER_BG, self::MARGIN_L, $y, $w, $h);
        // Barra lateral izquierda en acento
        $this->graphics .= sprintf("%s rg\n%d %d 5 %d re f\n0 0 0 rg\n",
            self::C_ACCENT, self::MARGIN_L, $y, $h);

        $lineY = $this->line;
        foreach ($items as $item) {
            $this->setColor(self::C_WHITE_DIM);
            $this->writeText('F2', $this->safe($item[0]), 8, self::MARGIN_L + 14, $lineY);
            $this->setColor(self::C_WHITE);
            $this->writeText('F2', $this->safe($item[1]), 9, 400, $lineY);
            $lineY -= 18;
        }
        $this->setColor('0 0 0');
        $this->line = $lineY - 10;
    }

    public function progressBar(string $label, int $percent, string $detail = ''): void
    {
        $percent = max(0, min(100, $percent));
        $w       = self::MARGIN_R - self::MARGIN_L;
        $trackH  = 20;

        // Lógica de color: verde=terminando, amarillo=a mitad, azul=inicio
        if ($percent >= 80) {
            $fillColor = '0.05 0.50 0.25'; // Verde oscuro: casi terminado
            $labelPct  = '0.05 0.50 0.25';
        } elseif ($percent >= 50) {
            $fillColor = '0.85 0.45 0.05'; // Naranja: a mitad
            $labelPct  = '0.85 0.45 0.05';
        } else {
            $fillColor = '0.12 0.30 0.65'; // Azul corporativo: inicio
            $labelPct  = '0.12 0.30 0.65';
        }

        $boxY = $this->line - $trackH - 26;
        $boxH = $trackH + 40;
        // Fondo del contenedor
        $this->graphics .= sprintf("0.93 0.96 1.0 rg\n%d %d %d %d re f\n0 0 0 rg\n",
            self::MARGIN_L, $boxY, $w, $boxH);

        // Etiqueta y porcentaje
        $this->setColor(self::C_SECTION_BAR);
        $this->writeText('F2', $this->safe($label), 8, self::MARGIN_L + 10, $this->line - 4);
        $this->setColor($labelPct);
        $this->writeText('F2', $this->safe($percent . '%'), 10, self::MARGIN_R - 38, $this->line - 4);

        $this->line -= 20;
        $trackY = $this->line - $trackH;

        // Track de fondo (gris claro)
        $this->graphics .= sprintf("0.80 0.85 0.93 rg\n%d %d %d %d re f\n0 0 0 rg\n",
            self::MARGIN_L + 10, $trackY, $w - 50, $trackH);

        // Fill de progreso
        if ($percent > 0) {
            $realFillW = (int)(($w - 50) * $percent / 100);
            $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n0 0 0 rg\n",
                $fillColor, self::MARGIN_L + 10, $trackY, $realFillW, $trackH);
        }

        if ($detail !== '') {
            $this->line -= $trackH + 8;
            $this->setColor(self::C_LABEL);
            $this->writeText('F1', $this->safe($detail), 7, self::MARGIN_L + 10, $this->line);
        }

        $this->line -= 22;
    }

    public function barChart(array $projects): void
    {
        if (empty($projects)) return;

        $this->sectionHeader('Presupuesto por proyecto');

        $budgets   = array_map(fn($p) => (float)($p['budget'] ?? 0), $projects);
        $maxBudget = !empty($budgets) ? max($budgets) : 0;
        if ($maxBudget <= 0) $maxBudget = 1;

        $chartX = self::MARGIN_L;
        $chartW = self::MARGIN_R - self::MARGIN_L;
        $chartH = 90;
        $baseY  = $this->line - $chartH;
        $topY   = $this->line;

        // Fondo del gráfico
        $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n0 0 0 rg\n",
            '0.96 0.97 0.99', $chartX, $baseY, $chartW, $chartH);
        // Línea base
        $this->graphics .= sprintf("%s RG\n0.5 w\n%d %d m %d %d l S\n",
            self::C_DIVIDER, $chartX, $baseY, self::MARGIN_R, $baseY);

        // Líneas de guía
        foreach ([0.33, 0.66] as $ratio) {
            $gy = $baseY + (int)($chartH * $ratio);
            $this->graphics .= sprintf("%s RG\n0.3 w\n[2 2] 0 d\n%d %d m %d %d l S\n[] 0 d\n",
                self::C_DIVIDER, $chartX + 32, $gy, self::MARGIN_R - 5, $gy);
            $this->setColor(self::C_LABEL);
            $this->writeText('F1', $this->safe('$' . number_format($maxBudget * $ratio, 0)), 5, $chartX + 2, $gy - 2);
        }

        $this->setColor(self::C_LABEL);
        $this->writeText('F1', $this->safe('$' . number_format($maxBudget, 0)), 5, $chartX + 2, $topY - 4);

        $n      = count($projects);
        $barW   = min(50, (int)(($chartW - 40) / $n) - 8);
        $gap    = (int)(($chartW - 30 - $barW * $n) / ($n + 1));
        $colors = ['0.12 0.30 0.65', '0.13 0.79 0.52', '0.96 0.62 0.04', '0.16 0.60 0.80', '0.75 0.20 0.20'];

        foreach ($projects as $i => $project) {
            $budget = $budgets[$i];
            $barH   = (int)(($budget / $maxBudget) * ($chartH - 15));
            $bx     = $chartX + 30 + $gap + $i * ($barW + $gap);
            $color  = $colors[$i % count($colors)];

            $this->graphics .= sprintf("%s rg\n%d %d %d %d re f\n0 0 0 rg\n", $color, $bx, $baseY, $barW, $barH);
            $this->setColor($color);
            $this->writeText('F2', $this->safe('$' . number_format($budget, 0)), 5, $bx + 2, $baseY + $barH + 3);

            $name = mb_strlen($project['name']) > 14 ? mb_substr($project['name'], 0, 13) . '.' : $project['name'];
            $this->setColor(self::C_LABEL);
            $this->writeText('F1', $this->safe($name), 5, $bx, $baseY - 10);
        }

        $this->setColor('0 0 0');
        $this->line = $baseY - 22;
    }

    /**
     * Firma con espacio generoso antes de ella para que quede al final del documento,
     * bien separada del contenido anterior.
     */
    public function signature(
        string $imagePath,
        string $name = '',
        string $role = '',
        int    $imgW = 130,
        int    $imgH = 45
    ): void {
        // Espacio generoso antes de la firma
        $this->line -= 40;

        // Línea separadora sutil antes de la zona de firma
        $sepX = (int)(self::PAGE_W / 2) - 90;
        $this->graphics .= sprintf("%s RG\n0.4 w\n[4 3] 0 d\n%d %d m %d %d l S\n[] 0 d\n",
            self::C_DIVIDER, self::MARGIN_L, $this->line + 10, self::MARGIN_R, $this->line + 10);

        $this->line -= 10;

        $centerX = (int)(self::PAGE_W / 2);
        $imgX    = $centerX - (int)($imgW / 2);
        $imgY    = $this->line - $imgH;

        // Imagen de firma
        $xName = $this->embedPng($imagePath);
        if ($xName !== null) {
            $this->graphics .= sprintf("q\n%d 0 0 %d %d %d cm\n/%s Do\nQ\n",
                $imgW, $imgH, $imgX, $imgY, $xName);
        }

        $y  = $imgY - 8;
        $lw = 180;
        $lx = $centerX - (int)($lw / 2);

        // Línea bajo la firma
        $this->graphics .= sprintf("%s RG\n0.6 w\n%d %d m %d %d l S\n",
            self::C_SECTION_BAR, $lx, $y, $lx + $lw, $y);

        $y -= 14;

        if ($name !== '') {
            $nx = $centerX - (int)(strlen($name) * 5 / 2);
            $this->setColor(self::C_VALUE);
            $this->writeText('F2', $this->safe($name), 9, $nx, $y);
            $y -= 14;
        }

        if ($role !== '') {
            $rx = $centerX - (int)(strlen($role) * 4 / 2);
            $this->setColor(self::C_LABEL);
            $this->writeText('F1', $this->safe($role), 7, $rx, $y);
            $y -= 12;
        }

        $this->setColor('0 0 0');
        $this->line = $y - 6;
    }

    private function embedPng(string $path): ?string
    {
        if (!is_readable($path)) return null;

        $img = @imagecreatefrompng($path);
        if ($img === false) return null;

        imagealphablending($img, false);
        imagesavealpha($img, true);

        $w = imagesx($img);
        $h = imagesy($img);

        $rgb = $alpha = '';
        for ($row = 0; $row < $h; $row++) {
            for ($col = 0; $col < $w; $col++) {
                $c    = imagecolorat($img, $col, $row);
                $rgb .= chr(($c >> 16) & 0xFF) . chr(($c >> 8) & 0xFF) . chr($c & 0xFF);
                $gdAlpha  = ($c >> 24) & 0x7F;
                $alpha   .= chr(255 - (int)round($gdAlpha * 255 / 127));
            }
        }
        imagedestroy($img);

        $xName    = 'Im' . $this->imageCounter++;
        $sMaskNum = count($this->objects) + 1;
        $this->objects[$sMaskNum] = sprintf(
            "<< /Type /XObject /Subtype /Image /Width %d /Height %d\n" .
            "   /ColorSpace /DeviceGray /BitsPerComponent 8 /Length %d >>\nstream\n%s\nendstream",
            $w, $h, strlen($alpha), $alpha
        );

        $imgNum = count($this->objects) + 1;
        $this->objects[$imgNum] = sprintf(
            "<< /Type /XObject /Subtype /Image /Width %d /Height %d\n" .
            "   /ColorSpace /DeviceRGB /BitsPerComponent 8 /SMask %d 0 R /Length %d >>\nstream\n%s\nendstream",
            $w, $h, $sMaskNum, strlen($rgb), $rgb
        );

        $this->imageObjects[$xName] = $imgNum;
        return $xName;
    }

    public function output(string $filename): never
    {
        $this->drawFooter();

        $next       = count($this->objects) + 1;
        $catalogNum = $next;
        $pagesNum   = $next + 1;
        $pageNum    = $next + 2;
        $f1Num      = $next + 3;
        $f2Num      = $next + 4;
        $contNum    = $next + 5;

        $xobjDict = '';
        foreach ($this->imageObjects as $name => $objNum) {
            $xobjDict .= ' /' . $name . ' ' . $objNum . ' 0 R';
        }
        $xobjStr = !empty($xobjDict) ? ' /XObject <<' . $xobjDict . ' >>' : '';

        $this->objects[$catalogNum] = '<< /Type /Catalog /Pages ' . $pagesNum . ' 0 R >>';
        $this->objects[$pagesNum]   = '<< /Type /Pages /Kids [' . $pageNum . ' 0 R] /Count 1 >>';
        $this->objects[$pageNum]    = '<< /Type /Page /Parent ' . $pagesNum . ' 0 R /MediaBox [0 0 ' . self::PAGE_W . ' ' . self::PAGE_H . '] /Resources << /Font << /F1 ' . $f1Num . ' 0 R /F2 ' . $f2Num . ' 0 R >>' . $xobjStr . ' >> /Contents ' . $contNum . ' 0 R >>';
        $this->objects[$f1Num]      = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $this->objects[$f2Num]      = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $stream = $this->graphics . "BT\n" . $this->content . "ET";
        $this->objects[$contNum] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";

        $pdf     = "%PDF-1.4\n";
        $offsets = [];
        foreach ($this->objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref  = strlen($pdf);
        $count = count($this->objects);
        $pdf  .= "xref\n0 " . ($count + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . ($count + 1) . " /Root " . $catalogNum . " 0 R >>\n";
        $pdf .= "startxref\n" . $xref . "\n%%EOF";

        while (ob_get_level() > 0) { ob_end_clean(); }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function drawStatusBadge(string $status, int $x, int $y): void
    {
        $colors = self::C_STATUS[$status] ?? ['bg' => '0.92 0.92 0.92', 'fg' => '0.30 0.30 0.30'];
        $w = strlen($status) * 4 + 10;
        $this->graphics .= sprintf("%s rg\n%d %d %d 11 re f\n0 0 0 rg\n", $colors['bg'], $x, $y - 2, $w);
        $this->setColor($colors['fg']);
        $this->writeText('F2', $this->safe($status), 6, $x + 5, $y + 5);
        $this->setColor('0 0 0');
    }

    private function calcColWidths(array $columns, int $totalW): array
    {
        $n = count($columns);
        return match($n) {
            2 => [intval($totalW * 0.60), intval($totalW * 0.40)],
            3 => [intval($totalW * 0.45), intval($totalW * 0.28), intval($totalW * 0.27)],
            4 => [intval($totalW * 0.35), intval($totalW * 0.22), intval($totalW * 0.22), intval($totalW * 0.21)],
            5 => [intval($totalW * 0.30), intval($totalW * 0.18), intval($totalW * 0.17), intval($totalW * 0.17), intval($totalW * 0.18)],
            default => array_fill(0, $n, intval($totalW / $n)),
        };
    }

    private function drawHeader(string $title): void
    {
        // Fondo principal del header (más alto para más presencia)
        $this->graphics .= self::C_HEADER_BG    . " rg\n0 " . (self::PAGE_H - 55) . " " . self::PAGE_W . " 55 re f\n";
        // Franja decorativa media
        $this->graphics .= self::C_HEADER_STRIP . " rg\n0 " . (self::PAGE_H - 58) . " " . self::PAGE_W . " 4 re f\n";
        // Línea de acento inferior
        $this->graphics .= self::C_ACCENT       . " rg\n0 " . (self::PAGE_H - 61) . " " . self::PAGE_W . " 3 re f\n";
        // Barra vertical decorativa derecha
        $this->graphics .= self::C_ACCENT       . " rg\n"   . (self::PAGE_W - 32) . " " . (self::PAGE_H - 55) . " 5 55 re f\n";
        $this->graphics .= "0 0 0 rg\n";

        // Nombre de la empresa (grande)
        $this->setColor(self::C_WHITE);
        $this->writeText('F2', $this->safe(APP_NAME), 14, self::MARGIN_L, self::PAGE_H - 26);
        // Subtítulo / tipo de documento
        $this->setColor(self::C_WHITE_DIM);
        $this->writeText('F1', $this->safe($title), 8, self::MARGIN_L, self::PAGE_H - 42);
        // Fecha a la derecha
        $this->writeText('F1', 'Fecha: ' . date('d/m/Y'), 8, 440, self::PAGE_H - 26);
        $this->setColor('0 0 0');

        // Línea divisora sutil debajo del header
        $this->graphics .= self::C_ACCENT . " RG\n0.5 w\n" . self::MARGIN_L . " " . (self::PAGE_H - 68) . " m " . self::MARGIN_R . " " . (self::PAGE_H - 68) . " l S\n";

        $this->line = self::PAGE_H - 82;
    }

    private function drawFooter(): void
    {
        // Franja superior del footer
        $this->graphics .= self::C_ACCENT    . " rg\n0 28 " . self::PAGE_W . " 2 re f\n";
        // Fondo del footer
        $this->graphics .= self::C_FOOTER_BG . " rg\n0 0 " . self::PAGE_W . " 28 re f\n";
        $this->graphics .= "0 0 0 rg\n";
        $this->setColor(self::C_FOOTER_TEXT);
        $this->writeText('F1', 'Generado el ' . date('d/m/Y H:i') . '  |  ' . $this->safe(APP_NAME), 7, self::MARGIN_L, 10);
        $this->writeText('F1', 'Documento confidencial', 7, 445, 10);
        $this->setColor('0 0 0');
    }

    private function setColor(string $rgb): void { $this->content .= $rgb . " rg\n"; }

    private function writeText(string $font, string $safe, int $size, int $x, int $y): void
    {
        $this->content .= sprintf("/%s %d Tf 1 0 0 1 %d %d Tm (%s) Tj\n", $font, $size, $x, $y, $safe);
    }

    /**
     * Convierte texto UTF-8 a string seguro para PDF con WinAnsiEncoding.
     * Maneja correctamente todos los caracteres con tilde y especiales del español.
     */
    private function safe(string $text): string
    {
        // Mapa completo de caracteres UTF-8 → WinAnsi (ISO-8859-1 extendido)
        $map = [
            // Minúsculas con tilde
            'á' => "\xe1", 'é' => "\xe9", 'í' => "\xed",
            'ó' => "\xf3", 'ú' => "\xfa", 'ñ' => "\xf1",
            'ü' => "\xfc", 'à' => "\xe0", 'è' => "\xe8",
            'ì' => "\xec", 'ò' => "\xf2", 'ù' => "\xf9",
            'â' => "\xe2", 'ê' => "\xea", 'î' => "\xee",
            'ô' => "\xf4", 'û' => "\xfb",
            // Mayúsculas con tilde
            'Á' => "\xc1", 'É' => "\xc9", 'Í' => "\xcd",
            'Ó' => "\xd3", 'Ú' => "\xda", 'Ñ' => "\xd1",
            'Ü' => "\xdc", 'À' => "\xc0", 'È' => "\xc8",
            'Ì' => "\xcc", 'Ò' => "\xd2", 'Ù' => "\xd9",
            'Â' => "\xc2", 'Ê' => "\xca", 'Î' => "\xce",
            'Ô' => "\xd4", 'Û' => "\xdb",
            // Puntuación especial
            '¿' => "\xbf", '¡' => "\xa1",
            // Símbolos de moneda y otros
            '€' => "\x80", '°' => "\xb0",
        ];

        $text = str_replace(array_keys($map), array_values($map), $text);

        // Convertir lo que quede (cualquier char multibyte no mapeado) con TRANSLIT
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        if ($converted === false) {
            $converted = preg_replace('/[^\x20-\x7E\x80-\xFF]/', '?', $text) ?? $text;
        }

        // Escapar caracteres especiales de PDF
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
    }
}