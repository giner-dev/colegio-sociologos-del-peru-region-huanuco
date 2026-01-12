<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/EgresoService.php';

class EgresoController extends Controller {
    private $egresoService;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->egresoService = new EgresoService();
    }

    public function index() {
        $this->requireRole(['administrador', 'tesorero', 'decano']);
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 25;
        
        $filtros = [
            'fecha_inicio' => $this->getQuery('fecha_inicio'),
            'fecha_fin' => $this->getQuery('fecha_fin'),
            'tipo_gasto_id' => $this->getQuery('tipo_gasto_id')
        ];
        
        $resultado = $this->egresoService->obtenerEgresos($page, $perPage, $filtros);
        
        $this->render('egresos/index', [
            'egresos' => $resultado['egresos'],
            'pagination' => [
                'page' => $resultado['page'],
                'perPage' => $resultado['perPage'],
                'total' => $resultado['total'],
                'totalPages' => $resultado['totalPages']
            ],
            'filtros' => $filtros,
            'tiposGasto' => $resultado['tiposGasto'],
            'active_menu' => 'egresos',
            'titulo' => 'Gestión de Egresos'
        ]);
    }

    public function registrar() {
        $this->requireRole(['administrador', 'tesorero']);
        
        $tiposGasto = $this->egresoService->obtenerTiposGasto();
        
        $this->render('egresos/registrar', [
            'tiposGasto' => $tiposGasto,
            'active_menu' => 'egresos',
            'titulo' => 'Registrar Egreso'
        ]);
    }

    public function guardar() {
        $this->requireRole(['administrador', 'tesorero']);
        $this->validateMethod('POST');
        
        $datos = $this->getAllPost();
        
        if (!empty($_FILES['comprobante']['name'])) {
            $resultadoArchivo = $this->egresoService->subirComprobante($_FILES['comprobante']);
            if ($resultadoArchivo['success']) {
                $datos['comprobante'] = $resultadoArchivo['ruta'];
            } else {
                $this->setError($resultadoArchivo['message']);
                $this->back();
                return;
            }
        }
        
        $resultado = $this->egresoService->registrarEgreso($datos, authUserId());
        
        if ($resultado['success']) {
            $this->setSuccess('Egreso registrado correctamente');
            $this->redirect(url('egresos/ver/' . $resultado['id']));
        } else {
            $this->setError('Error: ' . implode(', ', $resultado['errors']));
            $this->back();
        }
    }

    public function ver($id) {
        $this->requireRole(['administrador', 'tesorero', 'decano']);
        
        $egreso = $this->egresoService->obtenerPorId($id);
        
        if (!$egreso) {
            $this->setError('Egreso no encontrado');
            $this->redirect(url('egresos'));
            return;
        }
        
        $this->render('egresos/ver', [
            'egreso' => $egreso,
            'active_menu' => 'egresos',
            'titulo' => 'Detalle del Egreso'
        ]);
    }

    public function editar($id) {
        $this->requireRole(['administrador', 'tesorero']);
        
        $egreso = $this->egresoService->obtenerPorId($id);
        
        if (!$egreso) {
            $this->setError('Egreso no encontrado');
            $this->redirect(url('egresos'));
            return;
        }
        
        $tiposGasto = $this->egresoService->obtenerTiposGasto();
        
        $this->render('egresos/editar', [
            'egreso' => $egreso,
            'tiposGasto' => $tiposGasto,
            'active_menu' => 'egresos',
            'titulo' => 'Editar Egreso'
        ]);
    }

    public function actualizar($id) {
        $this->requireRole(['administrador', 'tesorero']);
        $this->validateMethod('POST');
        
        $datos = $this->getAllPost();
        
        if (!empty($_FILES['comprobante']['name'])) {
            $resultadoArchivo = $this->egresoService->subirComprobante($_FILES['comprobante']);
            if ($resultadoArchivo['success']) {
                $datos['comprobante'] = $resultadoArchivo['ruta'];
            }
        }
        
        $resultado = $this->egresoService->actualizarEgreso($id, $datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Egreso actualizado correctamente');
            $this->redirect(url('egresos/ver/' . $id));
        } else {
            $this->setError('Error: ' . implode(', ', $resultado['errors']));
            $this->back();
        }
    }





    // GESTIÓN DE TIPOS DE GASTO (Solo Admin)
    public function tiposGasto() {
        $this->requireRole('administrador');
        
        $tiposGasto = $this->egresoService->obtenerTodosTiposGasto();
        
        $this->render('egresos/tipo_gasto/index', [
            'tiposGasto' => $tiposGasto,
            'active_menu' => 'egresos',
            'titulo' => 'Gestión de Tipos de Gasto'
        ]);
    }
    
    public function crearTipoGasto() {
        $this->requireRole('administrador');
        
        $this->render('egresos/tipo_gasto/crear', [
            'active_menu' => 'egresos',
            'titulo' => 'Nuevo Tipo de Gasto'
        ]);
    }
    
    public function guardarTipoGasto() {
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $datos = [
            'nombre' => $this->getPost('nombre'),
            'descripcion' => $this->getPost('descripcion'),
            'codigo' => $this->getPost('codigo')
        ];
        
        $resultado = $this->egresoService->crearTipoGasto($datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Tipo de gasto creado correctamente');
            $this->redirect(url('egresos/tipos-gasto'));
        } else {
            $this->setError('Error: ' . implode(', ', $resultado['errors']));
            $this->back();
        }
    }
    
    public function editarTipoGasto($id) {
        $this->requireRole('administrador');
        
        $tipoGasto = $this->egresoService->obtenerTipoGastoPorId($id);
        
        if (!$tipoGasto) {
            $this->setError('Tipo de gasto no encontrado');
            $this->redirect(url('egresos/tipos-gasto'));
            return;
        }
        
        $this->render('egresos/tipo_gasto/editar', [
            'tipoGasto' => $tipoGasto,
            'active_menu' => 'egresos',
            'titulo' => 'Editar Tipo de Gasto'
        ]);
    }
    
    public function actualizarTipoGasto($id) {
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $datos = [
            'nombre' => $this->getPost('nombre'),
            'descripcion' => $this->getPost('descripcion'),
            'codigo' => $this->getPost('codigo'),
            'estado' => $this->getPost('estado')
        ];
        
        $resultado = $this->egresoService->actualizarTipoGasto($id, $datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Tipo de gasto actualizado correctamente');
            $this->redirect(url('egresos/tipos-gasto'));
        } else {
            $this->setError('Error: ' . implode(', ', $resultado['errors']));
            $this->back();
        }
    }
    
    public function eliminarTipoGasto($id) {
        $this->requireRole('administrador');
        $this->validateMethod('POST');
        
        $resultado = $this->egresoService->eliminarTipoGasto($id);
        
        $this->json($resultado);
}
}