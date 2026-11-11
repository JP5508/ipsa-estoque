/*
 * script.js — comportamentos do front-end.
 *
 * Mudança de estilo: em vez de onclick="toggleMenu()" escrito dentro do HTML,
 * o JavaScript "se conecta" aos elementos (addEventListener). Vantagens: o HTML fica
 * limpo, e dá para ligar vários comportamentos ao mesmo elemento.
 *
 * Tudo roda dentro de DOMContentLoaded: só executa depois que o HTML foi lido,
 * então os elementos já existem quando fazemos getElementById.
 */

document.addEventListener("DOMContentLoaded", () => {
    iniciarMenu();
    iniciarConfirmacoes();
    iniciarPreviaDeFoto();
});

/* ------------------------------------------------------------------
 * MENU LATERAL: abre/fecha e LEMBRA a escolha do usuário (localStorage).
 * localStorage guarda texto no navegador, mesmo depois de fechar a aba.
 * ------------------------------------------------------------------ */
const CHAVE_MENU = "ipsa_menu_fechado";

function iniciarMenu() {
    const aside = document.getElementById("aside");
    const botao = document.getElementById("menu-btn");
    if (!aside || !botao) return; // páginas sem menu (ex.: login) não fazem nada

    // Escolha salva; se nunca escolheu, no celular começa fechado.
    let fechado = lerPreferenciaMenu();
    if (fechado === null) {
        fechado = window.matchMedia("(max-width: 768px)").matches;
    }
    aplicarMenu(aside, botao, fechado);

    botao.addEventListener("click", () => {
        const agoraFechado = !aside.classList.contains("fechado");
        aplicarMenu(aside, botao, agoraFechado);
        try {
            localStorage.setItem(CHAVE_MENU, agoraFechado ? "1" : "0");
        } catch (e) {
            /* localStorage pode estar bloqueado (aba anônima): o menu só não "lembra". */
        }
    });
}

function lerPreferenciaMenu() {
    try {
        const valor = localStorage.getItem(CHAVE_MENU);
        return valor === null ? null : valor === "1";
    } catch (e) {
        return null;
    }
}

function aplicarMenu(aside, botao, fechado) {
    // classList.toggle(classe, true/false): liga ou desliga a classe explicitamente.
    aside.classList.toggle("fechado", fechado);
    // aria-expanded avisa leitores de tela se o menu está aberto.
    botao.setAttribute("aria-expanded", String(!fechado));
}

/* ------------------------------------------------------------------
 * CONFIRMAÇÃO antes de ações perigosas.
 * Qualquer <form data-confirmar="Mensagem?"> pede confirmação ao enviar.
 * O PHP só escreve o atributo; a lógica fica aqui, num lugar só.
 * ------------------------------------------------------------------ */
function iniciarConfirmacoes() {
    document.querySelectorAll("form[data-confirmar]").forEach((form) => {
        form.addEventListener("submit", (evento) => {
            if (!confirm(form.dataset.confirmar)) {
                evento.preventDefault(); // cancela o envio do formulário
            }
        });
    });
}

/* ------------------------------------------------------------------
 * PRÉ-VISUALIZAÇÃO da foto escolhida no formulário de equipamento.
 * URL.createObjectURL cria um endereço temporário para o arquivo que o
 * usuário escolheu — nada é enviado ao servidor ainda.
 * ------------------------------------------------------------------ */
function iniciarPreviaDeFoto() {
    const input = document.getElementById("foto");
    const previa = document.getElementById("foto-preview");
    if (!input || !previa) return;

    input.addEventListener("change", () => {
        const arquivo = input.files[0];
        if (!arquivo) return;

        // Validação rápida no navegador (a validação de verdade é no PHP).
        if (arquivo.size > 2 * 1024 * 1024) {
            alert("A foto pode ter no máximo 2 MB.");
            input.value = ""; // limpa a escolha
            return;
        }
        previa.src = URL.createObjectURL(arquivo);
        previa.hidden = false;
    });
}
