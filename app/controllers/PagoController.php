<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/PagoService.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';
require_once __DIR__ . '/../repositories/DeudaRepository.php';

class PagoController extends Controller {
    private $pagoService;
    
    public function __construct() {
        parent::__construct();
        $this->pagoService = new PagoService();
    }

    // Lista todos los pagos con paginación
    public function index() {
        $this->requireAuth();
        $this->requirePermission('pagos', 'ver');
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 20;
        
        $filtros = [
            'numero_colegiatura' => $this->getQuery('numero_colegiatura'),
            'dni' => $this->getQuery('dni'),
            'fecha_inicio' => $this->getQuery('fecha_inicio'),
            'fecha_fin' => $this->getQuery('fecha_fin'),
            'metodo_pago' => $this->getQuery('metodo_pago'),
            'estado' => $this->getQuery('estado'),
            'concepto_id' => $this->getQuery('concepto_id')
        ];
        
        $resultado = $this->pagoService->obtenerPagos($page, $perPage, $filtros);
        
        $this->render('pagos/index', [
            'pagos' => $resultado['pagos'],
            'pagination' => [
                'total' => $resultado['total'],
                'page' => $resultado['page'],
                'perPage' => $resultado['perPage'],
                'totalPages' => $resultado['totalPages']
            ],
            'filtros' => $filtros,
            'metodos' => $resultado['metodos'],
            'conceptos' => $resultado['conceptos'],
            'active_menu' => 'pagos',
            'titulo' => 'Gestión de Pagos'
        ]);
    }

    // Obtiene colegiados CON deudas pendientes
    private function obtenerColegiadosConDeudas() {
        $this->requirePermission('pagos', 'ver');
        $colegiadoRepo = new ColegiadoRepository();
        $deudaRepo = new DeudaRepository();
        
        $todosColegiados = $colegiadoRepo->findAll();
        
        $colegiadosConDeudas = [];
        foreach ($todosColegiados as $colegiado) {
            if ($deudaRepo->tieneDeudasPendientes($colegiado->idColegiados)) {
                $colegiadosConDeudas[] = $colegiado;
            }
        }
        
        return $colegiadosConDeudas;
    }

    // Muestra formulario para registrar pago
    public function registrar() {
        $this->requireAuth();
        $this->requirePermission('pagos', 'crear');
        
        $opciones = $this->pagoService->obtenerOpcionesPago();
        
        // Obtener colegiados con deudas pendientes
        $deudaRepo = new DeudaRepository();
        $colegiadosRepo = new ColegiadoRepository();
        
        // Obtener IDs de colegiados con deudas
        $sql = "SELECT DISTINCT colegiado_id 
                FROM deudas 
                WHERE estado IN ('pendiente', 'vencido', 'parcial')
                AND saldo_pendiente > 0";

        $db = Database::getInstance();
        $results = $db->query($sql);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiado = $colegiadosRepo->findById($row['colegiado_id']);
            if ($colegiado) {
                $colegiados[] = $colegiado;
            }
        }

        // Resto del código permanece igual...
        $deudaId = $this->getQuery('deuda_id');
        $colegiadoId = $this->getQuery('colegiado_id');

        $deudasPendientes = [];
        $colegiadoSeleccionado = null;

        if ($colegiadoId) {
            $deudasPendientes = $this->pagoService->obtenerDeudasPendientes($colegiadoId);
            $colegiadoRepo = new ColegiadoRepository();
            $colegiadoSeleccionado = $colegiadoRepo->findById($colegiadoId);
        }

