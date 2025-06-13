<?php

// Habilita a exibição de todos os erros (bom para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega o autoloader do Composer
require __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env que criamos
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/**
 * Função para retornar uma instância da conexão PDO com o banco de dados.
 * @return PDO
 */
function getDbConnection(): PDO
{
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_DATABASE'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASSWORD'];
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna os resultados como arrays associativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa 'prepared statements' reais
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Em um ambiente de produção, você logaria este erro em vez de exibi-lo
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}