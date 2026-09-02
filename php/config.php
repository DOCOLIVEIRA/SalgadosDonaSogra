<?php
// ============================================================================
// config.php - Configurações do Sistema
// ============================================================================
// Coloque aqui as informações do seu painel HostGator.
// ============================================================================

// Defina as constantes para acesso ao banco de dados MySQL
define('DB_HOST', getenv('DB_HOST') ?: (file_exists('/.dockerenv') ? 'db' : 'localhost')); // 'db' se estiver dentro do container Docker
define('DB_USER', 'dougl628_root');      // Substitua pelo seu usuário do banco no cPanel
define('DB_PASS', 'donasogra@2026');          // Substitua pela senha do seu banco
define('DB_NAME', 'dougl628_dona_sogra'); // Substitua pelo nome do seu banco de dados

// Configurações do Painel
define('ADMIN_TITLE', 'Dona Sogra - Admin');
define('BASE_URL', '/'); // URL base da aplicação
?>
