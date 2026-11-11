<?php
/**
 * aside.php — menu lateral. Incluído automaticamente pelo header.php.
 *
 * Em vez de copiar/colar um <a> para cada item (como estava no base.php),
 * descrevemos o menu em um ARRAY e usamos um foreach para desenhar.
 * Para criar um item novo, basta adicionar uma linha no array.
 *
 * Formato:  'chave' => ['Texto', 'link', 'arquivo-do-icone', somenteAdmin]
 * A 'chave' é comparada com $paginaAtual (definida na página) para marcar o item ativo.
 */
$menu = [
    'estoque'     => ['Estoque',     'estoque.php',                  'icon_inventory_black.svg', false],
    'emprestimos' => ['Empréstimos', 'em_breve.php?p=emprestimos',   'icon_balance_black.svg',   false],
    'calendario'  => ['Calendário',  'em_breve.php?p=calendario',    'icon_calendar_black.svg',  false],
    'relatorios'  => ['Relatórios',  'em_breve.php?p=relatorios',    'icon_report_black.svg',    false],
    // Só administradores veem este item. (Esconder o link NÃO protege a página:
    // a proteção de verdade é o exigir_admin() dentro da própria página.)
    // Dica: baixe um ícone "settings" no Material Symbols e troque o icon_menu_black.
    'gerenciar'   => ['Gerenciar',   'em_breve.php?p=gerenciar',     'icon_menu_black.svg',      true],
];
?>
<!-- ASIDE -->
<aside id="aside">
    <nav id="nav-main" aria-label="Menu principal">
        <?php foreach ($menu as $chave => [$texto, $link, $icone, $somenteAdmin]): ?>
            <?php if ($somenteAdmin && !eh_admin()) continue; ?>
            <a class="nav-link <?= $chave === $paginaAtual ? 'in-page' : '' ?>" href="<?= e($link) ?>">
                <img src="../assets/<?= e($icone) ?>" alt="" />
                <p><?= e($texto) ?></p>
            </a>
        <?php endforeach; ?>
    </nav>
    <div id="nav-bottom">
        <!-- No base.php havia um </div> fechando antes do </a>: HTML mal aninhado. Corrigido. -->
        <a class="nav-link" href="../notes/esboco_ipsaestoque.png">
            <img src="../assets/icon_help_black.svg" alt="" />
            <p>Ajuda</p>
        </a>
    </div>
</aside>
