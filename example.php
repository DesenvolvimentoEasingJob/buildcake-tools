<?php

/**
 * Exemplo de uso da biblioteca BuildCake Utils
 * 
 * Para usar este exemplo:
 * 1. Execute: composer install
 * 2. Execute: php example.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use BuildCake\Utils\Utils;

echo "=== Exemplos de uso da biblioteca BuildCake Utils ===\n\n";

// Exemplo 1: Substituir campos em texto
echo "1. Substituição de campos:\n";
$template = "Olá {{nome}}, seu email é {{email}} e você tem {{idade}} anos.";
$data = [
    'nome' => 'João Silva',
    'email' => 'joao@example.com',
    'idade' => '30'
];
$resultado = Utils::replaceFields($template, $data);
echo "   Template: $template\n";
echo "   Resultado: $resultado\n\n";

// Exemplo 2: Carregar variáveis de ambiente (se existir arquivo .env)
echo "2. Carregamento de variáveis de ambiente:\n";
if (file_exists(__DIR__ . '/.env.example')) {
    try {
        Utils::loadEnv(__DIR__ . '/.env.example');
        echo "   Variáveis de ambiente carregadas com sucesso!\n";
    } catch (\Exception $e) {
        echo "   Erro: " . $e->getMessage() . "\n";
    }
} else {
    echo "   Arquivo .env não encontrado. Criando exemplo...\n";
    // Criar arquivo .env.example para demonstração
    file_put_contents(__DIR__ . '/.env.example', "DB_HOST=localhost\nDB_NAME=testdb\nDB_USER=root\n");
    echo "   Arquivo .env.example criado!\n";
}
echo "\n";

// Exemplo 3: Processar requisição (simulação)
echo "3. Processamento de requisição:\n";
// Simular uma requisição
$_SERVER['REQUEST_URI'] = '/api/users/123';
$request = Utils::getFileRequest();
echo "   URI: /api/users/123\n";
echo "   Resultado: " . json_encode($request, JSON_PRETTY_PRINT) . "\n\n";

// Exemplo 4: Resposta JSON (não executado para não encerrar o script)
echo "4. Envio de resposta JSON:\n";
echo "   Utils::sendResponse(200, ['data' => 'exemplo'], 'Sucesso');\n";
echo "   (Não executado para manter o exemplo rodando)\n\n";

echo "=== Fim dos exemplos ===\n";

