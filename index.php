<?php
// Página inicial do site: só encaminha para a lista de estoque
// (que por sua vez manda para o login se a pessoa não estiver logada).
// Este arquivo substitui o index.html — PHP só roda em arquivos .php.
header('Location: src/pages/estoque.php');
exit;
