<?php
class Database{
    private static $instance = null; // Instancia única de la clase (Patrón Singleton)
    private $connection = null; // Conexión PDO a la base de datos
    private $config = []; // Configuración de la base de datos

    // Constructor privado (Singleton)
    private function __construct(){
        $this->config = [
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'  => 'utf8mb4'
        ];

        $this->connect();
    }

    // Conexión con MySQL usando PDO
    private function connect(){
        try{
            // Nombre de Origen de Dato para MySQL
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            // Opciones
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en errores
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retornar arrays asociativos
                PDO::ATTR_EMULATE_PREPARES => false, // Usar prepared statements reales
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Establecer charset 
            ];

            // Crea conexión PDO
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            // Log de conexión exitosa
            if(env('APP_ENV') === 'development'){
                logMessage("Conexión a base de datos establecida correctamente", 'info');
            }

        } catch(PDOException $e){
            
            logMessage("Error de conexión a BD: " . $e->getMessage(), 'error');

            
            if (env('APP_ENV') === 'production') {
                die("Error: No se pudo conectar a la base de datos. Contacte al administrador.");
            } else {
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }

    // Obtiene la instancia única de Database (Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Obtiene la conexión PDO
    public function getConnection() {
        return $this->connection;
    }

    // Ejecuta una consulta SELECT y retorna todos los resultados
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logMessage("Error en query: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }

    // Ejecuta una consulta SELECT y retorna UN solo resultado
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            logMessage("Error en queryOne: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }

    // Ejecuta una consulta INSERT, UPDATE o DELETE
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            logMessage("Error en execute: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error al ejecutar: " . $e->getMessage());
        }
    }

    // Ejecuta un INSERT y retorna el ID insertado
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            logMessage("Error en insert: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error al insertar: " . $e->getMessage());
        }
    }

    // Inicia una transacción
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    // Confirma una transacción (COMMIT)
    public function commit() {
        return $this->connection->commit();
    }

    // Revierte una transacción (ROLLBACK)
    public function rollback() {
        return $this->connection->rollback();
    }

    // Verifica si hay una transacción activa
    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    // Cierra la conexión a la base de datos
    public function close() {
        $this->connection = null;
    }

    // Previene la clonación de la instancia (Singleton)
    private function __clone() {}

    // Previene la deserialización de la instancia (Singleton)
    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton");
    }
}

// Función helper global para obtener la conexión a la BD
function db() {
    return Database::getInstance()->getConnection();
}

// Función helper para obtener la instancia de Database
function database() {
    return Database::getInstance();
}