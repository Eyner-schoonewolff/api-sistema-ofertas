<?php

namespace App\Services;

use App\Models\Oferta;
use App\Models\Actividad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Servicio para exportar ofertas a Excel
 */
class ExcelExportService
{
    private Oferta $ofertaModel;
    private Actividad $actividadModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ofertaModel = new Oferta();
        $this->actividadModel = new Actividad();
    }

    /**
     * Genera y envía un archivo Excel con las ofertas
     * 
     * @param array $filters Filtros opcionales para las ofertas
     * @return void
     */
    public function export(array $filters = []): void
    {
        // Obtener todas las ofertas (sin paginación para exportación)
        $ofertas = $this->ofertaModel->find($filters, ['sort' => ['creado_en' => -1]]);
        
        // Enriquecer con datos de actividades
        foreach ($ofertas as &$oferta) {
            if (isset($oferta['actividad_id'])) {
                $actividad = $this->actividadModel->findById($oferta['actividad_id']);
                $oferta['actividad_nombre'] = $actividad ? $actividad['producto'] ?? '' : '';
            } else {
                $oferta['actividad_nombre'] = '';
            }
        }

        // Crear el spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ofertas');

        // Definir encabezados
        $headers = [
            'A' => 'Consecutivo',
            'B' => 'Objeto',
            'C' => 'Descripción',
            'D' => 'Moneda',
            'E' => 'Presupuesto',
            'F' => 'Actividad',
            'G' => 'Fecha Inicio',
            'H' => 'Hora Inicio',
            'I' => 'Fecha Cierre',
            'J' => 'Hora Cierre',
            'K' => 'Estado',
            'L' => 'Creado En'
        ];

        // Establecer encabezados
        $row = 1;
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $row, $header);
        }

        // Estilizar encabezados
        $headerRange = 'A1:L1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Llenar datos
        $row = 2;
        foreach ($ofertas as $oferta) {
            $sheet->setCellValue('A' . $row, $oferta['consecutivo'] ?? '');
            $sheet->setCellValue('B' . $row, $oferta['objeto'] ?? '');
            $sheet->setCellValue('C' . $row, $oferta['descripcion'] ?? '');
            $sheet->setCellValue('D' . $row, $oferta['moneda'] ?? '');
            $sheet->setCellValue('E' . $row, $oferta['presupuesto'] ?? 0);
            $sheet->setCellValue('F' . $row, $oferta['actividad_nombre'] ?? '');
            $sheet->setCellValue('G' . $row, $oferta['fecha_inicio'] ?? '');
            $sheet->setCellValue('H' . $row, $oferta['hora_inicio'] ?? '');
            $sheet->setCellValue('I' . $row, $oferta['fecha_cierre'] ?? '');
            $sheet->setCellValue('J' . $row, $oferta['hora_cierre'] ?? '');
            $sheet->setCellValue('K' . $row, $oferta['estado'] ?? '');
            
            // Formatear fecha de creación
            $creadoEn = '';
            if (isset($oferta['creado_en'])) {
                try {
                    $date = new \DateTime($oferta['creado_en']);
                    $creadoEn = $date->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $creadoEn = $oferta['creado_en'];
                }
            }
            $sheet->setCellValue('L' . $row, $creadoEn);
            
            $row++;
        }

        // Ajustar ancho de columnas
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Aplicar bordes a todas las celdas con datos
        $lastRow = $row - 1;
        if ($lastRow > 1) {
            $dataRange = 'A1:L' . $lastRow;
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        // Enviar el archivo
        $filename = 'ofertas_' . date('Y-m-d_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
