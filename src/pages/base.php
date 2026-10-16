<?php
require_once "../includes/conexao.php";

$sql = "SELECT * FROM equipamento ORDER BY equ_nome ASC";
$resultado = $conn->query($sql);
?>

<!doctype html>
<html lang="pt-br">

<head>
    <title>Ipsa Estoque - BASE</title>
    <link rel="icon" type="image/x-icon" href="../assets/logo_ipsa_color.png">
    <!-- META TAGS -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- CSS STYLE -->
    <link href="../styles/home.css" rel="stylesheet" />
</head>

<body>

    <!-- HEADER -->
    <header>
        <button id="menu-btn" onclick="toggleMenu()">
            <img src="../assets/icon_menu_white.svg" alt="Ícone de menu" />
        </button>
        <img id="header-logo" src="../assets/logo_ipsa_white.png" alt="Logo da IPSA" />
        <a id="user-link" href="../pages/admin.php">
            <p>Usuário</p>
            <img id="user-icon" src="../assets/profile_default.png" alt="Ícone de usuário" />
        </a>
    </header>

    <!-- ASIDE -->
    <aside id="aside">
        <div id="nav-main">
            <a class="in-page nav-link" href="../pages/products.html">
                <img src="../assets/icon_inventory_black.svg" alt="" />
                <p>Estoque</p>
            </a>
            <a class="nav-link" href="../pages/products.html">
                <img src="../assets/icon_balance_black.svg" alt="" />
                <p>Empréstimos</p>
            </a>
            <a class="nav-link" href="../pages/products.html">
                <img src="../assets/icon_report_black.svg" alt="" />
                <p>Calendário</p>
            </a>
            <a class="nav-link" href="../pages/products.html">
                <img src="../assets/icon_calendar_black.svg" alt="" />
                <p>Relatórios</p>
            </a>
            <a class="nav-link" href="../pages/products.html">
                <img src="../assets/icon_calendar_black.svg" alt="" />
                <p>Gerenciar</p>
            </a>
        </div>
        <div id="nav-bottom">
            <a class="nav-link" href="../notes/esboco_ipsaestoque.png">
                <img src="../assets/icon_help_black.svg" alt="" />
                <p>Ajuda</p>
        </div>
        </a>
    </aside>

    <!-- SECTION -->
    <section>
        <!-- OPÇÕES -->
        <h5>EQUIPAMENTOS</h5>
        <div id="options">
            <div class="search-input">
                <input type="search" placeholder="buscar...">
                <img src="../assets/icon_search_white.svg" alt="" />
            </div>
            <button class="option-btn">Filtros</button>
        </div>
        <!-- TABELA -->
        <div id="table">
            <table>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Modelo</th>
                    <th>Descrição</th>
                    <th>Data de Aquisição</th>
                    <!-- <th>Valor de Aquisição</th> -->
                    <th>Quantidade</th>
                    <th>Status</th>
                    <th>Categoria</th>
                    <th>Foto</th>
                    <th>Ações</th>
                </tr>
                <!-- LINHAS POR PHP -->
                <?php while ($equipamento = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_nome"], ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_tipo"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_modelo"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_descricao"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_data_aquisicao"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        <?= (int) $equipamento["equ_quantidade"] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_status"], ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($equipamento["equ_categoria"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>
                    <td>
                        (foto)
                    </td>
                    <td>
                        <button type="button">Editar</button>
                        <button type="button">Remover</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        © 2026 não sei o que colocar no footer
    </footer>

    <script src="../js/script.js"></script>
</body>

</html>