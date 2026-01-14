<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/ColegiadoService.php';
require_once __DIR__ . '/../services/ExcelImportService.php';

class ColegiadoController extends Controller{
    private $colegiadoService;

    public function __construct(){
        parent::__construct();
        $this->colegiadoService = new ColegiadoService();
    }

    // listar los colegiados
    public function index() {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'ver');

        // Obtener página actual y registros por página
        $pagina = (int)($this->getQuery('pagina') ?? 1);
        $porPagina = 20;
        
        $filtros = [
            'numero_colegiatura' => $this->getQuery('numero_colegiatura'),
            'dni' => $this->getQuery('dni'),
            'nombres' => $this->getQuery('nombres'),
            'estado' => $this->getQuery('estado')
        ];
        
        // Si hay filtros, buscar con paginación, sino mostrar todos paginados
        if (array_filter($filtros)) {
            $resultado = $this->colegiadoService->buscarPaginado($filtros, $pagina, $porPagina);
        } else {
            $resultado = $this->colegiadoService->obtenerTodosPaginado($pagina, $porPagina);
        }
        
        $this->render('colegiados/index', [
            'colegiados' => $resultado['data'],
            'filtros' => $filtros,
            'paginacion' => [
                'pagina_actual' => $resultado['pagina'],
                'por_pagina' => $resultado['porPagina'],
                'total' => $resultado['total'],
                'total_paginas' => $resultado['totalPaginas']
            ],
            'active_menu' => 'colegiados',
            'titulo' => 'Gestión de Colegiados'
        ]);
    }

    // muestra el formulario para crear un colegiado
    public function crear() {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'crear');
        
        $this->render('colegiados/crear', [
            'titulo' => 'Nuevo Colegiado',
            'active_menu' => 'colegiados'
        ]);
    }

    // guarda un nuevo colegiado
    public function guardar() {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'crear');
        $this->validateMethod('POST');
        
        $datos = [
            'numero_colegiatura' => $this->getPost('numero_colegiatura'),
            'dni' => $this->getPost('dni'),
            'nombres' => $this->getPost('nombres'),
            'apellido_paterno' => $this->getPost('apellido_paterno'),
            'apellido_materno' => $this->getPost('apellido_materno'),
            'fecha_colegiatura' => $this->getPost('fecha_colegiatura'),
            'telefono' => $this->getPost('telefono'),
            'correo' => $this->getPost('correo'),
            'direccion' => $this->getPost('direccion'),
            'fecha_nacimiento' => $this->getPost('fecha_nacimiento'),
            'observaciones' => $this->getPost('observaciones')
        ];
        
        $resultado = $this->colegiadoService->crear($datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Colegiado registrado correctamente');
            $this->redirect(url('colegiados/ver/' . $resultado['id']));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('colegiados/crear'));
        }
    }

    // muestra la información completa de un colegiado
    public function ver($id) {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'ver');
        
        $info = $this->colegiadoService->obtenerInfoCompleta($id);
        
        if (!$info) {
            $this->setError('Colegiado no encontrado');
            $this->redirect(url('colegiados'));
            return;
        }
        
        $this->render('colegiados/ver', [
            'colegiado' => $info['colegiado'],
            'historial_estados' => $info['historial_estados'],
            'historial_pagos' => $info['historial_pagos'],
            'deudas' => $info['deudas'],
            'active_menu' => 'colegiados',
            'titulo' => 'Información del Colegiado'
        ]);
    }

    // muestra el formulario para editar un colegiado
    public function editar($id) {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'editar');
        
        $colegiado = $this->colegiadoService->obtenerPorId($id);
        
        if (!$colegiado) {
            $this->setError('Colegiado no encontrado');
            $this->redirect(url('colegiados'));
            return;
        }
        
        $this->render('colegiados/editar', [
            'colegiado' => $colegiado,
            'active_menu' => 'colegiados',
            'titulo' => 'Editar Colegiado'
        ]);
    }

    // actualiza un colegiado
    public function actualizar($id) {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'editar');
        $this->validateMethod('POST');
        
        $datos = [
            'numero_colegiatura' => $this->getPost('numero_colegiatura'),
            'dni' => $this->getPost('dni'),
            'nombres' => $this->getPost('nombres'),
            'apellido_paterno' => $this->getPost('apellido_paterno'),
            'apellido_materno' => $this->getPost('apellido_materno'),
            'fecha_colegiatura' => $this->getPost('fecha_colegiatura'),
            'telefono' => $this->getPost('telefono'),
            'correo' => $this->getPost('correo'),
            'direccion' => $this->getPost('direccion'),
            'fecha_nacimiento' => $this->getPost('fecha_nacimiento'),
            'observaciones' => $this->getPost('observaciones')
        ];
        
        $resultado = $this->colegiadoService->actualizar($id, $datos);
        
        if ($resultado['success']) {
            $this->setSuccess('Colegiado actualizado correctamente');
            $this->redirect(url('colegiados/ver/' . $id));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('colegiados/editar/' . $id));
        }
    }

    // cambia el estado de un colegiado
    public function cambiarEstado($id) {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'editar');
        $this->validateMethod('POST');
        
        $nuevoEstado = $this->getPost('estado');
        $motivo = $this->getPost('motivo');
        $usuarioId = authUserId();
        
        $resultado = $this->colegiadoService->cambiarEstado($id, $nuevoEstado, $motivo, $usuarioId);
        
        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }
        
        $this->redirect(url('colegiados/ver/' . $id));
    }

    // muestra el formulario de importación de Excel
    public function importar() {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'crear');
        
        $this->render('colegiados/importar', [
            'active_menu' => 'colegiados',
            'titulo' => 'Importar Colegiados desde Excel'
        ]);
    }

    // procesa el archivo Excel
    public function procesarExcel() {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'crear');
        $this->validateMethod('POST');
        
        // Verificar que se haya subido un archivo
        if (!isset($_FILES['archivo_excel'])) {
            $this->setError('No se ha seleccionado ningún archivo');
            $this->redirect(url('colegiados/importar'));
            return;
        }
        
        $excelService = new ExcelImportService();
        $resultado = $excelService->importarColegiados($_FILES['archivo_excel']);
        
        if (!$resultado['success']) {
            $this->setError($resultado['message']);
            $this->redirect(url('colegiados/importar'));
            return;
        }
        
        // Preparar mensaje de resultado
        $mensaje = "Importación completada: {$resultado['importados']} colegiado(s) importado(s)";
        
        if ($resultado['omitidos'] > 0) {
            $mensaje .= ", {$resultado['omitidos']} registro(s) omitido(s)";
        }
        
        // Guardar detalles en sesión para mostrarlos
        $_SESSION['resultado_importacion'] = $resultado;
        
        $this->setSuccess($mensaje);
        $this->redirect(url('colegiados/resultado-importacion'));
    }

    // muestra el resultado detallado de la importación
    public function resultadoImportacion() {
        $this->requireAuth();
        $this->requirePermission('colegiados', 'crear');
        
        if (!isset($_SESSION['resultado_importacion'])) {
            $this->redirect(url('colegiados'));
            return;
        }
        
        $resultado = $_SESSION['resultado_importacion'];
        unset($_SESSION['resultado_importacion']);
        
        $this->render('colegiados/resultado_importacion', [
            'resultado' => $resultado,
            'titulo' => 'Resultado de Importación'
        ]);
    }

    // descarga la plantilla de Excel
    public function descargarPlantilla() {
        $this->requireAuth();

        $excelService = new ExcelImportService();
        $excelService->generarPlantilla();
    }
}