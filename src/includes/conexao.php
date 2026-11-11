<?php
/**
 * conexao.php — cria a conexão com o MySQL ($conn).
 *
 * O que mudou em relação à sua versão:
 *  1. mysqli_report(...): faz o mysqli LANÇAR EXCEÇÕES quando algo dá errado
 *     (SQL inválido, banco fora do ar...). Sem isso, o PHP só devolve `false`
 *     e o erro passa em silêncio — o que gera bugs difíceis de achar.
 *  2. try/catch na conexão: se o banco estiver fora, o usuário vê uma
 *     mensagem amigável e o erro técnico (que pode conter dados sensíveis)
 *     vai para o log do servidor, e não para a tela.
 *
 * RECOMENDAÇÃO: usuário "root" sem senha só serve no seu computador.
 * No servidor da igreja, crie um usuário só para este sistema, com
 * permissão apenas no banco ipsa_estoque:
 *   CREATE USER 'ipsa_app'@'localhost' IDENTIFIED BY 'uma-senha-forte';
 *   GRANT SELECT, INSERT, UPDATE, DELETE ON ipsa_estoque.* TO 'ipsa_app'@'localhost';
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host    = "localhost";
$usuario = "root";
$senha   = "";
$banco   = "ipsa_estoque";

try {
    $conn = new mysqli($host, $usuario, $senha, $banco);
    // utf8mb4 = acentos e emojis funcionando corretamente.
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $erro) {
    // error_log grava no log do Apache/PHP (XAMPP: xampp/apache/logs/error.log)
    error_log("Falha ao conectar no banco: " . $erro->getMessage());
    http_response_code(500);
    exit("Não foi possível conectar ao banco de dados. Tente novamente mais tarde.");
}