        $this->render('pagos/registrar', [
            'metodos' => $opciones['metodos'],
            'colegiados' => $colegiados,
            'deudasPendientes' => $deudasPendientes,
            'colegiadoSeleccionado' => $colegiadoSeleccionado,
            'deudaId' => $deudaId,
            'colegiadoId' => $colegiadoId,
            'active_menu' => 'pagos',
            'titulo' => 'Registrar Pago'
        ]);
    }

    // Guarda un nuevo pago
    public function guardar() {
        $this->requireAuth();
        $this->requirePermission('pagos', 'crear');
        $this->validateMethod('POST');
        
        $datos = [
            'colegiado_id' => $this->getPost('colegiado_id'),
            'deuda_id' => $this->getPost('deuda_id'),
            'monto' => $this->getPost('monto'),
            'fecha_pago' => $this->getPost('fecha_pago'),
            'metodo_pago_id' => $this->getPost('metodo_pago_id'),
            'numero_comprobante' => $this->getPost('numero_comprobante'),
            'observaciones' => $this->getPost('observaciones')
        ];
        
        // Procesar archivo si se subió
        if (!empty($_FILES['archivo_comprobante']['name'])) {
            $resultadoArchivo = $this->pagoService->subirComprobante($_FILES['archivo_comprobante']);
            if ($resultadoArchivo['success']) {
                $datos['archivo_comprobante'] = $resultadoArchivo['ruta'];
            } else {
                $this->setError($resultadoArchivo['message']);
                $this->redirect(url('pagos/registrar'));
                return;
            }
        }
        
        $resultado = $this->pagoService->registrarPago($datos, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess('Pago registrado correctamente');
            $this->redirect(url('pagos/ver/' . $resultado['id']));
        } else {
            $this->setError('Error: ' . implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/registrar'));
        }
    }

    // Muestra detalles de un pago
    public function ver($id) {
        $this->requireAuth();
        
        $pago = $this->pagoService->obtenerPorId($id);
        
        if (!$pago) {
            $this->setError('Pago no encontrado');
            $this->redirect(url('pagos'));
            return;
        }
        
        $this->render('pagos/ver', [
            'pago' => $pago,
            'active_menu' => 'pagos',
            'titulo' => 'Detalle del Pago'
        ]);
    }

    // Confirma un pago
    public function confirmar($id) {
        $this->requireAuth();
        $this->requirePermission('pagos', 'crear');
        $this->validateMethod('POST');
        
        $resultado = $this->pagoService->confirmarPago($id, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }
        
        $this->redirect(url('pagos/ver/' . $id));
    }

    // Anula un pago
    public function anular($id) {
        $this->requireAuth();
        $this->requirePermission('pagos', 'crear ');
        $this->validateMethod('POST');
        
        $resultado = $this->pagoService->anularPago($id, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }
        
        $this->redirect(url('pagos/ver/' . $id));
    }
    
    // Historial de pagos de un colegiado
    public function historialColegiado($idColegiado) {
        $this->requireAuth();
        
        $colegiadoRepo = new ColegiadoRepository();
        $colegiado = $colegiadoRepo->findById($idColegiado);
        
        if (!$colegiado) {
            $this->setError('Colegiado no encontrado');
            $this->redirect(url('colegiados'));
            return;
        }
        
        $pagos = $this->pagoService->obtenerHistorialColegiado($idColegiado);
        
        $this->render('pagos/historial', [
            'colegiado' => $colegiado,
            'pagos' => $pagos,
            'active_menu' => 'pagos',
            'titulo' => 'Historial de Pagos'
        ]);
    }

    // API: Obtiene deudas pendientes de un colegiado
    public function apiDeudasPendientes($colegiadoId) {
        $this->requireAuth();
        $this->requirePermission('pagos', 'ver');
        
        try {
            // Log para debugging
            error_log("=== API Deudas Pendientes ===");
            error_log("Colegiado ID: " . $colegiadoId);
            
            // Obtener deudas usando el servicio
            $deudasData = $this->pagoService->obtenerDeudasPendientes($colegiadoId);
            
            error_log("Deudas encontradas: " . count($deudasData));
            error_log("Tipo de dato: " . gettype($deudasData));
            
            // Normalizar datos - el servicio ya devuelve arrays
            $deudasArray = [];
            
            if (is_array($deudasData) && !empty($deudasData)) {
                foreach ($deudasData as $deuda) {
                    // El servicio ya convierte todo a arrays
                    if (is_array($deuda)) {
                        $deudasArray[] = [
                            'idDeuda' => $deuda['idDeuda'] ?? null,
                            'concepto_id' => $deuda['concepto_id'] ?? null,
                            'concepto_nombre' => $deuda['concepto_nombre'] ?? 'Sin concepto',
                            'descripcion_deuda' => $deuda['descripcion_deuda'] ?? '',
                            'monto_esperado' => floatval($deuda['monto_esperado'] ?? 0),
                            'monto_pagado' => floatval($deuda['monto_pagado'] ?? 0),
                            'saldo_pendiente' => floatval($deuda['saldo_pendiente'] ?? 0),
                            'fecha_generacion' => $deuda['fecha_generacion'] ?? null,
                            'fecha_vencimiento' => $deuda['fecha_vencimiento'] ?? null,
                            'fecha_maxima_pago' => $deuda['fecha_maxima_pago'] ?? null,
                            'estado' => $deuda['estado'] ?? 'pendiente',
                            'origen' => $deuda['origen'] ?? 'manual'
                        ];
                    } 
                    // Por si acaso llegara a ser un objeto (no debería)
                    elseif (is_object($deuda)) {
                        error_log("Deuda es objeto, convirtiendo...");
                        
                        $deudasArray[] = [
                            'idDeuda' => $deuda->idDeuda ?? null,
                            'concepto_id' => $deuda->concepto_id ?? null,
                            'concepto_nombre' => $deuda->concepto_nombre ?? 'Sin concepto',
                            'descripcion_deuda' => $deuda->descripcion_deuda ?? '',
                            'monto_esperado' => floatval($deuda->monto_esperado ?? 0),
                            'monto_pagado' => floatval($deuda->monto_pagado ?? 0),
                            'saldo_pendiente' => floatval($deuda->saldo_pendiente ?? 0),
                            'fecha_generacion' => $deuda->fecha_generacion ?? null,
                            'fecha_vencimiento' => $deuda->fecha_vencimiento ?? null,
                            'fecha_maxima_pago' => $deuda->fecha_maxima_pago ?? null,
                            'estado' => $deuda->estado ?? 'pendiente',
                            'origen' => $deuda->origen ?? 'manual'
                        ];
                    }
                }
            }
            
            error_log("Deudas procesadas: " . count($deudasArray));
            error_log("JSON a enviar: " . json_encode($deudasArray));
            
            // Respuesta
            $this->json([
                'success' => true,
                'deudas' => $deudasArray,
                'total' => count($deudasArray),
                'colegiado_id' => $colegiadoId
            ]);
            
        } catch (Exception $e) {
            error_log("❌ ERROR en apiDeudasPendientes: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $this->json([
                'success' => false,
                'message' => 'Error al cargar deudas: ' . $e->getMessage(),
                'deudas' => [],
                'error_detail' => $e->getMessage()
            ]);
        }
    }

    // API: Obtener métodos de pago
    public function apiMetodos() {
        $this->requireAuth();

        $metodos = $this->pagoService->obtenerTodosMetodos();

        $this->json([
            'success' => true,
            'metodos' => $metodos
        ]);
    }

    // GESTIÓN DE CONCEPTOS DE PAGO (Solo Admin)
    public function conceptos() {
        $this->requireAuth();
        $this->requireRole('administrador');
        
        $conceptos = $this->pagoService->obtenerTodosConceptos();
        
        $this->render('pagos/conceptos/index', [
            'conceptos' => $conceptos,
            'active_menu' => 'conceptos',
            'titulo' => 'Gestión de Conceptos de Pago'
        ]);
    }

    public function crearConcepto() {
        $this->requireAuth();
        $this->requireRole('administrador');
        
        $this->render('pagos/conceptos/crear', [
            'active_menu' => 'conceptos',
            'titulo' => 'Nuevo Concepto de Pago'
        ]);
    }
    
    public function guardarConcepto() {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $datos = [
            'nombre' => $this->getPost('nombre'),
            'descripcion' => $this->getPost('descripcion'),
            'monto' => $this->getPost('monto'),
            'tipo' => $this->getPost('tipo'),
            'requiere_comprobante' => $this->getPost('requiere_comprobante'),
            'es_recurrente' => $this->getPost('es_recurrente'),
            'frecuencia' => $this->getPost('frecuencia'),
            'dia_vencimiento' => $this->getPost('dia_vencimiento')
        ];
        
        $resultado = $this->pagoService->crearConcepto($datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Concepto creado correctamente');
            $this->redirect(url('pagos/conceptos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/conceptos/crear'));
        }
    }
    
    public function editarConcepto($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        
        $concepto = $this->pagoService->obtenerConceptoPorId($id);
        
        if (!$concepto) {
            $this->setError('Concepto no encontrado');
            $this->redirect(url('pagos/conceptos'));
            return;
        }
        
        $this->render('pagos/conceptos/editar', [
            'concepto' => $concepto,
            'active_menu' => 'conceptos',
            'titulo' => 'Editar Concepto'
        ]);
    }
    
    public function actualizarConcepto($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $datos = [
            'nombre' => $this->getPost('nombre'),
            'descripcion' => $this->getPost('descripcion'),
            'monto' => $this->getPost('monto'),
            'tipo' => $this->getPost('tipo'),
            'requiere_comprobante' => $this->getPost('requiere_comprobante'),
            'es_recurrente' => $this->getPost('es_recurrente'),
            'frecuencia' => $this->getPost('frecuencia'),
            'dia_vencimiento' => $this->getPost('dia_vencimiento'),
            'estado' => $this->getPost('estado')
        ];
        
        $resultado = $this->pagoService->actualizarConcepto($id, $datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Concepto actualizado correctamente');
            $this->redirect(url('pagos/conceptos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/conceptos/editar/' . $id));
        }
    }
    
    public function eliminarConcepto($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $resultado = $this->pagoService->eliminarConcepto($id);
        
        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }
        
        $this->redirect(url('pagos/conceptos'));
    }

    // GESTIÓN DE MÉTODOS DE PAGO (Solo Admin)
    public function metodos() {
        $this->requireAuth();
        $this->requireRole('administrador');
        
        $metodos = $this->pagoService->obtenerTodosMetodos();
        
        $this->render('pagos/metodos/index', [
            'metodos' => $metodos,
            'active_menu' => 'metodos',
            'titulo' => 'Gestión de Métodos de Pago'
        ]);
    }
    
    public function crearMetodo() {
        $this->requireAuth();
        $this->requireRole('administrador');
        
        $this->render('pagos/metodos/crear', [
            'active_menu' => 'metodos',
            'titulo' => 'Nuevo Método de Pago'
        ]);
    }
    
    public function guardarMetodo() {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $datos = [
            'nombre' => $this->getPost('nombre'),
            'descripcion' => $this->getPost('descripcion'),
            'codigo' => $this->getPost('codigo'),
            'requiere_comprobante' => $this->getPost('requiere_comprobante'),
            'datos_adicionales' => $this->getPost('datos_adicionales'),
            'orden' => $this->getPost('orden')
        ];
        
        $resultado = $this->pagoService->crearMetodo($datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Método de pago creado correctamente');
            $this->redirect(url('pagos/metodos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/metodos/crear'));
        }
    }
    
    public function editarMetodo($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        
        $metodo = $this->pagoService->obtenerMetodoPorId($id);
        
        if (!$metodo) {
            $this->setError('Método no encontrado');
            $this->redirect(url('pagos/metodos'));
            return;
        }
        
        $this->render('pagos/metodos/editar', [
            'metodo' => $metodo,
            'active_menu' => 'metodos',
            'titulo' => 'Editar Método de Pago'
        ]);
    }
    
    public function actualizarMetodo($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $datos = [
            'nombre' => $this->getPost('nombre'),
            'descripcion' => $this->getPost('descripcion'),
            'codigo' => $this->getPost('codigo'),
            'requiere_comprobante' => $this->getPost('requiere_comprobante'),
            'datos_adicionales' => $this->getPost('datos_adicionales'),
            'orden' => $this->getPost('orden'),
            'activo' => $this->getPost('activo')
        ];
        
        $resultado = $this->pagoService->actualizarMetodo($id, $datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Método actualizado correctamente');
            $this->redirect(url('pagos/metodos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/metodos/editar/' . $id));
        }
    }
    
    public function eliminarMetodo($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $resultado = $this->pagoService->eliminarMetodo($id);
        
        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }
        
        $this->redirect(url('pagos/metodos'));
    }


    public function registrarAdelantado() {
        $this->requireAuth();
        $this->requirePermission('pagos', 'crear');
        
        $opciones = $this->pagoService->obtenerOpcionesPago();
        
        // PAGINACIÓN Y BÚSQUEDA
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 10;
        $busqueda = $this->getQuery('busqueda', '');
        
        $deudaRepo = new DeudaRepository();
        $colegiadosRepo = new ColegiadoRepository();
        
        // Obtener IDs base de colegiados con programaciones activas
        $sql = "SELECT DISTINCT p.colegiado_id 
                FROM programacion_deudas p
                WHERE p.estado = 'activa'
                AND (p.fecha_fin IS NULL OR p.fecha_fin >= CURDATE())";
        
        $db = Database::getInstance();
        $results = $db->query($sql);
        
        $idsConProgramacion = array_column($results, 'colegiado_id');
        
        if (empty($idsConProgramacion)) {
            $this->render('pagos/registrar_adelantado', [
                'metodos' => $opciones['metodos'],
                'colegiados' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => 1,
                    'perPage' => $perPage,
                    'totalPages' => 0
                ],
                'busqueda' => $busqueda,
                'active_menu' => 'pagos',
                'titulo' => 'Registrar Pago Adelantado'
            ]);
            return;
        }
        
        // Construir filtros para búsqueda
        $filtros = [];
        if (!empty($busqueda)) {
            // Determinar tipo de búsqueda
            if (is_numeric($busqueda)) {
                if (strlen($busqueda) <= 5) {
                    $filtros['numero_colegiatura'] = $busqueda;
                } elseif (strlen($busqueda) <= 8) {
                    $filtros['dni'] = $busqueda;
                } else {
                    $filtros['nombres'] = $busqueda;
                }
            } else {
                $filtros['nombres'] = $busqueda;
            }
        }
        
        // Obtener colegiados filtrados y paginados
        $resultado = $colegiadosRepo->buscarPaginated($filtros, $page, $perPage);
        
        // Filtrar solo los que tienen programaciones activas
        $colegiadosFiltrados = array_filter($resultado['data'], function($col) use ($idsConProgramacion) {
            return in_array($col->idColegiados, $idsConProgramacion);
        });
        
        // Recalcular totales después del filtro
        $totalFiltrados = count($colegiadosFiltrados);
        $totalPages = ceil($totalFiltrados / $perPage);
        
        $this->render('pagos/registrar_adelantado', [
            'metodos' => $opciones['metodos'],
            'colegiados' => array_values($colegiadosFiltrados),
            'pagination' => [
                'total' => $totalFiltrados,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages
            ],
            'busqueda' => $busqueda,
            'active_menu' => 'pagos',
            'titulo' => 'Registrar Pago Adelantado'
        ]);
    }
    
    public function apiProgramaciones($colegiadoId) {
        $this->requireAuth();
        $this->requirePermission('pagos', 'ver');
        
        try {
            $programaciones = $this->pagoService->obtenerProgramacionesPorColegiado($colegiadoId);
            
            $this->json([
                'success' => true,
                'programaciones' => $programaciones
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al cargar programaciones: ' . $e->getMessage()
            ]);
        }
    }
    
    public function guardarAdelantado() {
        $this->requireAuth();
        $this->requirePermission('pagos', 'crear');
        $this->validateMethod('POST');
        
        $datos = [
            'colegiado_id' => $this->getPost('colegiado_id'),
            'programacion_id' => $this->getPost('programacion_id'),
            'meses_adelantado' => $this->getPost('meses_adelantado'),
            'monto' => $this->getPost('monto'),
            'fecha_pago' => $this->getPost('fecha_pago'),
            'metodo_pago_id' => $this->getPost('metodo_pago_id'),
            'numero_comprobante' => $this->getPost('numero_comprobante'),
            'observaciones' => $this->getPost('observaciones')
        ];
        
        if (!empty($_FILES['archivo_comprobante']['name'])) {
            $resultadoArchivo = $this->pagoService->subirComprobante($_FILES['archivo_comprobante']);
            if ($resultadoArchivo['success']) {
                $datos['archivo_comprobante'] = $resultadoArchivo['ruta'];
            } else {
                $this->setError($resultadoArchivo['message']);
                $this->redirect(url('pagos/registrar-adelantado'));
                return;
            }
        }
        
        $resultado = $this->pagoService->registrarPagoAdelantado($datos, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess('Pago adelantado registrado correctamente. Se pagaron ' . $resultado['periodos_pagados'] . ' periodo(s).');
            $this->redirect(url('pagos/ver/' . $resultado['id']));
        } else {
            $this->setError('Error: ' . implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/registrar-adelantado'));
        }
    }
}