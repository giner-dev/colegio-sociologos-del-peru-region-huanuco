<?php
/**
 * Maneja la carga, validación y eliminación de archivos de comprobantes
 */

class FileUploadManager {
    
    private const UPLOAD_DIR = 'uploads/comprobantes/';
    private const MAX_FILE_SIZE = 5242880;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    
    private const MIME_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    /**
     * Sube un archivo de comprobante con validaciones completas
     */
    public static function uploadComprobante($file, $prefix = 'comprobante') {
        try {
            self::validateFile($file);
            
            $extension = self::getFileExtension($file['name']);
            $fileName = self::generateFileName($prefix, $extension);
            $destinationPath = self::getFullPath($fileName);
            
            self::ensureDirectoryExists();
            
            if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
                throw new Exception('Error al mover el archivo al directorio de destino');
            }
            
            chmod($destinationPath, 0644);
            
            $relativePath = self::getRelativePath($fileName);
            
            logMessage("Archivo subido exitosamente: {$relativePath}", 'info');
            
            return [
                'success' => true,
                'ruta' => $relativePath,
                'nombre_archivo' => $fileName,
                'tamano' => $file['size'],
                'extension' => $extension
            ];
            
        } catch (Exception $e) {
            logMessage("Error al subir archivo: {$e->getMessage()}", 'error');
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Elimina un archivo físico del servidor
     */
    public static function deleteComprobante($filePath) {
        if (empty($filePath)) {
            return ['success' => true, 'message' => 'No hay archivo para eliminar'];
        }
        
        $fullPath = self::resolveFullPath($filePath);
        
        logMessage("Resolviendo eliminación - Path recibido: '{$filePath}' | Path completo: '{$fullPath}'", 'info');
        
        if (!file_exists($fullPath)) {
            logMessage("Archivo no encontrado en: {$fullPath}", 'warning');
            return ['success' => true, 'message' => 'El archivo no existe'];
        }
        
        try {
            if (unlink($fullPath)) {
                logMessage("Archivo eliminado: {$fullPath}", 'info');
                return ['success' => true, 'message' => 'Archivo eliminado correctamente'];
            }
            
            throw new Exception('No se pudo eliminar el archivo');
            
        } catch (Exception $e) {
            logMessage("Error al eliminar archivo {$fullPath}: {$e->getMessage()}", 'error');
            
            return [
                'success' => false,
                'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Resuelve la ruta completa del archivo manejando diferentes formatos
     */
    private static function resolveFullPath($filePath) {
        $filePath = str_replace('\\', '/', $filePath);
        
        if (strpos($filePath, 'public/uploads/') === 0) {
            return basePath($filePath);
        }
        
        if (strpos($filePath, 'uploads/') === 0) {
            return basePath('public/' . $filePath);
        }
        
        return basePath('public/uploads/comprobantes/' . basename($filePath));
    }
    
    /**
     * Valida un archivo antes de subirlo
     */
    private static function validateFile($file) {
        if (!isset($file) || empty($file['name'])) {
            throw new Exception('No se ha seleccionado ningún archivo');
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(self::getUploadErrorMessage($file['error']));
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $maxSizeMB = self::MAX_FILE_SIZE / 1048576;
            throw new Exception("El archivo excede el tamaño máximo permitido de {$maxSizeMB}MB");
        }
        
        $extension = self::getFileExtension($file['name']);
        
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $allowedList = implode(', ', array_map('strtoupper', self::ALLOWED_EXTENSIONS));
            throw new Exception("Tipo de archivo no permitido. Formatos aceptados: {$allowedList}");
        }
        
        if (!self::validateMimeType($file['tmp_name'], $extension)) {
            throw new Exception('El tipo MIME del archivo no coincide con su extensión');
        }
        
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('El archivo no fue subido mediante POST');
        }
    }
    
    /**
     * Valida que el tipo MIME coincida con la extensión
     */
    private static function validateMimeType($filePath, $extension) {
        if (!function_exists('finfo_open')) {
            return true;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        $expectedMime = self::MIME_TYPES[$extension] ?? null;
        
        if (!$expectedMime) {
            return true;
        }
        
        return $mimeType === $expectedMime || 
               ($extension === 'jpg' && $mimeType === 'image/jpeg') ||
               ($extension === 'jpeg' && $mimeType === 'image/jpeg');
    }
    
    /**
     * Genera un nombre único para el archivo
     */
    private static function generateFileName($prefix, $extension) {
        $timestamp = date('Ymd_His');
        $random = bin2hex(random_bytes(8));
        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Obtiene la extensión del archivo de forma segura
     */
    private static function getFileExtension($fileName) {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }
    
    /**
     * Obtiene la ruta completa del archivo
     */
    private static function getFullPath($fileName) {
        return basePath('public/' . self::UPLOAD_DIR . $fileName);
    }
    
    /**
     * Obtiene la ruta relativa del archivo
     */
    private static function getRelativePath($fileName) {
        return self::UPLOAD_DIR . $fileName;
    }
    
    /**
     * Asegura que el directorio de uploads existe
     */
    private static function ensureDirectoryExists() {
        $directory = basePath('public/' . self::UPLOAD_DIR);
        
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de uploads');
            }
        }
        
        if (!is_writable($directory)) {
            throw new Exception('El directorio de uploads no tiene permisos de escritura');
        }
    }
    
    /**
     * Traduce códigos de error de PHP a mensajes legibles
     */
    private static function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida del archivo'
        ];
        
        return $errors[$errorCode] ?? 'Error desconocido al subir el archivo';
    }
    
    /**
     * Obtiene información de un archivo existente
     */
    public static function getFileInfo($filePath) {
        if (empty($filePath)) {
            return null;
        }
        
        $fullPath = self::resolveFullPath($filePath);
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        return [
            'exists' => true,
            'size' => filesize($fullPath),
            'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
            'modified' => filemtime($fullPath),
            'readable' => is_readable($fullPath)
        ];
    }
}

/**
 * Funciones helper globales
 */
function subirComprobante($file, $prefix = 'comprobante') {
    return FileUploadManager::uploadComprobante($file, $prefix);
}

function eliminarComprobante($filePath) {
    return FileUploadManager::deleteComprobante($filePath);
}

function obtenerInfoArchivo($filePath) {
    return FileUploadManager::getFileInfo($filePath);
}