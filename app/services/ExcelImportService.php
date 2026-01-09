<?php
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelImportService{
    private $colegiadoRepository;
    private $errores = [];
    private $advertencias = [];
    private $importados = 0;
    private $omitidos = 0;

    public function __construct(){
        $this->colegiadoRepository = new ColegiadoRepository();
    }

    // procesa un archivo Excel y importa los colegiados
    public function importarColegiados($archivo) {
        // Reiniciar contadores
        $this->errores = [];
        $this->advertencias = [];
        $this->importados = 0;
        $this->omitidos = 0;
        
        // Validar archivo
        $validacion = $this->validarArchivo($archivo);
        if (!$validacion['success']) {
            return $validacion;
        }
        
        try {
            // Cargar el archivo Excel
            $rutaArchivo = $archivo['tmp_name'];
            $spreadsheet = IOFactory::load($rutaArchivo);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Obtener todas las filas
            $filas = $worksheet->toArray();
            
            // Validar que tenga datos
            if (count($filas) < 2) {
                return [
                    'success' => false,
                    'message' => 'El archivo no contiene datos para importar'
                ];
            }
            
            // La primera fila son los encabezados
            $encabezados = array_map('trim', $filas[0]);
            
            // Mapear encabezados a nombres de campos
            $mapeo = $this->mapearEncabezados($encabezados);
            
            if (empty($mapeo)) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron identificar las columnas requeridas en el archivo'
                ];
            }
            
            // Procesar cada fila de datos (desde la segunda fila en adelante)
            for ($i = 1; $i < count($filas); $i++) {
                $fila = $filas[$i];
                $numeroFila = $i + 1;
                
                // Extraer datos según el mapeo
                $datos = $this->extraerDatosFila($fila, $mapeo);
                
                // Validar si la fila tiene datos
                if ($this->filaVacia($datos)) {
                    continue;
                }
                
                // Procesar el registro
                $this->procesarRegistro($datos, $numeroFila);
            }
            
            logMessage("Importación Excel completada: {$this->importados} importados, {$this->omitidos} omitidos", 'info');
            
            return [
                'success' => true,
                'importados' => $this->importados,
                'omitidos' => $this->omitidos,
                'errores' => $this->errores,
                'advertencias' => $this->advertencias
            ];
            
        } catch (Exception $e) {
            logMessage("Error en importación Excel: " . $e->getMessage(), 'error');
            return [
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ];
        }
    }

    // valida el archivo subido
    private function validarArchivo($archivo) {
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Error al subir el archivo'
            ];
        }
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return [
                'success' => false,
                'message' => 'Solo se permiten archivos Excel (.xlsx o .xls)'
            ];
        }
        
        $tamanoMaximo = 10 * 1024 * 1024; // 10MB
        if ($archivo['size'] > $tamanoMaximo) {
            return [
                'success' => false,
                'message' => 'El archivo es demasiado grande. Máximo 10MB'
            ];
        }
        
        return ['success' => true];
    }

    // mapea los encabezados del Excel a nombres de campos
    private function mapearEncabezados($encabezados) {
        $mapeo = [];
        
        // Posibles variaciones de nombres de columnas
        $variaciones = [
            'numero_colegiatura' => ['numero de colegiatura', 'n° colegiatura', 'numero colegiatura', 'colegiatura', 'nro colegiatura'],
            'dni' => ['dni', 'documento', 'numero de documento'],
            'nombres' => ['nombres', 'nombre'],
            'apellido_paterno' => ['apellido paterno', 'ape paterno', 'primer apellido'],
            'apellido_materno' => ['apellido materno', 'ape materno', 'segundo apellido'],
            'fecha_colegiatura' => ['fecha de colegiatura', 'fecha colegiatura', 'fecha ingreso'],
            'telefono' => ['telefono', 'teléfono', 'celular', 'tel'],
            'correo' => ['correo', 'email', 'correo electronico', 'e-mail'],
            'direccion' => ['direccion', 'dirección', 'domicilio'],
            'fecha_nacimiento' => ['fecha de nacimiento', 'fecha nacimiento', 'f. nacimiento', 'nacimiento'],
            'observaciones' => ['observaciones', 'obs', 'notas', 'comentarios']
        ];
        
        foreach ($encabezados as $indice => $encabezado) {
            $encabezadoNormalizado = strtolower(trim($encabezado));
            
            foreach ($variaciones as $campo => $posibles) {
                if (in_array($encabezadoNormalizado, $posibles)) {
                    $mapeo[$campo] = $indice;
                    break;
                }
            }
        }
        
        // Verificar campos obligatorios
        $obligatorios = ['dni', 'nombres', 'apellido_paterno', 'apellido_materno', 'fecha_colegiatura'];
        foreach ($obligatorios as $campo) {
            if (!isset($mapeo[$campo])) {
                return [];
            }
        }
        
        return $mapeo;
    }

    // extrae los datos de una fila según el mapeo
    private function extraerDatosFila($fila, $mapeo) {
        $datos = [];
        
        foreach ($mapeo as $campo => $indice) {
            $valor = isset($fila[$indice]) ? trim($fila[$indice]) : '';
            
            // Convertir fechas de Excel a formato MySQL
            if (in_array($campo, ['fecha_colegiatura', 'fecha_nacimiento']) && !empty($valor)) {
                $valor = $this->convertirFechaExcel($valor);
            }
            
            $datos[$campo] = $valor;
        }
        
        return $datos;
    }

    // convierte fechas de Excel a formato MySQL (Y-m-d)
    private function convertirFechaExcel($valor) {
        // Si ya está en formato Y-m-d, Y/m/d o d/m/Y
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return $valor;
        }
        
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $valor)) {
            return str_replace('/', '-', $valor);
        }
        
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor)) {
            $partes = explode('/', $valor);
            return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }
        
        // Si es número serial de Excel
        if (is_numeric($valor)) {
            $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor);
            return $fecha->format('Y-m-d');
        }
        
        // Intentar parsear con strtotime
        $timestamp = strtotime($valor);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return '';
    }

    // verifica si una fila está vacía
    private function filaVacia($datos) {
        foreach ($datos as $valor) {
            if (!empty($valor)) {
                return false;
            }
        }
        return true;
    }

    // procesa un registro individual
    private function procesarRegistro($datos, $numeroFila) {
        // Si no viene número de colegiatura, generar uno automáticamente
        if (empty($datos['numero_colegiatura'])) {
            $datos['numero_colegiatura'] = $this->colegiadoRepository->generarNumeroColegiatura();
        }

        // Validar datos básicos
        $erroresFila = $this->validarDatosFila($datos, $numeroFila);
        
        if (!empty($erroresFila)) {
            $this->errores[] = "Fila $numeroFila: " . implode(', ', $erroresFila);
            $this->omitidos++;
            return;
        }
        
        // Verificar si ya existe por DNI
        if ($this->colegiadoRepository->existeDni($datos['dni'])) {
            $this->advertencias[] = "Fila $numeroFila: DNI {$datos['dni']} ya existe, registro omitido";
            $this->omitidos++;
            return;
        }
        
        // Verificar si ya existe por número de colegiatura
        if ($this->colegiadoRepository->existeNumeroColegiatura($datos['numero_colegiatura'])) {
            $this->advertencias[] = "Fila $numeroFila: Número de colegiatura {$datos['numero_colegiatura']} ya existe, registro omitido";
            $this->omitidos++;
            return;
        }
        
        // Preparar datos para inserción
        $datosInsert = [
            'numero_colegiatura' => $datos['numero_colegiatura'],
            'dni' => $datos['dni'],
            'nombres' => $datos['nombres'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'],
            'fecha_colegiatura' => $datos['fecha_colegiatura'],
            'telefono' => $datos['telefono'] ?? null,
            'correo' => $datos['correo'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'estado' => 'inhabilitado',
            'observaciones' => $datos['observaciones'] ?? null
        ];
        
        // Insertar en base de datos
        try {
            $id = $this->colegiadoRepository->create($datosInsert);
            if ($id) {
                $this->importados++;
            } else {
                $this->errores[] = "Fila $numeroFila: Error al insertar en base de datos";
                $this->omitidos++;
            }
        } catch (Exception $e) {
            $this->errores[] = "Fila $numeroFila: " . $e->getMessage();
            $this->omitidos++;
        }
    }

    // valida los datos de una fila
    private function validarDatosFila($datos, $numeroFila) {
        $errores = [];
        
        //if (empty($datos['numero_colegiatura'])) {
        //    $errores[] = 'Número de colegiatura vacío';
        //}
        
        if (empty($datos['dni'])) {
            $errores[] = 'DNI vacío';
        } elseif (strlen($datos['dni']) != 8 || !is_numeric($datos['dni'])) {
            $errores[] = 'DNI debe tener 8 dígitos';
        }
        
        if (empty($datos['nombres'])) {
            $errores[] = 'Nombres vacío';
        }
        
        if (empty($datos['apellido_paterno'])) {
            $errores[] = 'Apellido paterno vacío';
        }
        
        if (empty($datos['apellido_materno'])) {
            $errores[] = 'Apellido materno vacío';
        }
        
        if (empty($datos['fecha_colegiatura'])) {
            $errores[] = 'Fecha de colegiatura vacía';
        }
        
        if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Correo electrónico inválido';
        }
        
        return $errores;
    }

    // genera un archivo Excel de plantilla para descargar
    public function generarPlantilla() {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Encabezados
        $encabezados = [
            'Numero de Colegiatura (Opcional)',
            'DNI',
            'Nombres',
            'Apellido Paterno',
            'Apellido Materno',
            'Fecha de Colegiatura',
            'Telefono',
            'Correo',
            'Direccion',
            'Fecha de Nacimiento',
            'Observaciones'
        ];
        
        // Establecer encabezados en la primera fila
        $columna = 'A';
        foreach ($encabezados as $encabezado) {
            $worksheet->setCellValue($columna . '1', $encabezado);
            $worksheet->getStyle($columna . '1')->getFont()->setBold(true);
            $worksheet->getColumnDimension($columna)->setAutoSize(true);
            $columna++;
        }
        
        // Agregar algunas filas de ejemplo
        $ejemplos = [
            ['12345', '12345678', 'Juan Carlos', 'Pérez', 'García', '2020-01-15', '987654321', 'juan@email.com', 'Av. Principal 123', '1990-05-20', ''],
            ['', '87654321', 'María Elena', 'López', 'Ramírez', '2021-03-10', '912345678', 'maria@email.com', 'Jr. Secundaria 456', '1985-08-15', 'Se generará automáticamente']
        ];
        
        $fila = 2;
        foreach ($ejemplos as $ejemplo) {
            $columna = 'A';
            foreach ($ejemplo as $valor) {
                $worksheet->setCellValue($columna . $fila, $valor);
                $columna++;
            }
            $fila++;
        }
        
        // Crear el archivo
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        // Configurar headers para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="plantilla_colegiados.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}