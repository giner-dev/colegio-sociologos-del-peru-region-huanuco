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

    // Contadores para el resumen
    private $numPropios = 0;
    private $numAutomaticos = 0;

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
        $this->numPropios = 0;
        $this->numAutomaticos = 0;
        
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
                    'message' => 'No se pudieron identificar las columnas requeridas (DNI, Nombres, Apellidos, etc.)'
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

            if ($this->numPropios > 0) {
                $this->advertencias[] = "Se utilizaron {$this->numPropios} números de colegiatura propios del Excel.";
            }
            if ($this->numAutomaticos > 0) {
                $this->advertencias[] = "Se generaron {$this->numAutomaticos} números de colegiatura automáticamente.";
            }
            
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
        
        $variaciones = [
            'numero_colegiatura' => ['numero de colegiatura', 'n° colegiatura', 'numero colegiatura', 'colegiatura', 'nro colegiatura', 'num colegiatura'],
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
            // 1. Convertir a minúsculas
            $normalized = mb_strtolower(trim($encabezado), 'UTF-8');

            // 2. Eliminar contenido entre paréntesis
            $normalized = preg_replace('/\s*\(.*?\)\s*/', '', $normalized);

            // 3. Normalizar símbolos comunes (n°, nro. -> numero)
            $normalized = str_replace(['n°', 'nro.', 'nro', 'num.'], 'numero', $normalized);
            $normalized = trim($normalized);

            foreach ($variaciones as $campo => $posibles) {
                if (in_array($normalized, $posibles)) {
                    $mapeo[$campo] = $indice;
                    break;
                }
            }
        }

        // Verificar campos obligatorios
        $obligatorios = ['dni', 'nombres', 'apellido_paterno', 'apellido_materno', 'fecha_colegiatura'];
        foreach ($obligatorios as $campo) {
            if (!isset($mapeo[$campo])) {
                return []; // O podrías lanzar una excepción indicando qué columna falta
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
        
        // Normalización robusta de ceros a la izquierda
        $numeroExcel = null;
        if (isset($datos['numero_colegiatura']) && trim($datos['numero_colegiatura']) !== '') {
            // Quitamos espacios y luego quitamos ceros a la izquierda
            $numeroExcel = ltrim(trim($datos['numero_colegiatura']), '0');
            
            // Si el número era "000", ltrim lo deja vacío, en ese caso lo tratamos como "0"
            if ($numeroExcel === '') {
                $numeroExcel = '0';
            }
        }
    
        // PASO 2: Validar datos básicos
        $erroresFila = $this->validarDatosFila($datos, $numeroFila);
        if (!empty($erroresFila)) {
            $this->errores[] = "Fila $numeroFila: " . implode(', ', $erroresFila);
            $this->omitidos++;
            return;
        }
    
        // PASO 3: Verificar si ya existe por DNI
        $colegiadoExistente = $this->colegiadoRepository->findByDni($datos['dni']);
        if ($colegiadoExistente) {
            // Actualizamos enviando el número del excel procesado
            $datos['numero_colegiatura'] = $numeroExcel; 
            $this->actualizarColegiadoExistente($colegiadoExistente, $datos, $numeroFila);
            return;
        }
    
        // --- CAMBIO 2: Lógica de asignación de número ---
        $numeroAUsar = null;
    
        // Solo generamos automático si REALMENTE no hay nada en el Excel
        if ($numeroExcel !== null && $numeroExcel !== '') {
            // Verificar si el número ya existe en otro colegiado
            $colegiadoPorNumero = $this->colegiadoRepository->findByNumeroColegiatura($numeroExcel);
        
            if ($colegiadoPorNumero) {
                $this->errores[] = "Fila $numeroFila: El número $numeroExcel ya está asignado al DNI {$colegiadoPorNumero->dni}";
                $this->omitidos++;
                return;
            }
            $numeroAUsar = $numeroExcel;
            $this->numPropios++;
        } else {
            // Generar automático solo si el campo está vacío en el Excel
            $numeroAUsar = $this->colegiadoRepository->generarNumeroColegiatura();
            $this->numAutomaticos++;
        }
    
        // PASO 5: Preparar e Insertar
        $datosInsert = [
            'numero_colegiatura' => $numeroAUsar,
            'dni'                => $datos['dni'],
            'nombres'            => $datos['nombres'],
            'apellido_paterno'   => $datos['apellido_paterno'],
            'apellido_materno'   => $datos['apellido_materno'],
            'fecha_colegiatura'  => $datos['fecha_colegiatura'],
            'telefono'           => $datos['telefono'] ?? null,
            'correo'             => $datos['correo'] ?? null,
            'direccion'          => $datos['direccion'] ?? null,
            'fecha_nacimiento'   => $datos['fecha_nacimiento'] ?? null,
            'estado'             => 'inhabilitado',
            'observaciones'      => $datos['observaciones'] ?? null
        ];
    
        try {
            $id = $this->colegiadoRepository->create($datosInsert);
            if ($id) {
                $this->importados++;
            }
        } catch (Exception $e) {
            $this->errores[] = "Fila $numeroFila: " . $e->getMessage();
            $this->omitidos++;
        }
    }

    // MÉTODO PARA ACTUALIZAR REGISTROS EXISTENTES
    private function actualizarColegiadoExistente($colegiado, $datos, $numeroFila) {
        try {
            // Verificar si el número del Excel es diferente al que ya tiene
            if (!empty($datos['numero_colegiatura']) && 
                $datos['numero_colegiatura'] != $colegiado->numero_colegiatura) {
                
                // Verificar si el nuevo número ya existe en OTRO colegiado
                $colegiadoConNuevoNumero = $this->colegiadoRepository
                    ->findByNumeroColegiatura($datos['numero_colegiatura']);
                
                if ($colegiadoConNuevoNumero && 
                    $colegiadoConNuevoNumero->idColegiados != $colegiado->idColegiados) {
                    $this->advertencias[] = "Fila $numeroFila: No se pudo cambiar número de {$colegiado->numero_colegiatura} a {$datos['numero_colegiatura']} porque ya existe";
                    // No cambiamos el número
                    $nuevoNumero = $colegiado->numero_colegiatura;
                } else {
                    // Podemos cambiar el número
                    $nuevoNumero = $datos['numero_colegiatura'];
                    $this->numPropios++;
                }
            } else {
                // Mantener número actual
                $nuevoNumero = $colegiado->numero_colegiatura;
            }

            // Preparar datos para actualización
            $datosUpdate = [
                'numero_colegiatura' => $nuevoNumero,
                'dni' => $colegiado->dni, // Mantener DNI original
                'nombres' => $datos['nombres'] ?? $colegiado->nombres,
                'apellido_paterno' => $datos['apellido_paterno'] ?? $colegiado->apellido_paterno,
                'apellido_materno' => $datos['apellido_materno'] ?? $colegiado->apellido_materno,
                'fecha_colegiatura' => $datos['fecha_colegiatura'] ?? $colegiado->fecha_colegiatura,
                'telefono' => $datos['telefono'] ?? $colegiado->telefono,
                'correo' => $datos['correo'] ?? $colegiado->correo,
                'direccion' => $datos['direccion'] ?? $colegiado->direccion,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? $colegiado->fecha_nacimiento,
                'observaciones' => $datos['observaciones'] ?? $colegiado->observaciones
            ];

            // Actualizar el registro existente
            $resultado = $this->colegiadoRepository->update($colegiado->idColegiados, $datosUpdate);

            if ($resultado) {
                $this->importados++;
                $this->advertencias[] = "Fila $numeroFila: Colegiado {$colegiado->dni} actualizado";
                logMessage("Colegiado actualizado desde importación: ID {$colegiado->idColegiados}", 'info');
            } else {
                $this->errores[] = "Fila $numeroFila: Error al actualizar colegiado existente {$colegiado->dni}";
                $this->omitidos++;
            }

        } catch (Exception $e) {
            $this->errores[] = "Fila $numeroFila: Error al actualizar {$colegiado->dni} - " . $e->getMessage();
            $this->omitidos++;
        }
    }

    // valida los datos de una fila
    private function validarDatosFila($datos, $numeroFila) {
        $errores = [];
        
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