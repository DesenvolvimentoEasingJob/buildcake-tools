# BuildCake Utils

Biblioteca PHP de utilitários para operações comuns do sistema.

## Instalação

### Via Composer

```bash
composer require buildcake/tools
```

### Instalação Manual

1. Clone ou baixe este repositório
2. Inclua o autoloader do Composer:

```php
require_once 'vendor/autoload.php';
```

## Requisitos

- PHP >= 7.4

## Uso

### Carregar Variáveis de Ambiente

```php
use BuildCake\Utils\Utils;

// Carrega variáveis de um arquivo .env
Utils::loadEnv(__DIR__ . '/.env');

// Agora você pode usar getenv() ou $_ENV
$dbHost = getenv('DB_HOST');
```

### Enviar Resposta JSON

```php
use BuildCake\Utils\Utils;

// Resposta de sucesso
Utils::sendResponse(200, ['user' => ['id' => 1, 'name' => 'João']], 'Usuário encontrado');

// Resposta de erro
Utils::sendResponse(400, [], 'Dados inválidos', ['email' => 'Email é obrigatório']);
```

### Incluir Arquivos

```php
use BuildCake\Utils\Utils;

// Inclui um arquivo (procura recursivamente se não encontrar)
Utils::includeFile('config/database.php');

// Inclui um serviço de um módulo específico
Utils::includeService('User', 'Auth'); // Procura por Auth/services/UserService.php
```

### Substituir Campos em Texto

```php
use BuildCake\Utils\Utils;

$template = "Olá {{nome}}, seu email é {{email}}";
$data = [
    'nome' => 'João',
    'email' => 'joao@example.com'
];

$resultado = Utils::replaceFields($template, $data);
// Resultado: "Olá João, seu email é joao@example.com"
```

### Processar Requisições

```php
use BuildCake\Utils\Utils;

$request = Utils::getFileRequest();
// Retorna array com informações sobre file, route, id e type
```

## Métodos Disponíveis

### `includeFile(string $filepath): mixed`
Inclui um arquivo procurando recursivamente no sistema de diretórios.

### `includeService(string $filepath, string $module = ""): mixed`
Inclui um arquivo de serviço baseado no módulo especificado.

### `loadEnv(string $path): void`
Carrega variáveis de ambiente de um arquivo .env.

### `sendResponse(int $statusCode, array $data, string $message = '', array $errors = []): void`
Envia uma resposta JSON padronizada e encerra a execução.

### `getFileRequest(): array`
Processa a requisição e retorna informações sobre o arquivo/rota solicitada.

### `replaceFields(string $text, array|object $fields): string`
Substitui campos em um texto usando placeholders no formato `{{campo}}`.

## Estrutura do Projeto

```
buildcake-tools/
├── src/
│   └── BuildCake/
│       └── Utils/
│           └── Utils.php
├── composer.json
├── README.md
└── .gitignore
```

## Licença

MIT

## Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou pull requests.

