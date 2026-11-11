<?php
/**
 * header.php — ABRE a página: <html>, <head>, <header> e o <aside> (menu).
 * Depois dele, a página escreve o próprio conteúdo e termina com footer.php.
 *
 * COMO USAR em qualquer página:
 *
 *     <?php
 *     require_once __DIR__ . '/../includes/init.php';
 *     exigir_login();
 *     $tituloPagina = 'Estoque';     // vai no <title> da aba
 *     $paginaAtual  = 'estoque';     // qual item do menu fica destacado
 *     require __DIR__ . '/../includes/header.php';
 *     ?>
 *
 *     ... conteúdo da página (a parte que muda) ...
 *
 *     <?php require __DIR__ . '/../includes/footer.php'; ?>
 *
 * Assim, se você quiser trocar o logo ou adicionar um item no menu,
 * muda em UM lugar só e todas as páginas atualizam.
 *
 * ATENÇÃO: os caminhos de css/imagens abaixo (../assets, ../styles) são
 * relativos à PÁGINA que inclui este arquivo. Por isso todas as páginas
 * ficam na mesma pasta (src/pages/).
 */

$tituloPagina = $tituloPagina ?? 'Página';
$paginaAtual  = $paginaAtual ?? '';
$usuario      = usuario_atual();
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ipsa Estoque - <?= e($tituloPagina) ?></title>
    <link rel="icon" type="image/png" href="../assets/logo_ipsa_color.png">
    <!-- palette.css agora é carregado aqui, e não com @import dentro do home.css:
         o @import faz o navegador esperar um arquivo terminar para só então baixar o outro. -->
    <link href="../styles/palette.css" rel="stylesheet" />
    <link href="../styles/home.css" rel="stylesheet" />
    <link href="../styles/componentes.css" rel="stylesheet" />
</head>

<body>

    <!-- HEADER -->
    <header>
        <button id="menu-btn" type="button" aria-label="Abrir ou fechar o menu" aria-controls="aside">
            <img src="../assets/icon_menu_white.svg" alt="" />
        </button>
        <img id="header-logo" src="../assets/logo_ipsa_white.png" alt="Logo da IPSA" />

        <?php if ($usuario): ?>
            <div id="user-link">
                <p><?= e($usuario['nome']) ?></p>
                <img id="user-icon" src="../assets/profile_default.png" alt="" />
            </div>
            <!-- Sair é um POST com token CSRF: assim ninguém desloga você por um link malicioso. -->
            <form method="post" action="logout.php" id="logout-form">
                <?= csrf_campo() ?>
                <button type="submit" class="btn btn-claro">Sair</button>
            </form>
        <?php endif; ?>
    </header>

    <?php require __DIR__ . '/aside.php'; ?>

    <!-- SECTION (o conteúdo principal; fecha em footer.php) -->
    <section>
        <?php /* Mensagens flash: "Equipamento salvo!", "Erro ao remover"... */ ?>
        <?php foreach (consumir_flash() as $mensagem): ?>
            <div class="flash flash-<?= e($mensagem['tipo']) ?>" role="status">
                <?= e($mensagem['mensagem']) ?>
            </div>
        <?php endforeach; ?>
