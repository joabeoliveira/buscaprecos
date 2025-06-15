<?php

namespace Joabe\Buscaprecos\Controller;

class ProcessoController
{
        public function listar($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT * FROM processos ORDER BY data_criacao DESC");
        $processos = $stmt->fetchAll();

        // Prepara as variáveis para o layout principal
        $tituloPagina = "Lista de Processos";
        
        // --- A CORREÇÃO ESTÁ AQUI ---
        // Garante que estamos apontando para a view correta da lista de PROCESSOS.
        $paginaConteudo = __DIR__ . '/../View/processos/lista.php';

        // Renderiza o layout principal, que por sua vez incluirá a nossa lista
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    // --- CORREÇÃO APLICADA AQUI ---
        public function exibirFormulario($request, $response, $args)
    {
        // Prepara as variáveis para o layout principal
        $tituloPagina = "Novo Processo";
        // Define o arquivo de conteúdo que o layout principal vai incluir
        $paginaConteudo = __DIR__ . '/../View/processos/formulario.php';

        // Renderiza o layout principal
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }
    // --- FIM DA CORREÇÃO ---

    public function criar($request, $response, $args)
{
    $dados = $request->getParsedBody();

    // **ERRO CORRIGIDO AQUI**: Faltavam as colunas 'agente_responsavel' e 'uasg'.
    $sql = "INSERT INTO processos (numero_processo, nome_processo, tipo_contratacao, status, agente_responsavel, uasg) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);

    // Agora a quantidade de parâmetros está correta.
    $stmt->execute([
        $dados['numero_processo'],
        $dados['nome_processo'],
        $dados['tipo_contratacao'],
        $dados['status'],
        $dados['agente_responsavel'], 
        $dados['uasg']                
    ]);

    // **AJUSTE**: Redirecionar para a lista de processos é mais intuitivo que para o dashboard.
    return $response->withHeader('Location', '/processos')->withStatus(302);
}


    public function exibirFormularioEdicao($request, $response, $args)
    {
        $id = $args['id'];
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmt->execute([$id]);
        $processo = $stmt->fetch();

        if (!$processo) {
            $response->getBody()->write("Processo não encontrado.");
            return $response->withStatus(404);
        }
        
        // Prepara as variáveis para o layout principal
        $tituloPagina = "Editar Processo";
        $paginaConteudo = __DIR__ . '/../View/processos/formulario_edicao.php';

        // Renderiza o layout principal
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    // NOVO MÉTODO: Salva as alterações no banco de dados
    public function atualizar($request, $response, $args)
{
    $id = $args['id'];
    $dados = $request->getParsedBody();

    // **ERRO CORRIGIDO AQUI**: A query SQL estava incompleta.
    $sql = "UPDATE processos 
            SET numero_processo = ?, 
                nome_processo = ?, 
                tipo_contratacao = ?, 
                status = ?, 
                agente_responsavel = ?, 
                uasg = ? 
            WHERE id = ?";

    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);

    // **ERRO CORRIGIDO AQUI**: A ordem dos parâmetros e a quantidade devem corresponder à query.
    $stmt->execute([
        $dados['numero_processo'],
        $dados['nome_processo'],
        $dados['tipo_contratacao'],
        $dados['status'],
        $dados['agente_responsavel'], 
        $dados['uasg'],
        $id
    ]);

    // **AJUSTE ADICIONADO**: Redireciona o usuário para a lista de processos após salvar.
    return $response->withHeader('Location', '/processos')->withStatus(302);
}

        // NOVO MÉTODO: Apaga um processo do banco de dados
    public function excluir($request, $response, $args)
    {
        $id = $args['id'];

        $sql = "DELETE FROM processos WHERE id = ?";

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        // Redireciona para o dashboard após excluir
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}