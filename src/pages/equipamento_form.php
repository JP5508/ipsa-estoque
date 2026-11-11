<?php
/**
 * equipamento_form.php — CADASTRAR e EDITAR equipamento (a mesma página faz os dois).
 *
 *   equipamento_form.php        -> formulário vazio  (CREATE)
 *   equipamento_form.php?id=5   -> formulário preenchido com o item 5 (UPDATE)
 *
 * Fluxo de uma página com formulário (padrão que você vai usar sempre):
 *   GET  -> mostra o formulário
 *   POST -> valida -> se tiver erro, mostra o formulário de novo com as mensagens
 *                  -> se estiver ok, grava no banco e REDIRECIONA (padrão PRG)
 */
require_once __DIR__ . '/../includes/init.php';
exigir_admin(); // só administradores cadastram/editam

const TIPOS_FOTO_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];
const TAMANHO_MAX_FOTO      = 2 * 1024 * 1024; // 2 MB

/** Texto vazio vira NULL no banco (campos opcionais). */
function ou_nulo(string $valor): ?string
{
    return $valor === '' ? null : $valor;
}

$id       = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$editando = $id > 0;
$erros    = [];
$temFoto  = false;

// Valores iniciais do formulário (cadastro novo).
$dados = [
    'nome' => '', 'tipo' => '', 'modelo' => '', 'descricao' => '',
    'data_aquisicao' => '', 'quantidade' => '1', 'status' => 'disponivel', 'categoria' => '',
];

/* ---------- EDIÇÃO: carrega o item existente ---------- */
if ($editando) {
    $stmt = $conn->prepare(
        'SELECT equ_nome, equ_tipo, equ_modelo, equ_descricao, equ_data_aquisicao,
                equ_quantidade, equ_status, equ_categoria, (equ_foto IS NOT NULL) AS tem_foto
         FROM equipamento WHERE equ_id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc(); // 1 linha (ou null se não existir)
    $stmt->close();

    if (!$linha) {
        flash('erro', 'Equipamento não encontrado.');
        redirecionar('estoque.php');
    }

    $temFoto = (bool) $linha['tem_foto'];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $dados = [
            'nome'           => $linha['equ_nome'],
            'tipo'           => $linha['equ_tipo'] ?? '',
            'modelo'         => $linha['equ_modelo'] ?? '',
            'descricao'      => $linha['equ_descricao'] ?? '',
            'data_aquisicao' => $linha['equ_data_aquisicao'] ?? '',
            'quantidade'     => (string) $linha['equ_quantidade'],
            'status'         => $linha['equ_status'],
            'categoria'      => $linha['equ_categoria'] ?? '',
        ];
    }
}

/* ---------- POST: validar e salvar ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $dados = [
        'nome'           => post_texto('nome'),
        'tipo'           => post_texto('tipo'),
        'modelo'         => post_texto('modelo'),
        'descricao'      => post_texto('descricao'),
        'data_aquisicao' => post_texto('data_aquisicao'),
        'quantidade'     => post_texto('quantidade'),
        'status'         => post_texto('status'),
        'categoria'      => post_texto('categoria'),
    ];
    $removerFoto = isset($_POST['remover_foto']);

    // --- Validação: SEMPRE no servidor. O "required" do HTML é só conforto para
    //     o usuário; quem quiser burla pelo DevTools ou por um script. ---
    if ($dados['nome'] === '') {
        $erros[] = 'Informe o nome do equipamento.';
    } elseif (mb_strlen($dados['nome']) > 50) {
        $erros[] = 'O nome pode ter no máximo 50 caracteres.';
    }

    foreach ([['tipo', 100, 'Tipo'], ['modelo', 50, 'Modelo'], ['categoria', 50, 'Categoria']] as [$campo, $max, $rotulo]) {
        if (mb_strlen($dados[$campo]) > $max) {
            $erros[] = "$rotulo pode ter no máximo $max caracteres.";
        }
    }

    $quantidade = filter_var($dados['quantidade'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($quantidade === false) {
        $erros[] = 'A quantidade deve ser um número inteiro maior ou igual a zero.';
    }

    if (!isset(STATUS_EQUIPAMENTO[$dados['status']])) {
        $erros[] = 'Status inválido.';
    }

    if ($dados['data_aquisicao'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $dados['data_aquisicao']);
        // Reformatar e comparar pega datas impossíveis, como 2026-02-31.
        if (!$d || $d->format('Y-m-d') !== $dados['data_aquisicao']) {
            $erros[] = 'Data de aquisição inválida.';
        }
    }

    /* --- Upload da foto ---
     * $_FILES['foto'] traz: name, type, tmp_name, error, size.
     * ATENÇÃO: 'type' e 'name' vêm do navegador e podem ser FALSIFICADOS.
     * Por isso descobrimos o tipo real olhando o CONTEÚDO do arquivo (finfo). */
    $foto     = null;
    $fotoTipo = null;
    $arquivo  = $_FILES['foto'] ?? null;

    if ($arquivo && $arquivo['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            // UPLOAD_ERR_INI_SIZE = passou do upload_max_filesize do php.ini (padrão 2 MB no XAMPP)
            $erros[] = 'Não foi possível enviar a foto (arquivo muito grande ou corrompido).';
        } elseif ($arquivo['size'] > TAMANHO_MAX_FOTO) {
            $erros[] = 'A foto pode ter no máximo 2 MB.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
            if (!in_array($mime, TIPOS_FOTO_PERMITIDOS, true)) {
                $erros[] = 'A foto deve ser JPG, PNG ou WEBP.';
            } else {
                $foto     = file_get_contents($arquivo['tmp_name']);
                $fotoTipo = $mime;
            }
        }
    }

    /* --- Gravar no banco --- */
    if (!$erros) {
        try {
            if (!$editando) {
                // INSERT: 10 colunas, 10 "?". Tipos: s s s s s i s s s s
                $stmt = $conn->prepare(
                    'INSERT INTO equipamento
                     (equ_nome, equ_tipo, equ_modelo, equ_descricao, equ_data_aquisicao,
                      equ_quantidade, equ_status, equ_categoria, equ_foto, equ_foto_tipo)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $nome = $dados['nome'];
                $tipo = ou_nulo($dados['tipo']);
                $modelo = ou_nulo($dados['modelo']);
                $descricao = ou_nulo($dados['descricao']);
                $data = ou_nulo($dados['data_aquisicao']);
                $status = $dados['status'];
                $cat = ou_nulo($dados['categoria']);
                $stmt->bind_param('sssssissss', $nome, $tipo, $modelo, $descricao, $data, $quantidade, $status, $cat, $foto, $fotoTipo);
                $stmt->execute();
                $stmt->close();
                flash('sucesso', 'Equipamento cadastrado com sucesso!');
            } else {
                // UPDATE montado por partes: só mexemos na foto se o usuário pediu.
                $campos  = ['equ_nome = ?', 'equ_tipo = ?', 'equ_modelo = ?', 'equ_descricao = ?',
                            'equ_data_aquisicao = ?', 'equ_quantidade = ?', 'equ_status = ?', 'equ_categoria = ?'];
                $valores = [$dados['nome'], ou_nulo($dados['tipo']), ou_nulo($dados['modelo']), ou_nulo($dados['descricao']),
                            ou_nulo($dados['data_aquisicao']), $quantidade, $dados['status'], ou_nulo($dados['categoria'])];
                $tipos   = 'sssssiss';

                if ($foto !== null) {                 // enviou foto nova -> substitui
                    $campos[] = 'equ_foto = ?';
                    $campos[] = 'equ_foto_tipo = ?';
                    array_push($valores, $foto, $fotoTipo);
                    $tipos .= 'ss';
                } elseif ($removerFoto) {             // marcou "remover foto"
                    $campos[] = 'equ_foto = NULL';
                    $campos[] = 'equ_foto_tipo = NULL';
                }                                     // senão: mantém a foto atual

                $valores[] = $id;
                $tipos    .= 'i';
                $stmt = $conn->prepare('UPDATE equipamento SET ' . implode(', ', $campos) . ' WHERE equ_id = ?');
                $stmt->bind_param($tipos, ...$valores);
                $stmt->execute();
                $stmt->close();
                flash('sucesso', 'Equipamento atualizado com sucesso!');
            }
            redirecionar('estoque.php'); // PRG: depois de salvar, volta para a lista
        } catch (mysqli_sql_exception $erro) {
            error_log('Erro ao salvar equipamento: ' . $erro->getMessage());
            $erros[] = 'Erro ao salvar no banco de dados. Tente novamente.';
        }
    }
}

