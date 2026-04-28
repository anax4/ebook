<?php

namespace App\Services;

use App\Repositories\RelatorioLivrosRepository;

class RelatorioLivrosService
{
    private $repository;

    public function __construct(?RelatorioLivrosRepository $repository = null)
    {
        $this->repository = $repository ?? new RelatorioLivrosRepository();
    }

    public function getReport(array $params): array
    {
        $filters = $this->normalizeFilters($params);
        $rows = $this->repository->getAll();
        $rows = $this->applyFilters($rows, $filters);
        $rows = $this->sortRows($rows, $filters['sort']);

        $data = $this->groupByAuthor($rows);

        return [
            'data' => $data,
            'meta' => [
                'total' => count($data),
            ],
        ];
    }

    private function normalizeFilters(array $params): array
    {
        $search = trim((string) ($params['search'] ?? ''));
        $autorId = $params['autor_id'] ?? null;
        $anoPublicacao = trim((string) ($params['anoPublicacao'] ?? ''));
        $sort = trim((string) ($params['sort'] ?? 'title'));

        if ($search !== '' && mb_strlen($search) > 120) {
            throw new \InvalidArgumentException('O campo de busca deve ter no máximo 120 caracteres.');
        }

        if ($autorId !== null && $autorId !== '') {
            if (!ctype_digit((string) $autorId) || (int) $autorId < 1) {
                throw new \InvalidArgumentException('O campo autor deve ser selecionado.');
            }

            $autorId = (int) $autorId;
        } else {
            $autorId = null;
        }

        if ($anoPublicacao !== '' && !preg_match('/^\d{4}$/', $anoPublicacao)) {
            throw new \InvalidArgumentException('O campo ano deve conter 4 dígitos.');
        }

        if (!in_array($sort, ['title', 'year', 'value'], true)) {
            throw new \InvalidArgumentException('O campo da ordenação deve ser: título, ano ou valor.');
        }

        return [
            'search' => $search,
            'autor_id' => $autorId,
            'anoPublicacao' => $anoPublicacao !== '' ? $anoPublicacao : null,
            'sort' => $sort,
        ];
    }

    private function applyFilters(array $rows, array $filters): array
    {
        $search = $this->normalizeText($filters['search']);

        return array_values(array_filter($rows, function (array $row) use ($filters, $search) {
            if ($filters['autor_id'] !== null && (int) $row['autor_id'] !== $filters['autor_id']) {
                return false;
            }

            if ($filters['anoPublicacao'] !== null && (string) $row['anoPublicacao'] !== $filters['anoPublicacao']) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            foreach ([$row['titulo'] ?? '', $row['autor_nome'] ?? '', $row['autores_livro'] ?? '', $row['assuntos_livro'] ?? ''] as $field) {
                if (strpos($this->normalizeText($field), $search) !== false) {
                    return true;
                }
            }

            return false;
        }));
    }

    private function sortRows(array $rows, string $sort): array
    {
        usort($rows, function (array $left, array $right) use ($sort) {
            if ($sort === 'year') {
                $comparison = (int) $right['anoPublicacao'] <=> (int) $left['anoPublicacao'];
                return $comparison !== 0 ? $comparison : strcasecmp($left['titulo'], $right['titulo']);
            }

            if ($sort === 'value') {
                $comparison = (float) $right['valor'] <=> (float) $left['valor'];
                return $comparison !== 0 ? $comparison : strcasecmp($left['titulo'], $right['titulo']);
            }

            $comparison = strcasecmp($left['titulo'], $right['titulo']);
            return $comparison !== 0 ? $comparison : strcasecmp($left['autor_nome'], $right['autor_nome']);
        });

        return $rows;
    }

    private function groupByAuthor(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $authorId = (int) $row['autor_id'];

            if (!isset($grouped[$authorId])) {
                $grouped[$authorId] = [
                    'autor_id' => $authorId,
                    'autor_nome' => $row['autor_nome'],
                    'livros' => [],
                ];
            }

            $grouped[$authorId]['livros'][] = [
                'id' => (int) $row['livro_id'],
                'titulo' => $row['titulo'],
                'editora' => $row['editora'],
                'anoPublicacao' => $row['anoPublicacao'],
                'valor' => (float) $row['valor'],
                'assuntos_livro' => $row['assuntos_livro'] ?? '',
                'autores_livro' => $row['autores_livro'] ?? '',
            ];
        }

        return array_values($grouped);
    }

    private function normalizeText(string $value): string
    {
        $normalized = mb_strtolower(trim($value), 'UTF-8');

        if (class_exists('\Normalizer')) {
            $normalized = \Normalizer::normalize($normalized, \Normalizer::FORM_D);
            $normalized = preg_replace('/\p{Mn}+/u', '', $normalized) ?? $normalized;
        }

        return $normalized;
    }
}
