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

        $dni = $this->getQuery('dni');
        $numeroColegiatura = $this->getQuery('numero_colegiatura');

        if (empty($dni) && empty($numeroColegiatura)) {
            $this->json([
                'success' => false,
                'message' => 'Debe ingresar un DNI o Número de Colegiatura'
            ]);
            return;
        }

        try {
            $colegiado = null;

            if (!empty($dni)) {
                if (strlen($dni) !== 8 || !ctype_digit($dni)) {
                    $this->json([
                        'success' => false,
                        'message' => 'El DNI debe tener 8 dígitos numéricos'
                    ]);
                    return;
                }

                $colegiado = $this->colegiadoRepository->findByDni($dni);
            }
            else if (!empty($numeroColegiatura)) {
                $numeroLimpio = ltrim($numeroColegiatura, '0');

                if (empty($numeroLimpio)) {
                    $numeroLimpio = '0';
                }

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

            // MODIFICACIÓN: Determinar texto de estado según el nuevo campo
            $estadoTexto = 'NO HABILITADO';
            if ($colegiado->estado === 'habilitado') {
                $estadoTexto = 'HABILITADO';
            } elseif ($colegiado->estado === 'inactivo_cese') {
                $estadoTexto = 'INACTIVO POR CESE';
            }

            // Preparar respuesta con datos públicos
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
                    'estado_texto' => $estadoTexto,
                    'fecha_colegiatura' => formatDate($colegiado->fecha_colegiatura),
                    'fecha_cese' => $colegiado->fecha_cese ? formatDate($colegiado->fecha_cese) : null
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