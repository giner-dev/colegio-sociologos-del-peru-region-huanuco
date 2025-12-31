<?php
/**
 * MOTOR DE VISTAS
 * Maneja la carga y renderizado de archivos de vista
 */

class View{
    private $viewsPath;
    private $layout = 'main';
    private $sharedData = [];
    private $sections = [];
    private $currentSection = null;

    public function __construct(){
        $this->viewsPath = basePath('app/views/');
    }

    /**
     * Renderiza una vista
     * 
     * FUNCIONAMIENTO:
     * 1. Carga el archivo de vista
     * 2. Le pasa los datos
     * 3. Lo envuelve en un layout (header, sidebar, footer)
     * 
     * EJEMPLO DE USO:
     * $view->render('colegiados/index', ['colegiados' => $lista]);
     */
    public function render($viewPath, $data = [], $layout = null){
        $layout = $layout !== null ? $layout : $this->layout;
        $data = array_merge($this->sharedData, $data);
        extract($data); // Extraer datos como variables individuales
        ob_start();

        $viewFile = $this->viewsPath . str_replace('.', '/', $viewPath) . '.php';

        if(!file_exists($viewFile)){
            throw new Exception("Vista no encontrada: $viewFile");
        }

        include $viewFile;

        // Obtener el contenido capturado
        $content = ob_get_clean();
        
        // Si hay layout, envolver el contenido
        if ($layout) {
            $this->renderWithLayout($content, $layout, $data);
        } else {
            echo $content;
        }
    }

    // Renderiza el contenido dentro de un layout
    private function renderWithLayout($content, $layout, $data) {
        extract($data);
        
        $layoutFile = $this->viewsPath . 'layouts/' . $layout . '.php';
        
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout no encontrado: $layoutFile");
        }
        
        // $content estará disponible en el layout
        include $layoutFile;
    }

    // Establece el layout por defecto
    public function setLayout($layout) {
        $this->layout = $layout;
    }

    // Comparte datos con todas las vistas
    public function share($key, $value) {
        $this->sharedData[$key] = $value;
    }

    // Incluye una vista parcial dentro de otra vista
    public function partial($partialPath, $data = []) {
        extract($data);
        
        $partialFile = $this->viewsPath . str_replace('.', '/', $partialPath) . '.php';
        
        if (!file_exists($partialFile)) {
            throw new Exception("Parcial no encontrado: $partialFile");
        }
        
        include $partialFile;
    }

    // Comprueba si una vista existe
    public function exists($viewPath) {
        $viewFile = $this->viewsPath . str_replace('.', '/', $viewPath) . '.php';
        return file_exists($viewFile);
    }

    // Renderiza una sección del layout
    public function section($name) {
        if (isset($this->sections[$name])) {
            echo $this->sections[$name];
        }
    }

    // Define el inicio de una sección
    public function startSection($name) {
        $this->currentSection = $name;
        ob_start();
    }

    // Finaliza una sección
    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
}

// Función helper global para renderizar vistas rápidamente
function view($viewPath, $data = []) {
    $view = new View();
    $view->render($viewPath, $data);
}