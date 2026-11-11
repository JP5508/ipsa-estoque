<?php
/**
 * funcoes.php — pequenas funções que o sistema inteiro reutiliza.
 * Regra de ouro do back-end: se você escreveu o mesmo código 3 vezes, vira função.
 */

// Valores permitidos para o status do equipamento.
// chave = o que é gravado no banco (VARCHAR(10)) | valor = o que aparece na tela.
const STATUS_EQUIPAMENTO = [
    'disponivel' => 'Disponível',
    'emprestado' => 'Emprestado',
    'manutencao' => 'Manutenção',
];

// Quantas linhas aparecem por página na tabela de estoque.
const ITENS_POR_PAGINA = 10;

/**
 * e() = "escape". Transforma <, >, ", ' em texto seguro para o HTML.
 * Use SEMPRE que for imprimir algo que veio do banco ou do usuário.
 * Sem isso, alguém pode cadastrar um equipamento chamado
 * <script>...</script> e o código rodaria no navegador de todos (ataque XSS).
 * É o mesmo htmlspecialchars que você já usava, só que mais curto de escrever.
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redireciona para outra página e PARA a execução (o exit é importante!). */
function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Lê um campo de texto do $_POST (ou $_GET) com segurança.
 * Por que não usar $_POST['x'] direto? Porque o usuário pode mandar
 * ?x[]=1 (um array) e o PHP quebraria com erro. Aqui garantimos que
 * o resultado SEMPRE é uma string.
 */
function entrada_texto(array $origem, string $campo, bool $aparar = true): string
{
    $valor = $origem[$campo] ?? '';
    if (!is_string($valor)) {
        return '';
    }
    return $aparar ? trim($valor) : $valor;
}

function post_texto(string $campo, bool $aparar = true): string
{
    return entrada_texto($_POST, $campo, $aparar);
}

function get_texto(string $campo): string
{
    return entrada_texto($_GET, $campo);
}

/* ---------------------------------------------------------------
 * MENSAGENS "FLASH"
 * Mensagem que aparece UMA vez, na próxima página carregada.
 * Ex.: salvou o equipamento -> redireciona para a lista -> a lista
 * mostra "Equipamento salvo!" e a mensagem some ao recarregar.
 * Padrão chamado PRG (Post/Redirect/Get): evita que o F5 reenvie o formulário.
 * --------------------------------------------------------------- */
function flash(string $tipo, string $mensagem): void
{
    // $tipo: 'sucesso', 'erro' ou 'info' (vira classe CSS: flash-sucesso...)
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function consumir_flash(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']); // lê e apaga: por isso "consumir"
    return $mensagens;
}

/* ---------------------------------------------------------------
 * CSRF — proteção contra "requisição forjada"
 * Imagine que um admin está logado e abre um site malicioso que, em
 * segredo, envia um formulário para o seu /equipamento_remover.php.
 * O navegador mandaria o cookie de sessão junto e o item seria apagado!
 * A defesa: todo formulário carrega um token secreto que só o seu site
 * conhece. O site malicioso não consegue adivinhá-lo.
 * --------------------------------------------------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32)); // 64 caracteres aleatórios
    }
    return $_SESSION['csrf'];
}

/** Gera o <input hidden> que vai dentro de todo <form method="post">. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Chame no começo de todo código que processa um POST. */
function csrf_validar(): void
{
    $enviado = $_POST['csrf'] ?? '';
    // hash_equals compara em tempo constante (evita ataques de temporização).
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(403);
        exit('Requisição inválida (token de segurança ausente ou expirado). Volte e tente de novo.');
    }
}

/** Garante que a página só aceita POST (ex.: remover). Ações que MUDAM dados nunca devem ser GET. */
function exigir_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método não permitido.');
    }
}

/** '2026-11-04' (formato do banco) -> '04/11/2026' (formato brasileiro). */
function formatar_data(?string $data): string
{
    if (!$data) {
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d ? $d->format('d/m/Y') : '';
}

/**
 * Monta uma URL mantendo os filtros atuais e trocando só o que você pedir.
 * Ex.: url_com(['pagina' => 3]) em /estoque.php?q=cabo  =>  ?q=cabo&pagina=3
 */
function url_com(array $mudancas): string
{
    return '?' . http_build_query(array_merge($_GET, $mudancas));
}
