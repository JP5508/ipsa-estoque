# IPSA ESTOQUE

Este repositório contém o **sistema de controle de estoque de equipamentos de multimídia e som** para a **Igreja Presbiteriana de Santo Amaro**

[SITE DA IPSA](https://ipsantoamaro.com.br/)

[YOUTUBE DA IPSA](https://youtube.com/ipsantoamaro)

[INSTAGRAM DA IPSA](https://instagram.com/ipsantoamaro)

----
## EQUIPE

### desenvolvido por:
JOÃO PEDRO ALVES OLIVEIRA

### orientação:
RAUL CHAVARRIA

### período de desenvolvimento:
10/2026 - ...

----

## 📘 Descrição do Projeto

Um site responsivo, criado com **HTML5**, **CSS3**, **PHP**, **JAVA SCRIPT** e **MYSQL**, com as seguintes funcionalidades:
- Login e níveis de acesso (admin / comum);
- Cadastro do estoque (cadastrar, editar, remover, foto, busca e filtros);
- Sistema de empréstimo *(em desenvolvimento)*;
- Relatórios gerais *(em desenvolvimento)*.

## ▶️ Como rodar

1. Copie o projeto para `C:\xampp\htdocs\ipsa-estoque` e inicie **Apache** e **MySQL** no XAMPP.
2. Crie o banco executando `src/notes/ipsa-estoque.sql` e depois `src/notes/migracao_001.sql`.
3. Crie o primeiro usuário administrador (no terminal):
   ```
   cd src/tools
   php criar_usuario.php
   ```
4. Acesse `http://localhost/ipsa-estoque/`.

## 📚 Documentação

Veja [`docs/GUIA_DE_ESTUDO.md`](docs/GUIA_DE_ESTUDO.md) para entender a estrutura, o fluxo das páginas e os conceitos usados.
