<?php
/**
 * estoque.php — lista de equipamentos (evolução do seu base.php).
 *
 * Novidades:
 *  - Exige login.
 *  - Busca por texto (?q=), filtro por status e categoria.
 *  - Paginação (10 por página).
 *  - PREPARED STATEMENTS: o SQL é enviado separado dos valores, então
 *    é impossível "injetar" SQL pela busca (ataque SQL Injection).
 *  - Não carrega mais a coluna da foto (BLOB) na listagem: fotos podem
 *    ter MBs, e o SELECT * trazia isso tudo só para mostrar "(foto)".
 *    Agora perguntamos apenas "tem foto?" e o navegador busca a imagem
 *    em foto.php só das linhas visíveis.
 */
require_once __DIR__ . '/../includes/init.php';
exigir_login();

/* ---------- 1. LER OS FILTROS DA URL (GET) ---------- */
$busca     = get_texto('q');
$status    = get_texto('status');
$categoria = get_texto('categoria');
$pagina    = max(1, (int) ($_GET['pagina'] ?? 1));

// Valor de status que não existe? Ignora (nunca confie no que vem da URL).
if (!isset(STATUS_EQUIPAMENTO[$status])) {
    $status = '';
}

/* ---------- 2. MONTAR O WHERE DINAMICAMENTE ----------
 * Em vez de concatenar valores dentro do SQL, colocamos "?" (placeholders)
 * e guardamos os valores numa lista, junto com o tipo de cada um:
 *   s = string, i = inteiro, d = decimal.
 */
$condicoes = [];
$tipos     = '';
$valores   = [];

if ($busca !== '') {
    $condicoes[] = '(equ_nome LIKE ? OR equ_tipo LIKE ? OR equ_modelo LIKE ? OR equ_categoria LIKE ?)';
    // No LIKE, % e _ são curingas. Escapamos para a busca ser literal.
    $like = '%' . addcslashes($busca, '%_\\') . '%';
    array_push($valores, $like, $like, $like, $like);
    $tipos .= 'ssss';
}
if ($status !== '') {
    $condicoes[] = 'equ_status = ?';
    $valores[]   = $status;
    $tipos      .= 's';
}
if ($categoria !== '') {
    $condicoes[] = 'equ_categoria = ?';
    $valores[]   = $categoria;
    $tipos      .= 's';
}
$where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

/* ---------- 3. CONTAR O TOTAL (para a paginação) ---------- */
$stmt = $conn->prepare("SELECT COUNT(*) FROM equipamento $where");
if ($valores) {
    // O "..." (spread) espalha o array como argumentos separados.
    $stmt->bind_param($tipos, ...$valores);
}
$stmt->execute();
$total = (int) $stmt->get_result()->fetch_row()[0];
$stmt->close();

$totalPaginas = max(1, (int) ceil($total / ITENS_POR_PAGINA));
$pagina       = min($pagina, $totalPaginas);
$deslocamento = ($pagina - 1) * ITENS_POR_PAGINA; // OFFSET: quantas linhas pular

/* ---------- 4. BUSCAR AS LINHAS DA PÁGINA ---------- */
$sql = "SELECT equ_id, equ_nome, equ_tipo, equ_modelo, equ_descricao,
               equ_data_aquisicao, equ_quantidade, equ_status, equ_categoria,
               (equ_foto IS NOT NULL) AS tem_foto
        FROM equipamento
        $where
        ORDER BY equ_nome ASC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$tiposPagina   = $tipos . 'ii';
$valoresPagina = [...$valores, ITENS_POR_PAGINA, $deslocamento];
$stmt->bind_param($tiposPagina, ...$valoresPagina);
$stmt->execute();
$equipamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // todas as linhas de uma vez, como array
$stmt->close();

/* ---------- 5. CATEGORIAS EXISTENTES (para o <select> do filtro) ---------- */
$categorias = $conn->query(
    "SELECT DISTINCT equ_categoria FROM equipamento
     WHERE equ_categoria IS NOT NULL AND equ_categoria <> ''
     ORDER BY equ_categoria"
)->fetch_all(MYSQLI_ASSOC);

$filtrosAtivos = ($status !== '' ? 1 : 0) + ($categoria !== '' ? 1 : 0);

/* ---------- 6. HTML ---------- */
$tituloPagina = 'Estoque';
$paginaAtual  = 'estoque';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="titulo-pagina">Equipamentos</h1>

<!-- OPÇÕES: busca + filtros. method="get" porque busca não altera nada
     e assim a URL pode ser copiada/favoritada (?q=cabo&status=disponivel). -->
