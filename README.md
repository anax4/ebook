# Desafio Projeto E-book

Sistema web de cadastro de livros em PHP com MVC simples, com foco principal em frontend com Twig, Tailwind CSS e JS. O projeto usa páginas renderizadas no servidor e inclui um endpoint JSON interno para a tela de relatórios.

## Requisitos

- PHP `7.4` ou superior
- Composer
- MySQL `5.7+` ou `8+`
- Extensões PHP `pdo` e `pdo_mysql`

## Instalar dependências

Na raiz do projeto:

```
composer install
```

## Configurar ambiente

O projeto usa MySQL com configuração via `.env`.

Use o arquivo `.env.example` como base e ajuste os valores do seu ambiente:

```env
DB_ROOT_PASSWORD=root
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ebook
DB_USERNAME=user
DB_PASSWORD=password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Se você for rodar sem Docker, troque `DB_HOST`, `DB_USERNAME` e `DB_PASSWORD` para os valores do seu MySQL local.

## Criar o banco

Crie um banco chamado `ebook` no MySQL.

```sql
CREATE DATABASE ebook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Como rodar

Na raiz do projeto:

```
php -S localhost:8000
```

Depois acesse:

http://localhost:8000

## Como rodar com Docker

Na raiz do projeto:

```
docker compose up --build
```

A aplicação fica em:

http://localhost:8000

Observações importantes:

- O `docker-compose.yml` usa as credenciais do seu arquivo `.env`.
- Se você já subiu o MySQL com outra senha anteriormente, recrie o volume para o banco ler a nova configuração.

```
docker compose down -v
docker compose up --build
```

## Como acessar o relatório

Com a aplicação rodando, acesse:

http://localhost:8000/relatorios

Nessa tela, o relatório é carregado dinamicamente no frontend e consome o endpoint JSON interno `/api/relatorio-livros`.

Você pode:

- Buscar por título, autor ou assunto.
- Filtrar por autor.
- Filtrar por ano.
- Alterar a ordenação.
- Exportar o resultado em CSV.
- Imprimir o relatório.

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
- `GET /api/relatorio-livros`

## Observações

- O frontend usa Twig, Tailwind CSS e JavaScript puro.
- O relatório consome o endpoint JSON interno `/api/relatorio-livros`.
- O projeto utiliza MySQL como banco de dados.
