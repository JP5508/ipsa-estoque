<?php
/**
 * init.php — a "porta de entrada" de TODAS as páginas.
 *
 * Toda página PHP começa com:
 *     require_once __DIR__ . '/../includes/init.php';
 *
 * Ele faz 4 coisas, sempre na mesma ordem:
 *   1. Inicia a SESSÃO (é o que "lembra" quem está logado entre uma página e outra).
 *   2. Carrega a conexão com o banco ($conn).
 *   3. Carrega as funções utilitárias (e(), flash(), csrf...).
 *   4. Carrega as funções de login (exigir_login(), exigir_admin()...).
 *
 * __DIR__ = pasta onde ESTE arquivo está. Usar __DIR__ evita o erro clássico
 * de "o caminho funciona numa página mas quebra em outra".
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,          // o cookie some quando o navegador fecha
        'path'     => '/',
        'httponly' => true,       // JavaScript NÃO consegue ler o cookie (protege contra XSS)
        'samesite' => 'Lax',      // o navegador não envia o cookie em requisições de outros sites (ajuda contra CSRF)
        // 'secure' => true,      // ATIVE quando o sistema rodar em HTTPS
    ]);
    session_start();
}

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/auth.php';