<form method="get" id="options">
    <div class="search-input">
        <input type="search" name="q" value="<?= e($busca) ?>" placeholder="buscar..." aria-label="Buscar equipamentos">
        <button type="submit" aria-label="Buscar"><img src="../assets/icon_search_white.svg" alt="" /></button>
    </div>

    <details class="filtros">
        <summary class="option-btn">Filtros<?= $filtrosAtivos ? " ($filtrosAtivos)" : '' ?></summary>
        <div class="filtros-painel">
            <label>Status
                <select name="status">
                    <option value="">Todos</option>
                    <?php foreach (STATUS_EQUIPAMENTO as $chave => $rotulo): ?>
                        <option value="<?= e($chave) ?>" <?= $chave === $status ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Categoria
                <select name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= e($c['equ_categoria']) ?>" <?= $c['equ_categoria'] === $categoria ? 'selected' : '' ?>>
                            <?= e($c['equ_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="filtros-acoes">
                <button type="submit" class="btn btn-primario">Aplicar</button>
                <a href="estoque.php" class="btn">Limpar</a>
            </div>
        </div>
    </details>

    <?php if (eh_admin()): ?>
        <a href="equipamento_form.php" class="btn btn-primario" id="btn-novo">+ Novo equipamento</a>
    <?php endif; ?>
</form>

<p class="resumo"><?= $total ?> equipamento(s) encontrado(s)</p>

<!-- TABELA -->
<div id="table">
    <table>
        <!-- thead/tbody: o navegador cria um <tbody> sozinho quando falta; declarar é mais correto e permite estilizar. -->
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Modelo</th>
                <th>Descrição</th>
                <th>Aquisição</th>
                <th>Qtd.</th>
                <th>Status</th>
                <th>Categoria</th>
                <?php if (eh_admin()): ?><th>Ações</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!$equipamentos): ?>
                <tr>
                    <td colspan="10" class="vazio">Nenhum equipamento encontrado.</td>
                </tr>
            <?php endif; ?>

            <!-- LINHAS POR PHP: foreach é mais simples que while + fetch_assoc quando já temos o array -->
            <?php foreach ($equipamentos as $eq): ?>
                <tr>
                    <td>
                        <?php if ($eq['tem_foto']): ?>
                            <img class="thumb" loading="lazy" src="foto.php?id=<?= (int) $eq['equ_id'] ?>" alt="Foto de <?= e($eq['equ_nome']) ?>">
                        <?php else: ?>
                            <span class="sem-foto">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($eq['equ_nome']) ?></td>
                    <td><?= e($eq['equ_tipo']) ?></td>
                    <td><?= e($eq['equ_modelo']) ?></td>
                    <!-- mb_strimwidth corta textos longos (mb_ = seguro com acentos) -->
                    <td title="<?= e($eq['equ_descricao']) ?>"><?= e(mb_strimwidth($eq['equ_descricao'] ?? '', 0, 60, '…', 'UTF-8')) ?></td>
                    <td><?= e(formatar_data($eq['equ_data_aquisicao'])) ?></td>
                    <td><?= (int) $eq['equ_quantidade'] ?></td>
                    <td>
                        <span class="badge badge-<?= e($eq['equ_status']) ?>">
                            <?= e(STATUS_EQUIPAMENTO[$eq['equ_status']] ?? $eq['equ_status']) ?>
                        </span>
                    </td>
                    <td><?= e($eq['equ_categoria']) ?></td>
                    <?php if (eh_admin()): ?>
                        <td class="acoes">
                            <a class="btn" href="equipamento_form.php?id=<?= (int) $eq['equ_id'] ?>">Editar</a>
                            <!-- Remover é um formulário POST (nunca um link!): links podem ser
                                 acionados por robôs, pré-carregamento do navegador, etc. -->
                            <form method="post" action="equipamento_remover.php"
                                  data-confirmar="Remover &quot;<?= e($eq['equ_nome']) ?>&quot;? Essa ação não pode ser desfeita.">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $eq['equ_id'] ?>">
                                <button type="submit" class="btn btn-perigo">Remover</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
    <nav class="paginacao" aria-label="Paginação">
        <?php if ($pagina > 1): ?>
            <a class="btn" href="<?= e(url_com(['pagina' => $pagina - 1])) ?>">‹ Anterior</a>
        <?php endif; ?>
        <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>
        <?php if ($pagina < $totalPaginas): ?>
            <a class="btn" href="<?= e(url_com(['pagina' => $pagina + 1])) ?>">Próxima ›</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
