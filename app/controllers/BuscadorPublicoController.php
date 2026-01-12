<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';

class BuscadorPublicoController extends Controller {
    private $colegiadoRepository;
    
    public function __construct() {
        parent::__construct();
        $this->colegiadoRepository = new ColegiadoRepository();
    }
    
    /**
     * Muestra la página del buscador público
     */
    public function index() {
        // Renderizar sin layout (página independiente)
        $this->view->setLayout(null);
        $this->render('public/buscador', [
            'titulo' => 'Buscador de Colegiados Habilitados'
        ]);
    }
    
    /**
     * Realiza la búsqueda por DNI (API)
     */
    public function buscar() {
        // Validar método
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        // Obtener DNI
        $dni = $this->getQuery('dni');
        
        // Validar DNI
        if (empty($dni)) {
            $this->json([
                'success' => false,
                'message' => 'Debe ingresar un número de DNI'
            ]);
            return;
        }
        
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            $this->json([
                'success' => false,
                'message' => 'El DNI debe tener 8 dígitos numéricos'
            ]);
            return;
        }
        
        // Buscar colegiado
        try {
            $colegiado = $this->colegiadoRepository->findByDni($dni);
            
            if (!$colegiado) {
                $this->json([
                    'success' => false,
                    'message' => 'No se encontró ningún colegiado con ese DNI',
                    'found' => false
                ]);
                return;
            }
            
            // Preparar respuesta con datos públicos únicamente
            $response = [
                'success' => true,
                'found' => true,
                'colegiado' => [
                    'numero_colegiatura' => formatNumeroColegiatura($colegiado->numero_colegiatura),
                    'nombres' => $colegiado->nombres,
                    'apellido_paterno' => $colegiado->apellido_paterno,
                    'apellido_materno' => $colegiado->apellido_materno,
                    'nombre_completo' => $colegiado->getNombreCompleto(),
                    'estado' => $colegiado->estado,
                    'estado_texto' => $colegiado->estado === 'habilitado' ? 'HABILITADO' : 'NO HABILITADO',
                    'fecha_colegiatura' => formatDate($colegiado->fecha_colegiatura)
                ]
            ];
            
            $this->json($response);
            
        } catch (Exception $e) {
            logMessage("Error en búsqueda pública: " . $e->getMessage(), 'error');
            
            $this->json([
                'success' => false,
                'message' => 'Ocurrió un error al realizar la búsqueda. Intente nuevamente.'
            ], 500);
        }
    }
}