<?php
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';

class ColegiadoService{
    private $colegiadoRepository;

    public function __construct(){
        $this->colegiadoRepository = new ColegiadoRepository();
    }

    public function obtenerTodosPaginado($pagina = 1, $porPagina = 20) {
        return $this->colegiadoRepository->findAllPaginated($pagina, $porPagina);
    }

    public function buscarPaginado($filtros, $pagina = 1, $porPagina = 20) {
        return $this->colegiadoRepository->buscarPaginated($filtros, $pagina, $porPagina);
    }

    public function obtenerTodos(){
        return $this->colegiadoRepository->findAll();
    }

    public function obtenerPorId($id){
        return $this->colegiadoRepository->findById($id);
    }

    public function buscar($filtros) {
        return $this->colegiadoRepository->buscar($filtros);
    }

    public function crear($datos) {
        // Si no viene número de colegiatura, generar uno automáticamente
        if (empty($datos['numero_colegiatura'])) {
            $datos['numero_colegiatura'] = $this->colegiadoRepository->generarNumeroColegiatura();
        }

        // validar datos
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        // verificar que no exista el número de colegiatura
        if ($this->colegiadoRepository->existeNumeroColegiatura($datos['numero_colegiatura'])) {
            return ['success' => false, 'errors' => ['El número de colegiatura ya existe']];
        }
        
        // verificar que no exista el DNI
        if ($this->colegiadoRepository->existeDni($datos['dni'])) {
            return ['success' => false, 'errors' => ['El DNI ya está registrado']];
        }
        
        // preparar datos
        $datosInsert = [
            'numero_colegiatura' => $datos['numero_colegiatura'],
            'dni' => $datos['dni'],
            'nombres' => $datos['nombres'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'],
            'fecha_colegiatura' => $datos['fecha_colegiatura'],
            'telefono' => $datos['telefono'] ?? null,
            'correo' => $datos['correo'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'estado' => 'inhabilitado',
            'observaciones' => $datos['observaciones'] ?? null
        ];
        
        // insertar en BD
        $id = $this->colegiadoRepository->create($datosInsert);
        
        if ($id) {
            logMessage("Colegiado creado: ID $id - {$datos['numero_colegiatura']}", 'info');
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'errors' => ['Error al crear el colegiado']];
    }

    public function actualizar($id, $datos) {
        // Verificar que existe
        $colegiado = $this->colegiadoRepository->findById($id);
        if (!$colegiado) {
            return ['success' => false, 'errors' => ['Colegiado no encontrado']];
        }
        
        // Validar datos
        $errores = $this->validarDatos($datos, $id);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        // Verificar número de colegiatura único
        if ($this->colegiadoRepository->existeNumeroColegiatura($datos['numero_colegiatura'], $id)) {
            return ['success' => false, 'errors' => ['El número de colegiatura ya existe']];
        }
        
        // Verificar DNI único
        if ($this->colegiadoRepository->existeDni($datos['dni'], $id)) {
            return ['success' => false, 'errors' => ['El DNI ya está registrado']];
        }
        
        // Preparar datos
        $datosUpdate = [
            'numero_colegiatura' => $datos['numero_colegiatura'],
            'dni' => $datos['dni'],
            'nombres' => $datos['nombres'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'],
            'fecha_colegiatura' => $datos['fecha_colegiatura'],
            'telefono' => $datos['telefono'] ?? null,
            'correo' => $datos['correo'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null
        ];
        
        // Actualizar en BD
        $resultado = $this->colegiadoRepository->update($id, $datosUpdate);
        
        if ($resultado) {
            logMessage("Colegiado actualizado: ID $id", 'info');
            return ['success' => true];
        }
        
        return ['success' => false, 'errors' => ['Error al actualizar el colegiado']];
    }

    public function cambiarEstado($id, $nuevoEstado, $motivo, $usuarioId) {
        $colegiado = $this->colegiadoRepository->findById($id);
        
        if (!$colegiado) {
            return ['success' => false, 'message' => 'Colegiado no encontrado'];
        }
        
        if ($colegiado->estado === $nuevoEstado) {
            return ['success' => false, 'message' => 'El colegiado ya tiene ese estado'];
        }
        
        // Cambiar estado
        $this->colegiadoRepository->cambiarEstado($id, $nuevoEstado, $motivo);
        
        // Registrar en historial
        $this->registrarCambioEstado($id, $colegiado->estado, $nuevoEstado, $motivo, 'manual', $usuarioId);
        
        logMessage("Estado cambiado para colegiado ID $id: {$colegiado->estado} -> $nuevoEstado", 'info');
        
        return ['success' => true, 'message' => 'Estado actualizado correctamente'];
    }

    private function registrarCambioEstado($colegiadoId, $estadoAnterior, $estadoNuevo, $motivo, $tipoCambio, $usuarioId = null) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO historial_estados (colegiado_id, estado_anterior, estado_nuevo, motivo, tipo_cambio, usuario_id)
                VALUES (:colegiado_id, :estado_anterior, :estado_nuevo, :motivo, :tipo_cambio, :usuario_id)";
        
        $db->execute($sql, [
            'colegiado_id' => $colegiadoId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'tipo_cambio' => $tipoCambio,
            'usuario_id' => $usuarioId
        ]);
    }

    public function obtenerInfoCompleta($id) {
        $colegiado = $this->colegiadoRepository->findById($id);
        
        if (!$colegiado) {
            return null;
        }
        
        return [
            'colegiado' => $colegiado,
            'historial_estados' => $this->colegiadoRepository->getHistorialEstados($id),
            'historial_pagos' => $this->colegiadoRepository->getHistorialPagos($id),
            'deudas' => $this->colegiadoRepository->getDeudas($id)
        ];
    }

    private function validarDatos($datos, $excludeId = null) {
        $errores = [];

        // agrega validación para que no sean null
        $datos['apellido_materno'] = $datos['apellido_materno'] ?? '';
        $datos['apellido_paterno'] = $datos['apellido_paterno'] ?? '';
        $datos['nombres'] = $datos['nombres'] ?? '';
        
        //if (empty($datos['numero_colegiatura'])) {
        //    $errores[] = 'El número de colegiatura es obligatorio';
        //}
        
        if (empty($datos['dni'])) {
            $errores[] = 'El DNI es obligatorio';
        } elseif (strlen($datos['dni']) != 8) {
            $errores[] = 'El DNI debe tener 8 dígitos';
        }
        
        if (empty($datos['nombres'])) {
            $errores[] = 'Los nombres son obligatorios';
        }
        
        if (empty($datos['apellido_paterno'])) {
            $errores[] = 'El apellido paterno es obligatorio';
        }
        
        if (empty($datos['apellido_materno'])) {
            $errores[] = 'El apellido materno es obligatorio';
        }
        
        if (empty($datos['fecha_colegiatura'])) {
            $errores[] = 'La fecha de colegiatura es obligatoria';
        }
        
        if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido';
        }
        
        return $errores;
    }

    public function subirFoto($id, $archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
        
        $extensionesPermitidas = ['jpg', 'jpeg', 'png'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionesPermitidas)) {
            return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG o PNG'];
        }
        
        $nombreArchivo = 'colegiado_' . $id . '_' . time() . '.' . $extension;
        $rutaDestino = basePath('public/uploads/fotos/' . $nombreArchivo);
        
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $this->colegiadoRepository->updateFoto($id, 'uploads/fotos/' . $nombreArchivo);
            return ['success' => true, 'ruta' => 'uploads/fotos/' . $nombreArchivo];
        }
        
        return ['success' => false, 'message' => 'Error al guardar el archivo'];
    }
}