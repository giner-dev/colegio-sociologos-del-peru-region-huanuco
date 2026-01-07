<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/PagoService.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';

class PagoController extends Controller{
    private $pagoService;
    
    public function __construct() {
        parent::__construct();
        $this->pagoService = new PagoService();
    }

    // lista todos los pagos con paginación
    public function index() {
        $this->requireAuth();
        
        $page = $this->getQuery('page', 1);
        $perPage = 20;
        
        $filtros = [
            'numero_colegiatura' => $this->getQuery('numero_colegiatura'),
            'fecha_inicio' => $this->getQuery('fecha_inicio'),
            'fecha_fin' => $this->getQuery('fecha_fin'),
            'metodo_pago' => $this->getQuery('metodo_pago'),
            'estado' => $this->getQuery('estado')
        ];
        
        $resultado = $this->pagoService->obtenerPagos($page, $perPage, $filtros);
        $opciones = $this->pagoService->obtenerOpcionesPago();
        
        $this->render('pagos/index', [
            'pagos' => $resultado['pagos'],
            'pagination' => [
                'total' => $resultado['total'],
                'page' => $resultado['page'],
                'perPage' => $resultado['perPage'],
                'totalPages' => $resultado['totalPages']
            ],
            'filtros' => $filtros,
            'metodos' => $opciones['metodos'],
            'active_menu' => 'pagos',
            'titulo' => 'Gestión de Pagos'
        ]);
    }

    // muestra formulario para registrar pago
    public function registrar() {
        $this->requireAuth();
        $this->requireRole(['administrador', 'tesorero']);
        
        $opciones = $this->pagoService->obtenerOpcionesPago();
        
        $colegiadoRepo = new ColegiadoRepository();
        $colegiados = $colegiadoRepo->findAll();
        
        $this->render('pagos/registrar', [
            'metodos' => $opciones['metodos'],
            'conceptos' => $opciones['conceptos'],
            'colegiados' => $colegiados,
            'active_menu' => 'pagos',
            'titulo' => 'Registrar Pago'
        ]);
    }

    // guarda un nuevo pago
    public function guardar() {
        $this->requireAuth();
        $this->requireRole(['administrador', 'tesorero']);
        $this->validateMethod('POST');
        
        $datos = [
            'colegiados_id' => $this->getPost('colegiados_id'),
            'concepto_id' => $this->getPost('concepto_id'),
            'concepto_texto' => $this->getPost('concepto_texto'),
            'monto' => $this->getPost('monto'),
            'fecha_pago' => $this->getPost('fecha_pago'),
            'metodo_pago_id' => $this->getPost('metodo_pago_id'),
            'numero_comprobante' => $this->getPost('numero_comprobante'),
            'observaciones' => $this->getPost('observaciones')
        ];
        
        $resultado = $this->pagoService->registrarPago($datos, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess('Pago registrado correctamente');
            $this->redirect(url('pagos/ver/' . $resultado['id']));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/registrar'));
        }
    }

    // muestra detalles de un pago
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

    // anula un pago
    public function anular($id) {
        $this->requireAuth();
        $this->requireRole(['administrador']);
        $this->validateMethod('POST');
        
        $resultado = $this->pagoService->anularPago($id, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }
        
        $this->redirect(url('pagos/ver/' . $id));
    }
    
    // historial de pagos de un colegiado
    public function historialColegiado($idColegiado) {
        $this->requireAuth();
        
        $colegiadoRepo = new ColegiadoRepository();
        
        $colegiado = $colegiadoRepo->findById($idColegiado);
        if (!$colegiado) {
            $this->setError('Colegiado no encontrado');
            $this->redirect(url('colegiados'));
            return;
        }
        
        $pagos = $colegiadoRepo->getHistorialPagos($idColegiado);
        
        $this->render('pagos/historial', [
            'colegiado' => $colegiado,
            'pagos' => $pagos,
            'active_menu' => 'pagos',
            'titulo' => 'Historial de Pagos'
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
            'requiere_comprobante' => $this->getPost('requiere_comprobante')
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
            'descripcion' => $this->getPost('descripcion')
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
}