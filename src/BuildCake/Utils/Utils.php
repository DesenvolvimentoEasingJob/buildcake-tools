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
            return $filepath;
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
     * Inclui um arquivo de serviço baseado no módulo
     * 
     * @param string $filepath Nome do arquivo de serviço (sem extensão)
     * @param string $module Nome do módulo (opcional, detecta automaticamente se vazio)
     * @return mixed Retorna o resultado do include ou string vazia se não encontrado
     */
    public static function IncludeService($service, $module)
    {
        $filepath = self::ReturnPathFile("src/{$module}/services/{$service}Service.php");
        if(file_exists($filepath)) {
            include_once $filepath;
            return true;
        } else {
            self::sendResponse(404, [], 'Service not found');
        }
        // Se não encontrou, retorna vazio
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
    
        echo json_encode($response);
        exit;
    }

    /**
     * Processa a requisição e retorna informações sobre o arquivo/rota solicitada
     * 
     * @return array Array com informações sobre file, route, id e type
     */
    public static function includeFileRequest($root = "src",$folder = "controllers",$file = "Controller.php")
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            self::sendResponse(404, [], 'API not found');
        }

        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $parms = explode('?', $uri);
        $segments = explode('/', $parms[0]);

        if(count($segments) < 2) {
            self::sendResponse(404, [], 'API not found');
        }

        $module = $segments[count($segments) - 2];
        $controller = $segments[count($segments) - 1];
        $path = "{$root}/{$module}/{$folder}/{$controller}{$file}";

        if (file_exists($path)) {
            include_once $path;
        } else {
            self::sendResponse(404, [], 'API not found');
        }
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

