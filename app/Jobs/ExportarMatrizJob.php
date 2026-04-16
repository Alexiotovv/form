<?php

namespace App\Jobs;

use App\Models\ExportacionMatriz;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExportarMatrizJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 1800; // 30 minutos
    public $tries = 1;
    
    protected const MAX_RECORDS_EXCEL = 10000; // Límite para Excel
    
    public function __construct(
        public ExportacionMatriz $exportacion,
        public array $filtros
    ) {
        // Aumentar límites
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);
    }
    
    public function handle(): void
    {
        try {
            $this->updateProgress(5, 'Iniciando exportación...');
            
            // Ejecutar stored procedure
            $this->updateProgress(10, 'Consultando base de datos...');
            $resultados = $this->ejecutarProcedimiento();
            $total = count($resultados);
            
            $this->exportacion->update(['total_registros' => $total]);
            
            if ($total === 0) {
                $this->completeExport(null, 'No se encontraron registros');
                return;
            }
            
            // 🔹 DECIDIR: ¿Excel o CSV?
            if ($total > self::MAX_RECORDS_EXCEL) {
                $this->updateProgress(30, "Generando CSV con {$total} registros (formato optimizado)...");
                $rutaArchivo = $this->generarCSV($resultados, $total);
                $tipoArchivo = 'CSV';
            } else {
                $this->updateProgress(30, "Generando Excel con {$total} registros...");
                $rutaArchivo = $this->generarExcelOptimizado($resultados, $total);
                $tipoArchivo = 'Excel';
            }
            
            $this->completeExport($rutaArchivo, "Exportación {$tipoArchivo} completada exitosamente");
            
        } catch (\Throwable $e) {
            $this->handleError($e);
            throw $e;
        }
    }
    
    /**
     * Generar CSV - MUY eficiente en memoria
     */
    private function generarCSV(array $datos, int $total): string
    {
        $nombreArchivo = 'matriz_disponibilidad_' . now()->format('Ymd_His') . '.csv';
        $rutaRelativa = 'exports/matriz/' . $nombreArchivo;
        $path = Storage::disk('public')->path($rutaRelativa);
        
        // Asegurar directorio
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        // Abrir archivo
        $handle = fopen($path, 'w');
        
        // 🔹 BOM para Excel en español (UTF-8)
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        $headers = [
            'PRODUCTO', 'COD_SISMED', 'TIPO PROD', 'TIPO ABAST',
            'MES1', 'MES2', 'MES3', 'MES4', 'MES5', 'MES6',
            'MES7', 'MES8', 'MES9', 'MES10', 'MES11', 'MES12',
            'STOCK_FINAL', 'FECHA_VCMTO.', 'CONSUMO_TOTAL', 'CPMA',
            'CONSUMO_ÚLT.4MESES', 'MSD', 'PRECIO_UNIT', 'MONTO',
            'SIT.STOCK', 'MESES_PARA_VCMTO.', 'SIT.FECH_VCMTO.',
            'DIST.1', 'INGRESO_ICI', 'PEND.ING.ICI', 'DIST.2',
            'CONSUMO_PROYEC.', 'STOCK_PROYEC.', 'CPMA PROYEC.',
            'CONSUMO_4M_PROYEC.', 'MSD PROYEC.', 'SIT.STOCK_PROYEC', 'ENVÍO SUGERIDO'
        ];
        
        fputcsv($handle, $headers, ';'); // Usar ; como separador (Excel en español)
        
        // 🔹 Procesar en lotes para actualizar progreso
        $campos = [
            'descripcion_producto_alt', 'cod_sismed', 'tipo_prod', 'tipo_abastecimiento',
            'Mes1', 'Mes2', 'Mes3', 'Mes4', 'Mes5', 'Mes6', 'Mes7', 'Mes8', 'Mes9', 'Mes10', 'Mes11', 'Mes12',
            'StockFinal', 'fec_exp', 'consumo_total', 'cpma', 'consumo_ultimos_4meses', 'meses_prov',
            'precio', 'monto', 'situacion_stock', 'meses_para_vencimiento', 'sit_fecha_vcmto',
            'dist1', 'ingre', 'pendingre_ici', 'dist2', 'consumo_total_proyectado', 'stockfinal_proyectado',
            'cpma_proyectado', 'consumo_cuatro_ult_meses_proyectado', 'msd_proyectado',
            'situacion_stock_proyectado', 'envio_sugerido'
        ];
        
        $batchSize = 1000; // Actualizar cada 1000 registros
        
        foreach (array_chunk($datos, $batchSize) as $batchIndex => $batch) {
            foreach ($batch as $reg) {
                $fila = [];
                foreach ($campos as $campo) {
                    $valor = $reg->$campo ?? '';
                    // Formatear números
                    if (is_numeric($valor)) {
                        $fila[] = number_format($valor, 2, ',', '');
                    } else {
                        $fila[] = $valor;
                    }
                }
                fputcsv($handle, $fila, ';');
            }
            
            // Actualizar progreso
            $procesados = min(($batchIndex + 1) * $batchSize, $total);
            $progreso = min(95, 30 + ($procesados / $total * 70));
            
            $this->exportacion->update([
                'registros_procesados' => $procesados,
                'progreso' => (int)$progreso,
                'mensaje' => "Procesando: {$procesados} de {$total} registros..."
            ]);
            
            // 🔹 Liberar memoria
            if ($batchIndex % 10 === 0) {
                gc_collect_cycles();
            }
        }
        
        fclose($handle);
        
        // Liberar datos originales
        unset($datos);
        gc_collect_cycles();
        
        return $path;
    }
    
    /**
     * Generar Excel - Solo para datasets pequeños (< 10,000 registros)
     */
    private function generarExcelOptimizado(array $datos, int $total): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Encabezados (mismo código que tienes)
        $headers = [/* ... tus 38 encabezados ... */];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 9],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ]);
            $col++;
        }
        
        // Procesar datos
        $fila = 2;
        $campos = [/* ... tus campos ... */];
        
        foreach (array_chunk($datos, 500) as $batchIndex => $batch) {
            foreach ($batch as $reg) {
                $col = 'A';
                foreach ($campos as $campo) {
                    $valor = $reg->$campo ?? '';
                    $sheet->setCellValue($col . $fila, is_numeric($valor) ? (float)$valor : $valor);
                    $col++;
                }
                $fila++;
            }
            
            $procesados = min(($batchIndex + 1) * 500, $total);
            $progreso = min(95, 30 + ($procesados / $total * 70));
            
            $this->exportacion->update([
                'registros_procesados' => $procesados,
                'progreso' => (int)$progreso,
                'mensaje' => "Procesando: {$procesados} de {$total}"
            ]);
            
            if ($batchIndex % 5 === 0) {
                gc_collect_cycles();
            }
        }
        
        // Guardar
        $nombreArchivo = 'matriz_disponibilidad_' . now()->format('Ymd_His') . '.xlsx';
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);
        
        $tempPath = $tempDir . '/' . $nombreArchivo;
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);
        
        // Mover a storage
        $rutaRelativa = 'exports/matriz/' . $nombreArchivo;
        Storage::disk('public')->put($rutaRelativa, file_get_contents($tempPath));
        @unlink($tempPath);
        
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $datos);
        gc_collect_cycles();
        
        return Storage::disk('public')->path($rutaRelativa);
    }
    
    private function ejecutarProcedimiento(): array
    {
        $codigo_pre = $this->filtros['cod_ipress'] ?? null;
        $codigo_sismed = $this->filtros['cod_sismed'] ?? null;
        $fecha = $this->filtros['fin_mes'] ?? date('Y-m-t');
        
        if (empty($codigo_pre)) {
            return DB::select("CALL sp_obtener_registros_matriz_todos(NULL, ?, ?)", 
                [$codigo_sismed, $fecha]);
        }
        
        return DB::select("CALL sp_obtener_registros_matriz(?, ?, ?)", 
            [$codigo_pre, $codigo_sismed, $fecha]);
    }
    
    private function updateProgress(int $progreso, string $mensaje): void
    {
        $this->exportacion->update([
            'progreso' => $progreso,
            'mensaje' => $mensaje,
            'estado' => $progreso >= 10 ? 'procesando' : 'pendiente'
        ]);
    }
    
    private function completeExport(?string $ruta, string $mensaje): void
    {
        $this->exportacion->update([
            'estado' => 'completado',
            'progreso' => 100,
            'ruta_archivo' => $ruta,
            'nombre_archivo' => $ruta ? basename($ruta) : '',
            'mensaje' => $mensaje,
            'fecha_fin' => now()
        ]);
    }
    
    private function handleError(\Throwable $e): void
    {
        Log::error('ExportarMatrizJob fallido', [
            'exportacion_id' => $this->exportacion->id,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        $this->exportacion->update([
            'estado' => 'error',
            'mensaje' => 'Error: ' . $e->getMessage(),
            'fecha_fin' => now()
        ]);
    }
}





// namespace App\Jobs;

// use App\Models\ExportacionMatriz;
// use App\Models\User;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage;
// use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
// use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\Fill;

// class ExportarMatrizJob implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, SerializesModels;
    
//     protected $exportacion;
//     protected $filtros;
//     public $timeout = 3600; // 1 hora
//     public $tries = 1; // Solo 1 intento
    
//     public function __construct(ExportacionMatriz $exportacion, array $filtros)
//     {
//         $this->exportacion = $exportacion;
//         $this->filtros = $filtros;
//     }
    
//     public function handle()
//     {
//         try {
//             // Actualizar estado
//             $this->exportacion->update([
//                 'estado' => 'procesando',
//                 'fecha_inicio' => now(),
//                 'progreso' => 5,
//                 'mensaje' => 'Iniciando exportación...'
//             ]);
            
//             // Ejecutar procedimiento almacenado
//             $this->exportacion->update([
//                 'progreso' => 10,
//                 'mensaje' => 'Ejecutando consulta a la base de datos...'
//             ]);
            
//             $resultados = $this->ejecutarProcedimiento();
            
//             $totalRegistros = count($resultados);
//             $this->exportacion->update([
//                 'total_registros' => $totalRegistros,
//                 'progreso' => 30,
//                 'mensaje' => "Se encontraron {$totalRegistros} registros. Generando archivo Excel..."
//             ]);
            
//             if ($totalRegistros == 0) {
//                 $this->exportacion->update([
//                     'estado' => 'completado',
//                     'progreso' => 100,
//                     'mensaje' => 'No se encontraron registros para exportar',
//                     'fecha_fin' => now()
//                 ]);
//                 return;
//             }
            
//             // Generar Excel
//             $archivoPath = $this->generarExcel($resultados);
            
//             // Actualizar final
//             $this->exportacion->update([
//                 'estado' => 'completado',
//                 'progreso' => 100,
//                 'ruta_archivo' => $archivoPath,
//                 'nombre_archivo' => basename($archivoPath),
//                 'mensaje' => 'Exportación completada exitosamente',
//                 'fecha_fin' => now()
//             ]);
            
//         } catch (\Exception $e) {
//             Log::error('Error en ExportarMatrizJob: ' . $e->getMessage());
//             $this->exportacion->update([
//                 'estado' => 'error',
//                 'mensaje' => 'Error: ' . $e->getMessage(),
//                 'fecha_fin' => now()
//             ]);
//         }
//     }
    
//     private function ejecutarProcedimiento()
//     {
//         $codigo_pre = $this->filtros['cod_ipress'] ?? null;
//         $codigo_sismed = $this->filtros['cod_sismed'] ?? null;
//         $fecha = $this->filtros['fin_mes'] ?? date('Y-m-t');
        
//         // Si no hay filtro de IPRESS, ejecutar para todos
//         if (empty($codigo_pre)) {
//             $results = DB::select("CALL sp_obtener_registros_matriz_todos(NULL, ?, ?)", [$codigo_sismed, $fecha]);
//         } else {
//             $results = DB::select("CALL sp_obtener_registros_matriz(?, ?, ?)", [$codigo_pre, $codigo_sismed, $fecha]);
//         }
        
//         return collect($results);
//     }
    
//     private function generarExcel($datos)
//     {
//         $spreadsheet = new Spreadsheet();
//         $sheet = $spreadsheet->getActiveSheet();
        
//         // Definir encabezados (igual que tu tabla)
//         $headers = [
//             'PRODUCTO', 'COD_SISMED', 'TIPO PROD', 'TIPO ABAST', 
//             'MES1', 'MES2', 'MES3', 'MES4', 'MES5', 'MES6', 
//             'MES7', 'MES8', 'MES9', 'MES10', 'MES11', 'MES12',
//             'STOCK_FINAL', 'FECHA_VCMTO.', 'CONSUMO_TOTAL', 'CPMA',
//             'CONSUMO_ÚLT.4MESES', 'MSD', 'PRECIO_UNIT', 'MONTO',
//             'SIT.STOCK', 'MESES_PARA_VCMTO.', 'SIT.FECH_VCMTO.',
//             'DIST.1', 'INGRESO_ICI', 'PEND.ING.ICI', 'DIST.2',
//             'CONSUMO_PROYEC.', 'STOCK_PROYEC.', 'CPMA PROYEC.',
//             'CONSUMO_4M_PROYEC.', 'MSD PROYEC.', 'SIT.STOCK_PROYEC', 'ENVÍO SUGERIDO'
//         ];
        
//         // Aplicar estilos a encabezados
//         $columna = 'A';
//         foreach ($headers as $header) {
//             $sheet->setCellValue($columna . '1', $header);
//             $sheet->getStyle($columna . '1')->applyFromArray([
//                 'font' => ['bold' => true, 'size' => 10],
//                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
//                 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
//             ]);
//             $columna++;
//         }
        
//         // Llenar datos
//         $fila = 2;
//         $progresoPorFila = 30 + (70 / max(1, count($datos)));
        
//         foreach ($datos as $index => $reg) {
//             $col = 'A';
//             $sheet->setCellValue($col++ . $fila, $reg->descripcion_producto_alt ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->cod_sismed ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->tipo_prod ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->tipo_abastecimiento ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->Mes1 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes2 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes3 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes4 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes5 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes6 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes7 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes8 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes9 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes10 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes11 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->Mes12 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->StockFinal ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->fec_exp ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->consumo_total ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->cpma ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->consumo_ultimos_4meses ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->meses_prov ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->precio ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->monto ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->situacion_stock ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->meses_para_vencimiento ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->sit_fecha_vcmto ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->dist1 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->ingre ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->pendingre_ici ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->dist2 ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->consumo_total_proyectado ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->stockfinal_proyectado ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->cpma_proyectado ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->consumo_cuatro_ult_meses_proyectado ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->msd_proyectado ?? 0);
//             $sheet->setCellValue($col++ . $fila, $reg->situacion_stock_proyectado ?? '');
//             $sheet->setCellValue($col++ . $fila, $reg->envio_sugerido ?? 0);
            
//             $fila++;
            
//             // Actualizar progreso cada 100 filas
//             if ($index % 100 == 0) {
//                 $progresoActual = min(95, 30 + ($index / max(1, count($datos)) * 70));
//                 $this->exportacion->update([
//                     'progreso' => (int)$progresoActual,
//                     'registros_procesados' => $index,
//                     'mensaje' => "Procesando registro {$index} de " . count($datos)
//                 ]);
//             }
//         }
        
//         // Autoajustar columnas
//         foreach (range('A', $columna) as $col) {
//             $sheet->getColumnDimension($col)->setAutoSize(true);
//         }
        
//         // Guardar archivo
//         $timestamp = now()->format('Ymd_His');
//         $nombreArchivo = "matriz_disponibilidad_{$timestamp}.xlsx";
//         $rutaArchivo = "exports/matriz/{$nombreArchivo}";
        
//         $tempPath = storage_path("app/temp/{$nombreArchivo}");
//         $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
//         $writer->save($tempPath);
        
//         // Mover a storage
//         Storage::disk('public')->put($rutaArchivo, file_get_contents($tempPath));
//         unlink($tempPath);
        
//         return Storage::disk('public')->path($rutaArchivo);
//     }
// }