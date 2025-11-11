<?php

namespace BuildCake\Utils;

/**
 * Classe Utils - Utilitários comuns do sistema
 * 
 * Fornece métodos estáticos para operações comuns como inclusão de arquivos,
 * carregamento de variáveis de ambiente, respostas HTTP, e manipulação de texto.
 */
class Utils
{
        /**
     * Inclui um arquivo procurando recursivamente no sistema de diretórios
     * 
     * @param string $filepath Caminho do arquivo a ser incluído
     * @return mixed Retorna o resultado do include ou string vazia se não encontrado
     */
    public static function ReturnPathFile($filepath)
    {
        if (file_exists($filepath)) {
            return include_once($filepath);
        }

        $filepathConcat = "/" . $filepath;
        $count = 0;
        $allcount = 0;

        while (!file_exists($filepathConcat)) {
            if ($count < 2) {
                $filepathConcat = "." . $filepathConcat;
                $count = $count + 1;
            } else {
                $count = 0;
                $filepathConcat = "/" . $filepathConcat;
            }

            if ($allcount > 32) {
                return "";
            }

            $allcount = $allcount + 1;
        }

        return $filepathConcat;
    }

    /**
     * Inclui um arquivo procurando recursivamente no sistema de diretórios
     * 
     * @param string $filepath Caminho do arquivo a ser incluído
     * @return mixed Retorna o resultado do include ou string vazia se não encontrado
     */
    public static function includeFile($filepath)
    {
        if (file_exists($filepath)) {
            return include_once($filepath);
        }

        $filepathConcat = "/" . $filepath;
        $count = 0;
        $allcount = 0;

        while (!file_exists($filepathConcat)) {
            if ($count < 2) {
                $filepathConcat = "." . $filepathConcat;
                $count = $count + 1;
            } else {
                $count = 0;
                $filepathConcat = "/" . $filepathConcat;
            }

            if ($allcount > 32) {
                return "";
            }

            $allcount = $allcount + 1;
        }

        return include_once($filepathConcat);
    }

    /**
     * Inclui um arquivo de serviço baseado no módulo
     * 
     * @param string $filepath Nome do arquivo de serviço (sem extensão)
     * @param string $module Nome do módulo (opcional, detecta automaticamente se vazio)
     * @return mixed Retorna o resultado do include ou string vazia se não encontrado
     */
    public static function includeService($filepath, $module = "")
    {
        if ($module == "") {
            $backtrace = debug_backtrace();
            if (isset($backtrace[0]["file"])) {
                $pathParts = explode("/", $backtrace[0]["file"]);
                $module = isset($pathParts[5]) ? $pathParts[5] : "";
            }
        }

        // Normalizar o nome do módulo para case-insensitive
        $module = self::normalizeModuleName($module);
        
        $filepath = "/src/" . $module . "/services/" . $filepath . "Service.php";
        
        // Tentar encontrar o arquivo de forma case-insensitive
        $actualPath = self::findFileCaseInsensitive($filepath);
        if ($actualPath) {
            return include_once($actualPath);
        }

        // Fallback para o método original de busca recursiva
        $filepathConcat = "/" . $filepath;
        $count = 0;
        $allcount = 0;

        while (!file_exists($filepathConcat)) {
            if ($count < 2) {
                $filepathConcat = "." . $filepathConcat;
                $count = $count + 1;
            } else {
                $count = 0;
                $filepathConcat = "/" . $filepathConcat;
            }

            if ($allcount > 32) {
                return "";
            }

            $allcount = $allcount + 1;
        }

        return include_once($filepathConcat);
    }

    /**
     * Encontra um arquivo de forma case-insensitive
     * 
     * @param string $filepath Caminho do arquivo
     * @return string|false Retorna o caminho real do arquivo ou false se não encontrado
     */
    private static function findFileCaseInsensitive($filepath)
    {
        // Se o arquivo existe exatamente como especificado, retorna o caminho
        if (file_exists($filepath)) {
            return $filepath;
        }

        // Extrair diretório e nome do arquivo
        $pathInfo = pathinfo($filepath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['basename'];

        // Se o diretório não existe, tentar encontrar uma variação case-insensitive
        if (!is_dir($directory)) {
            $parentDir = dirname($directory);
            $targetDir = basename($directory);
            
            if (is_dir($parentDir)) {
                $actualDir = self::findDirectoryCaseInsensitive($parentDir, $targetDir);
                if ($actualDir) {
                    $directory = $actualDir;
                } else {
                    return false; // Diretório não encontrado
                }
            } else {
                return false;
            }
        }

        // Procurar pelo arquivo no diretório encontrado
        if (is_dir($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if (strcasecmp($file, $filename) === 0) {
                    return $directory . '/' . $file;
                }
            }
        }

        return false;
    }

    /**
     * Encontra um diretório de forma case-insensitive
     * 
     * @param string $parentDir Diretório pai
     * @param string $targetDir Nome do diretório alvo
     * @return string|false Retorna o caminho real do diretório ou false se não encontrado
     */
    private static function findDirectoryCaseInsensitive($parentDir, $targetDir)
    {
        if (!is_dir($parentDir)) {
            return false;
        }

        $items = scandir($parentDir);
        foreach ($items as $item) {
            if (is_dir($parentDir . '/' . $item) && strcasecmp($item, $targetDir) === 0) {
                return $parentDir . '/' . $item;
            }
        }

        return false;
    }

    /**
     * Carrega variáveis de ambiente de um arquivo .env
     * 
     * @param string $path Caminho para o arquivo .env
     * @return void
     * @throws \Exception Se o arquivo não for encontrado
     */
    public static function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Arquivo .env não encontrado em: " . $path);
        }
    
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove aspas se existirem
                $value = trim($value, '"\'');
                
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    /**
     * Envia uma resposta JSON padronizada e encerra a execução
     * 
     * @param int $statusCode Código de status HTTP
     * @param array $data Dados da resposta
     * @param string $message Mensagem da resposta (opcional)
     * @param array $errors Lista de erros (opcional)
     * @return void
     */
    public static function sendResponse(int $statusCode, array $data, string $message = '', array $errors = []): void
    {
        http_response_code($statusCode);
    
        $response = [
            'status' => $statusCode,
            'message' => $message ?: ($statusCode >= 200 && $statusCode < 300 ? 'Success' : 'Error'),
            'data' => $data,
            'errors' => $errors
        ];
    
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * Processa a requisição e retorna informações sobre o arquivo/rota solicitada
     * 
     * @return array Array com informações sobre file, route, id e type
     */
    public static function getFileRequest()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return [
                "file" => "",
                "route" => "",
                "id" => "",
                "type" => "",
            ];
        }

        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $parms = explode('?', $uri);
        $path = $parms[0];

