<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';

class BuscadorPublicoController extends Controller {
    private $colegiadoRepository;
    
    public function __construct() {
        parent::__construct();
        $this->colegiadoRepository = new ColegiadoRepository();
    }
    
    //Muestra la página del buscador público
    public function index() {
        // Renderizar sin layout (página independiente)
        $this->view->setLayout(null);
        $this->render('public/buscador', [
            'titulo' => 'Buscador de Colegiados Habilitados'
        ]);
    }
    
    // Realiza la búsqueda por DNI (API)
    public function buscar() {
        // Validar método
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        // Obtener parámetros de búsqueda
        $dni = $this->getQuery('dni');
        $numeroColegiatura = $this->getQuery('numero_colegiatura');

        // Validar que al menos uno esté presente
        if (empty($dni) && empty($numeroColegiatura)) {
            $this->json([
                'success' => false,
                'message' => 'Debe ingresar un DNI o Número de Colegiatura'
            ]);
            return;
        }

        // Buscar colegiado
        try {
            $colegiado = null;

            // Buscar por DNI si está presente
            if (!empty($dni)) {
                // Validar formato DNI
                if (strlen($dni) !== 8 || !ctype_digit($dni)) {
                    $this->json([
                        'success' => false,
                        'message' => 'El DNI debe tener 8 dígitos numéricos'
                    ]);
                    return;
                }

                $colegiado = $this->colegiadoRepository->findByDni($dni);
            }
            // Buscar por número de colegiatura si DNI no está presente
            else if (!empty($numeroColegiatura)) {
                // Limpiar ceros a la izquierda y convertir a número puro
                $numeroLimpio = ltrim($numeroColegiatura, '0');

                // Si quedó vacío después de quitar ceros, es "0"
                if (empty($numeroLimpio)) {
                    $numeroLimpio = '0';
                }

                // Validar que sea numérico
                if (!ctype_digit($numeroLimpio)) {
                    $this->json([
                        'success' => false,
                        'message' => 'El número de colegiatura debe ser numérico'
                    ]);
                    return;
                }

                $colegiado = $this->colegiadoRepository->findByNumeroColegiatura($numeroLimpio);
            }

            if (!$colegiado) {
                $tipoBusqueda = !empty($dni) ? 'DNI' : 'número de colegiatura';
                $this->json([
                    'success' => false,
                    'message' => "No se encontró ningún colegiado con ese {$tipoBusqueda}",
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