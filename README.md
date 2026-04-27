# Desafio Projeto E-book

Sistema web de cadastro de livros em PHP com MVC simples, com o foco principal frontend com Twig e Tailwind CSS e Js.

## Requisitos

- PHP `7.4` ou superior
- Composer
- MySQL `5.7+` ou `8+`
- Extensoes PHP `pdo` e `pdo_mysql`

## Instalar dependencias

Na raiz do projeto:

```
composer install
```

## Configurar ambiente

O projeto usa MySQL com configuracao via `.env`.

Use o arquivo `.env.example` como base e ajuste os valores do seu ambiente:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ebook
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

## Criar o banco

Crie um banco chamado `ebook` no MySQL.

````
CREATE DATABASE ebook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
````

## Como rodar

Na raiz do projeto:

php -S localhost:8000


Depois acesse:

http://localhost:8000



## Estrutura principal

```
app/
  Controllers/
  Core/
  Models/
  Views/
database/
  schema.sql
index.php
composer.json
```


## Rotas
- `GET /`
- `GET /livros/cadastrar`
- `POST /livros`
- `GET /livros/editar/{id}`
- `POST /livros/atualizar/{id}`
- `POST /livros/excluir/{id}`
- `GET /relatorios`

## Observacoes
