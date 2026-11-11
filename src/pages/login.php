<?php
/**
 * login.php — tela de login.
 * Não usa header.php/footer.php porque o usuário ainda não está dentro do sistema
 * (sem menu lateral). Por isso tem o próprio <html>.
 */
require_once __DIR__ . '/../includes/init.php';

// Já está logado? Não faz sentido ver a tela de login.
if (usuario_atual() !== null) {
    redirecionar('estoque.php');
}

$erro  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $email = post_texto('email');
    $senha = post_texto('senha', false); // senha NÃO leva trim: espaços podem fazer parte dela

    $stmt = $conn->prepare('SELECT usu_id, usu_nome, usu_senha, usu_nivel FROM usuario WHERE usu_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // password_verify(senha digitada, hash do banco)
    if ($usuario && password_verify($senha, $usuario['usu_senha'])) {
        fazer_login($usuario);
        redirecionar('estoque.php');
    }

    // Mensagem GENÉRICA de propósito: não revelamos se foi o e-mail ou a senha que
    // errou, para não ajudar quem tenta descobrir e-mails cadastrados.
    $erro = 'E-mail ou senha incorretos.';
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ipsa Estoque - Entrar</title>
    <link rel="icon" type="image/png" href="../assets/logo_ipsa_color.png">
    <link href="../styles/palette.css" rel="stylesheet" />
    <link href="../styles/home.css" rel="stylesheet" />
    <link href="../styles/componentes.css" rel="stylesheet" />
</head>

<body class="login-page">
    <main class="login-card">
        <img src="../assets/logo_ipsantoamaro_color.svg" alt="Igreja Presbiteriana de Santo Amaro" class="login-logo">
        <h1>Estoque de Multimídia e Som</h1>

        <?php if (isset($_GET['saiu'])): ?>
            <div class="flash flash-info" role="status">Você saiu do sistema.</div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="flash flash-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="form-login">
            <?= csrf_campo() ?>
            <label class="campo">E-mail
                <input type="email" name="email" required autofocus autocomplete="username" value="<?= e($email) ?>">
            </label>
            <label class="campo">Senha
                <input type="password" name="senha" required autocomplete="current-password">
            </label>
            <button type="submit" class="btn btn-primario">Entrar</button>
        </form>
    </main>
</body>

</html>
