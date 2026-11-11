<?php
/**
 * logout.php — encerra a sessão. Só aceita POST com token CSRF
 * (o botão "Sair" do header.php é um pequeno formulário).
 */
require_once __DIR__ . '/../includes/init.php';
exigir_post();
csrf_validar();
fazer_logout();
redirecionar('login.php?saiu=1');