/* ---------- HTML ---------- */
$tituloPagina = $editando ? 'Editar equipamento' : 'Novo equipamento';
$paginaAtual  = 'estoque';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="titulo-pagina"><?= e($tituloPagina) ?></h1>

<?php if ($erros): ?>
    <div class="flash flash-erro" role="alert">
        <strong>Corrija os pontos abaixo:</strong>
        <ul>
            <?php foreach ($erros as $erro): ?>
                <li><?= e($erro) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- enctype="multipart/form-data" é OBRIGATÓRIO para enviar arquivos. Sem ele, $_FILES vem vazio. -->
<form method="post" enctype="multipart/form-data" class="form-grid">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <label class="campo campo-largo">Nome *
        <input type="text" name="nome" maxlength="50" required value="<?= e($dados['nome']) ?>">
    </label>

    <label class="campo">Tipo
        <input type="text" name="tipo" maxlength="100" placeholder="Ex.: Microfone, Caixa de som" value="<?= e($dados['tipo']) ?>">
    </label>

    <label class="campo">Modelo
        <input type="text" name="modelo" maxlength="50" value="<?= e($dados['modelo']) ?>">
    </label>

    <label class="campo">Categoria
        <input type="text" name="categoria" maxlength="50" placeholder="Ex.: Áudio, Vídeo, Cabos" value="<?= e($dados['categoria']) ?>">
    </label>

    <label class="campo">Data de aquisição
        <input type="date" name="data_aquisicao" value="<?= e($dados['data_aquisicao']) ?>">
    </label>

    <label class="campo">Quantidade *
        <input type="number" name="quantidade" min="0" step="1" required value="<?= e($dados['quantidade']) ?>">
    </label>

    <label class="campo">Status *
        <select name="status" required>
            <?php foreach (STATUS_EQUIPAMENTO as $chave => $rotulo): ?>
                <option value="<?= e($chave) ?>" <?= $chave === $dados['status'] ? 'selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="campo campo-largo">Descrição
        <textarea name="descricao" rows="4"><?= e($dados['descricao']) ?></textarea>
    </label>

    <div class="campo campo-largo">
        <label for="foto">Foto (JPG, PNG ou WEBP, até 2 MB)</label>
        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
        <!-- Pré-visualização: o script.js mostra a foto escolhida antes de enviar -->
        <img id="foto-preview" class="thumb-grande" alt="Pré-visualização da foto"
             <?php if ($editando && $temFoto): ?>src="foto.php?id=<?= $id ?>"<?php else: ?>hidden<?php endif; ?>>
        <?php if ($editando && $temFoto): ?>
            <label class="checkbox"><input type="checkbox" name="remover_foto"> Remover a foto atual</label>
        <?php endif; ?>
    </div>

    <div class="form-acoes campo-largo">
        <button type="submit" class="btn btn-primario">Salvar</button>
        <a href="estoque.php" class="btn">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
