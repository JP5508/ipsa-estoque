<?php
/**
 * criar_usuario.php — cria um usuário pelo TERMINAL (não pelo navegador).
 *
 * Por que existe? A tabela `usuario` guarda o HASH da senha, e você não consegue
 * gerar um hash digitando no HeidiSQL. Este script faz isso. Use para criar o
 * primeiro administrador:
 *
 *     cd src/tools
 *     php criar_usuario.php
 *
 * (No XAMPP, o php.exe fica em C:\xampp\php\php.exe — adicione ao PATH ou use o caminho completo.)
 */

// Barreira de segurança: se alguém tentar abrir este arquivo pelo navegador, recusa.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Execute este script pelo terminal.\n");
}

require_once __DIR__ . '/../includes/conexao.php';

function perguntar(string $texto): string
{
    echo $texto;
    return trim((string) fgets(STDIN)); // lê uma linha digitada
}

$nome  = perguntar('Nome: ');
$email = perguntar('E-mail: ');
$nivel = perguntar('Nível (admin/comum) [comum]: ') ?: 'comum';
// ATENÇÃO: a senha aparece na tela enquanto você digita. Faça isso num lugar privado.
$senha = perguntar('Senha (mínimo 8 caracteres): ');

if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Nome vazio ou e-mail inválido.\n");
}
if (!in_array($nivel, ['admin', 'comum'], true)) {
    exit("Nível deve ser 'admin' ou 'comum'.\n");
}
if (strlen($senha) < 8) {
    exit("A senha precisa de pelo menos 8 caracteres.\n");
}

// PASSWORD_DEFAULT = o melhor algoritmo disponível no seu PHP (hoje, bcrypt).
// O resultado tem ~60 caracteres e já inclui o "sal" aleatório. Cabe no VARCHAR(255).
$hash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare('INSERT INTO usuario (usu_nome, usu_email, usu_senha, usu_nivel) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $nome, $email, $hash, $nivel);
    $stmt->execute();
    echo "Usuário criado com sucesso (id {$conn->insert_id}).\n";
} catch (mysqli_sql_exception $erro) {
    // 1062 = "Duplicate entry": o usu_email tem UNIQUE, então e-mail repetido cai aqui.
    if ($erro->getCode() === 1062) {
        exit("Já existe um usuário com esse e-mail.\n");
    }
    throw $erro;
}
