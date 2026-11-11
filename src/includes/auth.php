<?php
/**
 * auth.php — autenticação (quem é você?) e autorização (o que você pode fazer?).
 *
 * Como o login funciona:
 *  1. O usuário envia e-mail + senha em login.php.
 *  2. Buscamos o usuário no banco pelo e-mail.
 *  3. password_verify() compara a senha digitada com o HASH guardado no banco.
 *  4. Se bater, guardamos os dados dele em $_SESSION['usuario'].
 *  5. Nas próximas páginas, basta olhar a $_SESSION para saber quem é.
 *
 * A senha NUNCA é guardada "pura" no banco, só o hash (password_hash).
 * Hash é uma via de mão única: dá para conferir uma senha, mas não dá
 * para descobrir a senha a partir do hash.
 */

/** Retorna os dados do usuário logado (id, nome, nivel) ou null se ninguém logou. */
function usuario_atual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function eh_admin(): bool
{
    return (usuario_atual()['nivel'] ?? '') === 'admin';
}

/** Coloque no topo de páginas que exigem login. */
function exigir_login(): void
{
    if (usuario_atual() === null) {
        redirecionar('login.php');
    }
}

/** Coloque no topo de páginas só para administradores. */
function exigir_admin(): void
{
    exigir_login();
    if (!eh_admin()) {
        flash('erro', 'Você não tem permissão para acessar essa página.');
        redirecionar('estoque.php');
    }
}

/** Registra o usuário na sessão. $usuario = linha vinda da tabela `usuario`. */
function fazer_login(array $usuario): void
{
    // Gera um novo ID de sessão ao logar. Impede o ataque "session fixation"
    // (alguém te dá um ID de sessão já conhecido e espera você logar nele).
    session_regenerate_id(true);

    // Guardamos só o necessário — NUNCA a senha/hash na sessão.
    $_SESSION['usuario'] = [
        'id'    => (int) $usuario['usu_id'],
        'nome'  => $usuario['usu_nome'],
        'nivel' => $usuario['usu_nivel'],
    ];
}

/** Encerra a sessão por completo (dados + cookie). */
function fazer_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
