<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/DeudaService.php';
require_once __DIR__ . '/../services/ColegiadoService.php';

class DeudaController extends Controller {
    private $deudaService;
    private $colegiadoService;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->deudaService = new DeudaService();
        $this->colegiadoService = new ColegiadoService();
    }

    // Lista todas las deudas
    public function index() {
        $this->requireRole(['administrador', 'tesorero', 'decano']);
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 25;
        
        $filtros = [
            'numero_colegiatura' => $this->getQuery('numero_colegiatura'),
            'dni' => $this->getQuery('dni'),
            'estado' => $this->getQuery('estado'),
            'fecha_desde' => $this->getQuery('fecha_desde'),
            'fecha_hasta' => $this->getQuery('fecha_hasta'),
            'concepto_id' => $this->getQuery('concepto_id'),
            'origen' => $this->getQuery('origen')
        ];
        
        $resultado = $this->deudaService->obtenerDeudas($page, $perPage, $filtros);
        $resumen = $this->deudaService->obtenerResumen();
        
        $this->render('deudas/index', [
            'deudas' => $resultado['deudas'],
            'pagination' => [
                'page' => $resultado['page'],
                'perPage' => $resultado['perPage'],
                'total' => $resultado['total'],
                'totalPages' => $resultado['totalPages']
            ],
            'filtros' => $filtros,
            'resumen' => $resumen,
            'conceptos' => $resultado['conceptos'],
            'active_menu' => 'deudas',
            'titulo' => 'Gestión de Deudas'
        ]);
    }

    // Muestra formulario para registrar deuda
    public function registrar() {
        $this->requireRole(['administrador', 'tesorero']);
        
        $colegiados = $this->colegiadoService->obtenerTodos();
        $conceptos = $this->deudaService->obtenerConceptos();
        
        $this->render('deudas/registrar', [
            'colegiados' => $colegiados,
            'conceptos' => $conceptos,
            'active_menu' => 'deudas',
            'titulo' => 'Registrar Nueva Deuda'
        ]);
    }

    // Guarda una nueva deuda
    public function guardar() {
        $this->requireRole(['administrador', 'tesorero']);
        $this->validateMethod('POST');
        
        $datos = $this->getAllPost();
        
        // Agregar usuario actual
        $datos['usuario_generador_id'] = authUserId();
        
        $resultado = $this->deudaService->registrarDeuda($datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Deuda registrada correctamente');
            $this->redirect(url('deudas'));
        } else {
            $this->setError('Error al registrar la deuda: ' . implode(', ', $resultado['errors']));
            $this->back();
        }
    }

    // Muestra deudas de un colegiado específico
    public function porColegiado($id) {
        $this->requireRole(['administrador', 'tesorero', 'decano']);
        
        $resultado = $this->deudaService->obtenerPorColegiado($id);
        
        if (!$resultado['colegiado']) {
            $this->setError('Colegiado no encontrado');
            $this->redirect(url('colegiados'));
            return;
        }
        
        $this->render('deudas/por_colegiado', [
            'deudas' => $resultado['deudas'],
            'total' => $resultado['total'],
            'colegiado' => $resultado['colegiado'],
            'active_menu' => 'deudas',
            'titulo' => 'Deudas del Colegiado'
        ]);
    }

    // Elimina una deuda (vía AJAX)
    public function eliminar($id) {
        $this->requireRole(['administrador']);
        
        $resultado = $this->deudaService->eliminarDeuda($id);
        
        $this->json($resultado);
    }

    // Muestra listado de morosos
    public function morosos() {
        $this->requireRole(['administrador', 'tesorero', 'decano']);
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 25;
        
        $resultado = $this->deudaService->obtenerMorosos($page, $perPage);
        
        $this->render('deudas/morosos', [
            'morosos' => $resultado['morosos'],
            'pagination' => [
                'page' => $resultado['page'],
                'perPage' => $resultado['perPage'],
                'total' => $resultado['total'],
                'totalPages' => $resultado['totalPages']
            ],
            'active_menu' => 'deudas',
            'titulo' => 'Colegiados Morosos'
        ]);
    }

    // Cancela una deuda (vía AJAX)
    public function cancelar($id) {
        $this->requireRole(['administrador']);
        $this->validateMethod('POST');
        
        $motivo = $this->getPost('motivo', 'Cancelada por el administrador');
        
        $resultado = $this->deudaService->cancelarDeuda($id, $motivo);
        
        $this->json($resultado);
    }

    // obtiene deudas pendientes de un colegiado
    public function apiDeudasPendientes($colegiadoId) {
        $this->requireRole(['administrador', 'tesorero']);
        
        $resultado = $this->deudaService->obtenerPorColegiado($colegiadoId);
        
        if (!$resultado['colegiado']) {
            $this->json([
                'success' => false,
                'message' => 'Colegiado no encontrado'
            ], 404);
            return;
        }
        
        $deudasPendientes = array_filter($resultado['deudas'], function($deuda) {
            return $deuda->puedeSerPagada();
        });
        
        // Convertir a array para JSON
        $deudasArray = array_map(function($deuda) {
            return [
                'idDeuda' => $deuda->idDeuda,
                'concepto_nombre' => $deuda->concepto_nombre,
                'descripcion_deuda' => $deuda->descripcion_deuda,
                'monto_esperado' => $deuda->monto_esperado,
                'monto_pagado' => $deuda->monto_pagado,
                'saldo_pendiente' => $deuda->getSaldoPendiente(),
                'fecha_vencimiento' => $deuda->fecha_vencimiento,
                'estado' => $deuda->estado,
                'dias_vencimiento' => $deuda->getDiasVencimiento()
            ];
        }, $deudasPendientes);
        
        $this->json([
            'success' => true,
            'deudas' => array_values($deudasArray),
            'total_pendiente' => $resultado['total']
        ]);
    }


    // API para buscar colegiados con paginación
    public function apiColegiadosSearch() {
        $this->requireAuth();
        
        $busqueda = $this->getQuery('busqueda', '');
        $pagina = (int)($this->getQuery('pagina') ?? 1);
        $porPagina = 10;

        // USAR EL SERVICE, NO EL REPOSITORY
        $filtros = [];
        if (!empty($busqueda)) {
            // Determinar tipo de búsqueda
            if (is_numeric($busqueda)) {
                if (strlen($busqueda) <= 5) {
                    $filtros['numero_colegiatura'] = $busqueda;
                } elseif (strlen($busqueda) <= 8) {
                    $filtros['dni'] = $busqueda;
                } else {
                    // Si es numérico pero muy largo, buscar como nombre también
                    $filtros['nombres'] = $busqueda;
                }
            } else {
                // Búsqueda por texto (nombre)
                $filtros['nombres'] = $busqueda;
            }
        }

        try {
            // USAR EL SERVICE
            $resultado = $this->colegiadoService->buscarPaginado($filtros, $pagina, $porPagina);

            // Formatear respuesta
            $colegiados = array_map(function($col) {
                return [
                    'id' => $col->idColegiados,
                    'numero_colegiatura' => formatNumeroColegiatura($col->numero_colegiatura),
                    'dni' => $col->dni,
                    'nombre_completo' => $col->getNombreCompleto(),
                    'estado' => $col->estado
                ];
            }, $resultado['data']);

            $this->json([
                'success' => true,
                'colegiados' => $colegiados,
                'total' => $resultado['total'],
                'pagina' => $resultado['pagina'],
                'totalPaginas' => $resultado['totalPaginas']
            ]);
        } catch (Exception $e) {
            error_log('Error en apiColegiadosSearch: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}