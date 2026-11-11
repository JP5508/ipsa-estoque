<?php
/**
 * em_breve.php — página "provisória" para os itens do menu que ainda vamos construir
 * (Empréstimos, Calendário, Relatórios, Gerenciar). Quando criar a página de verdade,
 * é só trocar o link no array $menu do includes/aside.php.
 */
require_once __DIR__ . '/../includes/init.php';
exigir_login();

$nomes = [
    'emprestimos' => 'Empréstimos',
    'calendario'  => 'Calendário',
    'relatorios'  => 'Relatórios',
    'gerenciar'   => 'Gerenciar',
];

$chave = get_texto('p');
if (!isset($nomes[$chave])) {
    redirecionar('estoque.php');
}
if ($chave === 'gerenciar') {
    exigir_admin(); // a proteção real fica na página, não só no menu
}

$tituloPagina = $nomes[$chave];
$paginaAtual  = $chave;
require __DIR__ . '/../includes/header.php';
?>

<h1 class="titulo-pagina"><?= e($nomes[$chave]) ?></h1>
<p>Esta área ainda está em construção.</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
