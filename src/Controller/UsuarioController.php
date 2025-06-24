<?php

namespace Joabe\Buscaprecos\Controller;

class UsuarioController
{
    /**
     * Exibe a página de login.
     */
    public function exibirFormularioLogin($request, $response, $args)
    {
        // Esta view não usará o layout principal, será uma página simples
        ob_start();
        require __DIR__ . '/../View/usuarios/login.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Processa a tentativa de login do usuário.
     */
    public function processarLogin($request, $response, $args)
    {
        $dados = $request->getParsedBody();
        $email = $dados['email'] ?? '';
        $senha = $dados['senha'] ?? '';

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verifica se o usuário existe e se a senha está correta
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido: armazena os dados do usuário na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            
            // Redireciona para o dashboard
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        // Falha no login: redireciona de volta com uma mensagem de erro
        $_SESSION['flash_error'] = 'E-mail ou senha inválidos.';
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    /**
     * Faz o logout do usuário.
     */
    public function processarLogout($request, $response, $args)
    {
        // Destrói todas as variáveis da sessão
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Redireciona para a página de login
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}