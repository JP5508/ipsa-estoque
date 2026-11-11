<?php
/**
 * equipamento_remover.php — remove um equipamento.
 * Esta página NÃO tem HTML: só processa o POST e redireciona de volta.
 *
 * Três camadas de proteção antes de apagar:
 *   1. exigir_admin()   -> só admin
 *   2. exigir_post()    -> não funciona por link (GET)
 *   3. csrf_validar()   -> o pedido precisa ter vindo do nosso formulário
 */
require_once __DIR__ . '/../includes/init.php';
exigir_admin();
exigir_post();
csrf_validar();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    flash('erro', 'Equipamento inválido.');
    redirecionar('estoque.php');
}

try {
    // A tabela `item` tem FOREIGN KEY para equipamento: se o equipamento já foi
    // emprestado alguma vez, o banco RECUSA apagar (integridade referencial).
    // Em vez de mostrar um erro feio, checamos antes e explicamos.
    $stmt = $conn->prepare('SELECT COUNT(*) FROM item WHERE equ_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usos = (int) $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    if ($usos > 0) {
        flash('erro', "Não é possível remover: este equipamento aparece em $usos item(ns) de empréstimo (histórico). "
            . 'Se ele não está mais em uso, mude o status ou ajuste a quantidade para 0.');
        redirecionar('estoque.php');
    }

    $stmt = $conn->prepare('DELETE FROM equipamento WHERE equ_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    // affected_rows = quantas linhas foram realmente apagadas (0 = id não existia)
    $apagou = $stmt->affected_rows > 0;
    $stmt->close();

    flash($apagou ? 'sucesso' : 'erro', $apagou ? 'Equipamento removido.' : 'Equipamento não encontrado.');
} catch (mysqli_sql_exception $erro) {
    error_log('Erro ao remover equipamento: ' . $erro->getMessage());
    flash('erro', 'Erro ao remover o equipamento. Tente novamente.');
}

redirecionar('estoque.php');
