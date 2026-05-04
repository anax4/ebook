# Desafio Projeto E-book

Sistema web de cadastro de livros em PHP com MVC simples, com foco principal em frontend com Twig, Tailwind CSS e Js. O projeto usa páginas renderizadas no servidor e inclui um endpoint JSON interno para a tela de relatórios.

## Stack

- PHP
- Twig
- MySQL
- Tailwind CSS
- JavaScript puro
- Vitest
- PHPUnit

## Requisitos

### Para rodar a aplicação

- PHP `7.4` ou superior
- Composer
- MySQL `5.7+` ou `8+`
- Extensões PHP `pdo` e `pdo_mysql`

### Para desenvolvimento e testes

- PHP `8.2` ou superior
- Node.js
- npm

Observação importante:

- A aplicação continua compatível com PHP `7.4+`.
- Os testes de backend usam `PHPUnit 11`, que exige PHP `8.2+`.
- Se você estiver em PHP abaixo de `8.2` e quiser apenas rodar a aplicação, use `composer install --no-dev`.

## Instalar dependências

### Dependências PHP

Se você também vai rodar os testes de backend:

```
composer install
```

Se você vai apenas rodar a aplicação sem dependências de desenvolvimento:

```
composer install --no-dev
```

### Dependências de frontend para testes

```
npm install
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

### Se for rodar sem Docker

Troque as variáveis de banco para os valores do seu MySQL local.

Exemplo:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ebook
DB_USERNAME=root
DB_PASSWORD=
```

### Se for usar o banco do Docker com a aplicação fora do container

Se você subir apenas o MySQL pelo Docker e rodar o PHP localmente, use:

```
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=ebook
DB_USERNAME=user
DB_PASSWORD=password
```

## Criar o banco

Crie um banco chamado `ebook` no MySQL:

```
CREATE DATABASE ebook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Se estiver usando o container MySQL do `docker-compose.yml`, o banco é criado automaticamente na inicialização.

## Como rodar

Na raiz do projeto:

```
php -S localhost:8000
```

Depois acesse:

`http://localhost:8000`

## Como rodar com Docker

Na raiz do projeto:

```
docker compose up --build
```

A aplicação fica em:

`http://localhost:8000`

O MySQL fica exposto em:

- host: `127.0.0.1`
- porta: `3307`

Observações importantes:

- O `docker-compose.yml` usa as credenciais do seu arquivo `.env`.
- O arquivo `database/schema.sql` é executado na criação inicial do volume do banco.
- Se você já subiu o MySQL anteriormente com outras credenciais ou schema antigo, recrie o volume:

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
  Repositories/
  Services/
  Views/
assets/
  js/
database/
  schema.sql
tests/
  frontend/
  Unit/
index.php
composer.json
package.json
phpunit.xml
```

## Rotas

- `GET /`
- `GET /livros/cadastrar`
- `POST /livros`
- `GET /livros/editar/{id}`
- `POST /livros/atualizar/{id}`
- `POST /livros/excluir/{id}`
- `GET /autores/cadastrar`
- `POST /autores`
- `GET /autores/editar/{id}`
- `POST /autores/atualizar/{id}`
- `POST /autores/excluir/{id}`
- `GET /assuntos/cadastrar`
- `POST /assuntos`
- `GET /assuntos/editar/{id}`
- `POST /assuntos/atualizar/{id}`
- `POST /assuntos/excluir/{id}`
- `GET /relatorios`
- `GET /api/relatorio-livros`

## Observações

- O frontend usa Twig, Tailwind CSS e JavaScript puro.
- O relatório consome o endpoint JSON interno `/api/relatorio-livros`.
- O projeto utiliza MySQL como banco de dados.
- Os testes de frontend usam `Vitest`.
- Os testes de backend usam `PHPUnit`.

## Melhorias

- Inserir páginação - limite de 5 a 10 por página.