        // Static assets only (CSS, JS, images, fonts)
        if (preg_match('/\.(js|css|png|jpg|jpeg|svg|woff2?|ttf|map|ico|gif|webp)$/', $path)) {
            return [
                "file" => "public/" . $path,
                "route" => "static_asset",
                "type" => "static_asset"
            ];
        }

        // Default: API logic
        return self::handleApiRoute($uri);
    }

    /**
     * Processa rotas de API
     * 
     * @param string $uri URI da requisição
     * @return array Array com informações sobre file, route, id e type
     */
    private static function handleApiRoute($uri)
    {
        $retorno = [
            "file" => "",
            "route" => "",
            "id" => "",
            "type" => "",
        ];

        $uri = explode('?', $uri)[0];
        $segments = explode('/', $uri);
        $id = end($segments);

        if (is_numeric($id)) {
            $id = intval($id);
        } elseif (preg_match('/^[a-f0-9]{11,32}$/i', $id)) {
            $id = $id;
        } else {
            $id = "";
        }

        if(isset($_GET['id'])) { 
            $id = $_GET['id']; 
        }

        if (count($segments) >= 2) {
            $module = $segments[1];
            $controller = $segments[2];
            
            // Normalize module name to match directory structure (case-insensitive)
            $module = self::normalizeModuleName($module);
            
            // Normalize controller name (case-insensitive)
            $apiFile = self::normalizeControllerName($controller);
            
            $apiPath = self::ReturnPathFile("/src/$module/controllers/$apiFile");

            $retorno["file"] = $apiPath;
            $retorno["route"] = "$module/" . $controller;
            $retorno["id"] = $id;
            $retorno["type"] = "api";
            
            if($id != ""){
                $_GET['id'] = $id;
                if(is_array($_POST)){
                    $_POST["id"] = $id;
                }else{
                    $_POST = [ "id" => $id ];
                }
            }
        }

        return $retorno;
    }

    /**
     * Normaliza o nome do módulo para corresponder à estrutura de diretórios (case-insensitive)
     * 
     * @param string $module Nome do módulo
     * @return string Nome do módulo normalizado
     */
    private static function normalizeModuleName($module)
    {
        $module = strtolower($module);
        $srcPath = __DIR__ . '/../../../../src';
    
        if (is_dir($srcPath)) {
            foreach (scandir($srcPath) as $entry) {
                $fullPath = $srcPath . '/' . $entry;
                if (is_dir($fullPath) && strtolower($entry) === $module) {
                    return $entry; // Retorna o nome exato da pasta com a capitalização correta
                }
            }
        }
    
        // Se não encontrou, retorna ucfirst como fallback
        return ucfirst($module);
    }

    /**
     * Normaliza o nome do controller para corresponder à estrutura de arquivos (case-insensitive)
     * 
     * @param string $controller Nome do controller
     * @return string Nome do controller normalizado
     */
    private static function normalizeControllerName($controller)
    {
        $controller = strtolower($controller);
        $srcPath = __DIR__ . '/../../../../src';
        $controllers = glob($srcPath . '/*/controllers/*.php');
    
        foreach ($controllers as $file) {
            $baseName = basename($file);
            $cleanName = strtolower(str_replace('Controller.php', '', $baseName));
    
            if ($cleanName === $controller) {
                return $baseName; // Retorna o nome real com capitalização exata
            }
        }
    
        // Se não encontrou, retorna fallback
        return ucfirst($controller) . 'Controller.php';
    }

    /**
     * Substitui campos em um texto usando placeholders no formato {{campo}}
     * 
     * @param string $text Texto com placeholders
     * @param array|object $fields Array ou objeto com os valores para substituição
     * @return string Texto com os placeholders substituídos
     */
    public static function replaceFields($text, $fields)
    {
        $objects = json_decode(json_encode($fields), true);

        foreach ($objects as $key => $value) {
            $text = str_replace("{{" . trim($key) . "}}", trim($value), $text);
        }

        return $text;
    }
}

