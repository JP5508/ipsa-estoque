<?php
/**
 * foto.php — entrega a foto de um equipamento: <img src="foto.php?id=5">
 *
 * Como a foto está guardada no banco (BLOB), não existe um arquivo .jpg para o
 * navegador acessar. Este script lê os bytes do banco e os devolve como se fosse
 * uma imagem, avisando o tipo certo no cabeçalho Content-Type.
 */
require_once __DIR__ . '/../includes/init.php';
exigir_login(); // fotos só para quem está logado

// IMPORTANTE: a sessão "trava" (lock) o arquivo de sessão enquanto o script roda.
// Uma tabela com 10 fotos faria 10 requisições em FILA. Já checamos o login,
// então liberamos a sessão para as imagens carregarem em paralelo.
session_write_close();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT equ_foto, equ_foto_tipo FROM equipamento WHERE equ_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$linha = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$linha || $linha['equ_foto'] === null) {
    http_response_code(404);
    exit;
}

$foto = $linha['equ_foto'];
// ETag = "impressão digital" da imagem. Se o navegador já tem a mesma versão,
// respondemos 304 (sem corpo) e economizamos banda. Se a foto mudar, o ETag muda.
$etag = '"' . md5($foto) . '"';

header('ETag: ' . $etag);
header('Cache-Control: private, no-cache'); // pode guardar, mas sempre pergunta se mudou
header('X-Content-Type-Options: nosniff');  // o navegador não "adivinha" o tipo

if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}

header('Content-Type: ' . ($linha['equ_foto_tipo'] ?: 'application/octet-stream'));
header('Content-Length: ' . strlen($foto));
echo $foto;
