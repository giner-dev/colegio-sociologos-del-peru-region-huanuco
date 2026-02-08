<?php
/**
 * CRON JOB: Generador Automático de Deudas Recurrentes
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

require_once ROOT_PATH . '/helpers/functions.php';
loadEnv();
date_default_timezone_set('America/Lima');

require_once ROOT_PATH . '/core/Database.php';

try {
    $db = Database::getInstance();
    $hoy = date('Y-m-d');
    
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando generación de deudas recurrentes...\n";
    
    // PASO 1: Obtener programaciones activas que deban generar hoy
    $sqlProgramaciones = "SELECT 
            pd.*,
            c.numero_colegiatura,
            c.dni,
            CONCAT(c.apellido_paterno, ' ', c.apellido_materno, ', ', c.nombres) as nombre_completo,
            cp.nombre_completo as concepto_nombre
        FROM programacion_deudas pd
        INNER JOIN colegiados c ON pd.colegiado_id = c.idColegiados
        INNER JOIN conceptos_pago cp ON pd.concepto_id = cp.idConcepto
        WHERE pd.estado = 'activa'
        AND (pd.proxima_generacion <= :hoy OR pd.proxima_generacion IS NULL)
        AND c.estado IN ('habilitado', 'inhabilitado')
        ORDER BY pd.idProgramacion ASC";
    
    $programaciones = $db->query($sqlProgramaciones, ['hoy' => $hoy]);
    
    if (empty($programaciones)) {
        echo "No hay programaciones pendientes de generar.\n";
        exit(0);
    }
    
    echo "Encontradas " . count($programaciones) . " programaciones pendientes.\n\n";
    
    $generadas = 0;
    $errores = 0;
    
    foreach ($programaciones as $prog) {
        try {
            $db->beginTransaction();
            
            // Calcular fecha de vencimiento
            $fechaVencimiento = calcularFechaVencimiento($prog['dia_vencimiento'], $prog['frecuencia']);
            
            // Verificar si ya existe deuda para este periodo
            $sqlVerificar = "SELECT idDeuda FROM deudas 
                WHERE colegiado_id = :colegiado_id 
                AND concepto_id = :concepto_id
                AND fecha_vencimiento = :fecha_vencimiento
                AND estado IN ('pendiente', 'parcial', 'vencido')
                LIMIT 1";
            
            $existente = $db->queryOne($sqlVerificar, [
                'colegiado_id' => $prog['colegiado_id'],
                'concepto_id' => $prog['concepto_id'],
                'fecha_vencimiento' => $fechaVencimiento
            ]);
            
            if ($existente) {
                echo "  [SKIP] Colegiado {$prog['numero_colegiatura']}: Ya existe deuda para {$fechaVencimiento}\n";
                
                // Actualizar próxima generación aunque ya exista
                $proximaFecha = calcularProximaFecha($fechaVencimiento, $prog['frecuencia']);
                actualizarProximaGeneracion($db, $prog['idProgramacion'], $proximaFecha);
                
                $db->commit();
                continue;
            }
            
            // Crear nueva deuda
            $sqlInsert = "INSERT INTO deudas (
                    colegiado_id,
                    concepto_id,
                    concepto_manual,
                    es_deuda_manual,
                    descripcion_deuda,
                    monto_esperado,
                    fecha_generacion,
                    fecha_vencimiento,
                    fecha_maxima_pago,
                    estado,
                    origen,
                    deuda_padre_id,
                    usuario_generador_id,
                    observaciones
                ) VALUES (
                    :colegiado_id,
                    :concepto_id,
                    NULL,
                    0,
                    :descripcion,
                    :monto,
                    :fecha_gen,
                    :fecha_venc,
                    :fecha_max,
                    :estado,
                    'recurrente',
                    NULL,
                    NULL,
                    :observaciones
                )";
            
            $mesAnio = date('m/Y', strtotime($fechaVencimiento));
            $descripcion = "{$prog['concepto_nombre']} - " . obtenerNombrePeriodo($fechaVencimiento, $prog['frecuencia']);
            $estadoInicial = ($fechaVencimiento < $hoy) ? 'vencido' : 'pendiente';
            
            $deudaId = $db->insert($sqlInsert, [
                'colegiado_id' => $prog['colegiado_id'],
                'concepto_id' => $prog['concepto_id'],
                'descripcion' => $descripcion,
                'monto' => $prog['monto'],
                'fecha_gen' => $hoy,
                'fecha_venc' => $fechaVencimiento,
                'fecha_max' => $fechaVencimiento,
                'estado' => $estadoInicial,
                'observaciones' => 'Generada automáticamente por programación recurrente'
            ]);
            
            // Actualizar próxima generación
            $proximaFecha = calcularProximaFecha($fechaVencimiento, $prog['frecuencia']);
            actualizarProximaGeneracion($db, $prog['idProgramacion'], $proximaFecha);
            
            $db->commit();
            
            echo "  [OK] Colegiado {$prog['numero_colegiatura']}: Deuda #{$deudaId} generada para {$fechaVencimiento}\n";
            $generadas++;
            
        } catch (Exception $e) {
            $db->rollback();
            echo "  [ERROR] Colegiado {$prog['numero_colegiatura']}: {$e->getMessage()}\n";
            $errores++;
        }
    }
    
    echo "\n==============================================\n";
    echo "RESUMEN:\n";
    echo "  Deudas generadas: {$generadas}\n";
    echo "  Errores: {$errores}\n";
    echo "==============================================\n";
    
    logMessage("Cron deudas recurrentes ejecutado: {$generadas} generadas, {$errores} errores", 'info');
    
    // PASO 2: Actualizar estados vencidos
    actualizarDeudasVencidas($db);
    
} catch (Exception $e) {
    echo "[FATAL ERROR] " . $e->getMessage() . "\n";
    logMessage("Error fatal en cron deudas: " . $e->getMessage(), 'error');
    exit(1);
}

// ===================================
// FUNCIONES AUXILIARES
// ===================================

function calcularFechaVencimiento($diaVencimiento, $frecuencia) {
    $hoy = new DateTime();
    $dia = (int)$diaVencimiento;
    
    switch($frecuencia) {
        case 'mensual':
            $fecha = new DateTime($hoy->format('Y-m') . '-01');
            break;
        case 'trimestral':
            $mesActual = (int)$hoy->format('m');
            $mesTrimestre = (ceil($mesActual / 3) * 3) - 2;
            $fecha = new DateTime($hoy->format('Y') . '-' . str_pad($mesTrimestre, 2, '0', STR_PAD_LEFT) . '-01');
            break;
        case 'semestral':
            $mesActual = (int)$hoy->format('m');
            $mesSemestre = ($mesActual <= 6) ? 1 : 7;
            $fecha = new DateTime($hoy->format('Y') . '-' . str_pad($mesSemestre, 2, '0', STR_PAD_LEFT) . '-01');
            break;
        case 'anual':
            $fecha = new DateTime($hoy->format('Y') . '-01-01');
            break;
        default:
            $fecha = clone $hoy;
    }
    
    $fecha->setDate($fecha->format('Y'), $fecha->format('m'), min($dia, 28));
    
    return $fecha->format('Y-m-d');
}

function calcularProximaFecha($fechaBase, $frecuencia) {
    $fecha = new DateTime($fechaBase);
    
    switch($frecuencia) {
        case 'mensual':
            $fecha->modify('+1 month');
            break;
        case 'trimestral':
            $fecha->modify('+3 months');
            break;
        case 'semestral':
            $fecha->modify('+6 months');
            break;
        case 'anual':
            $fecha->modify('+1 year');
            break;
    }
    
    return $fecha->format('Y-m-d');
}

function actualizarProximaGeneracion($db, $programacionId, $proximaFecha) {
    $sql = "UPDATE programacion_deudas 
            SET proxima_generacion = :proxima,
                ultima_generacion = :ultima
            WHERE idProgramacion = :id";
    
    $db->execute($sql, [
        'proxima' => $proximaFecha,
        'ultima' => date('Y-m-d'),
        'id' => $programacionId
    ]);
}

function obtenerNombrePeriodo($fecha, $frecuencia) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    $dt = new DateTime($fecha);
    $mes = (int)$dt->format('m');
    $anio = $dt->format('Y');
    
    switch($frecuencia) {
        case 'mensual':
            return $meses[$mes] . ' ' . $anio;
        case 'trimestral':
            $trimestre = ceil($mes / 3);
            return "Q{$trimestre} {$anio}";
        case 'semestral':
            $semestre = ($mes <= 6) ? 1 : 2;
            return "Semestre {$semestre} {$anio}";
        case 'anual':
            return "Año {$anio}";
        default:
            return $meses[$mes] . ' ' . $anio;
    }
}

function actualizarDeudasVencidas($db) {
    $sql = "UPDATE deudas 
            SET estado = 'vencido' 
            WHERE estado = 'pendiente' 
            AND fecha_vencimiento < CURDATE()";
    
    $actualizadas = $db->execute($sql);
    
    if ($actualizadas > 0) {
        echo "\n[INFO] {$actualizadas} deudas marcadas como vencidas.\n";
    }
}