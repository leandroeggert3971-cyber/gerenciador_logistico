# Gerenciador Logístico

## Sobre o projeto

O Gerenciador Logístico é um sistema desenvolvido como atividade prática da disciplina de Fundamentos de Programação para Internet da UNIPAR.

O sistema permite gerenciar produtos, endereços, transportadores e fretes, além de consultar e baixar relatórios.

## Funcionalidades

- Cadastro, consulta, edição e exclusão de produtos.
- Cadastro e gerenciamento de endereços de origem e destino.
- Cadastro e gerenciamento de transportadores.
- Cadastro de fretes vinculando transportadores, origem, destino e produtos.
- Inclusão de vários produtos em um mesmo frete.
- Consulta de fretes cadastrados.
- Relatórios de fretes por data, com opção de download em CSV.

## Tecnologias utilizadas

- PHP 8.3
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- XAMPP
- Git e GitHub

## Como executar o projeto

1. Instale o XAMPP.
2. Copie a pasta do projeto para `C:\xampp\htdocs\`.
3. Inicie o Apache e o MySQL no XAMPP.
4. Crie o banco de dados `gerenciador_logistico` no phpMyAdmin.
5. Configure a conexão no arquivo `config/conexao.php`.
6. Acesse `http://localhost/gerenciador_logistico/` pelo navegador.

## Autor

Leandro Eggert

## Instituição

UNIPAR – Universidade Paranaense
