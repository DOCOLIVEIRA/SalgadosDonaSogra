<?php
// ============================================================================
// db.php - Central de Conexão com o Banco de Dados (MySQL / PDO)
// ============================================================================
// Abre a conexão com o banco e lida com erros de forma limpa.
// Usamos PDO pois previne Injeção de SQL e funciona muito bem com mysql.
// ============================================================================

require_once 'config.php';

function get_connection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            // DSN (Data Source Name)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra os erros SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna resultados como dicionários (arrays associativos do PHP)
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Usar prepared statements reais do banco
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Em produção na HostGator, talvez não queiramos exibir o erro completo.
            die("Erro crítico de conexão com o banco de dados. Verifique suas credenciais no config.php. Erro: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>
