<?php

namespace Tests\Unit;

use App\Repositories\RelatorioLivrosRepository;
use App\Services\RelatorioLivrosService;
use PHPUnit\Framework\TestCase;

class RelatorioLivrosServiceTest extends TestCase
{
    public function test_groups_books_by_author(): void
    {
        $service = $this->makeService($this->makeRows());

        $report = $service->getReport([]);
        $groupsByAuthorId = $this->indexGroupsByAuthorId($report['data']);

        $this->assertSame(3, $report['meta']['total']);
        $this->assertCount(3, $report['data']);
        $this->assertSame(['Clean Code', 'Domain-Driven Design'], array_column($groupsByAuthorId[1]['livros'], 'titulo'));
        $this->assertSame(['Arquitetura Limpa'], array_column($groupsByAuthorId[2]['livros'], 'titulo'));
    }

    public function test_filters_by_author_year_and_search(): void
    {
        $service = $this->makeService($this->makeRows());

        $report = $service->getReport([
            'autor_id' => '2',
            'anoPublicacao' => '2023',
            'search' => 'arquitetura',
            'sort' => 'title',
        ]);

        $this->assertSame(1, $report['meta']['total']);
        $this->assertCount(1, $report['data']);
        $this->assertSame(2, $report['data'][0]['autor_id']);
        $this->assertSame(['Arquitetura Limpa'], array_column($report['data'][0]['livros'], 'titulo'));
    }

    public function test_sorts_books_by_value_desc(): void
    {
        $service = $this->makeService($this->makeRows());

        $report = $service->getReport([
            'sort' => 'value',
        ]);

        $groupsByAuthorId = $this->indexGroupsByAuthorId($report['data']);

        $this->assertSame(
            ['Domain-Driven Design', 'Clean Code'],
            array_column($groupsByAuthorId[1]['livros'], 'titulo')
        );
    }

    public function test_rejects_invalid_sort(): void
    {
        $service = $this->makeService($this->makeRows());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O campo da ordenação deve ser: título, ano ou valor.');

        $service->getReport([
            'sort' => 'random',
        ]);
    }

    private function makeService(array $rows): RelatorioLivrosService
    {
        return new RelatorioLivrosService(new FakeRelatorioLivrosRepository($rows));
    }

    private function indexGroupsByAuthorId(array $groups): array
    {
        $indexed = [];

        foreach ($groups as $group) {
            $indexed[(int) $group['autor_id']] = $group;
        }

        return $indexed;
    }

    private function makeRows(): array
    {
        return [
            [
                'autor_id' => 2,
                'autor_nome' => 'Ana Souza',
                'livro_id' => 20,
                'titulo' => 'Arquitetura Limpa',
                'editora' => 'Casa do Código',
                'anoPublicacao' => '2023',
                'valor' => '99.90',
                'assuntos_livro' => 'Arquitetura, Software',
                'autores_livro' => 'Ana Souza',
            ],
            [
                'autor_id' => 1,
                'autor_nome' => 'Bruno Lima',
                'livro_id' => 10,
                'titulo' => 'Clean Code',
                'editora' => 'Prentice Hall',
                'anoPublicacao' => '2008',
                'valor' => '80.00',
                'assuntos_livro' => 'Boas práticas',
                'autores_livro' => 'Bruno Lima, Carla Dias',
            ],
            [
                'autor_id' => 1,
                'autor_nome' => 'Bruno Lima',
                'livro_id' => 11,
                'titulo' => 'Domain-Driven Design',
                'editora' => 'Addison-Wesley',
                'anoPublicacao' => '2003',
                'valor' => '120.00',
                'assuntos_livro' => 'DDD, Arquitetura',
                'autores_livro' => 'Bruno Lima',
            ],
            [
                'autor_id' => 3,
                'autor_nome' => 'Carla Dias',
                'livro_id' => 30,
                'titulo' => 'JavaScript Patterns',
                'editora' => "O'Reilly",
                'anoPublicacao' => '2010',
                'valor' => '65.00',
                'assuntos_livro' => 'Frontend, JavaScript',
                'autores_livro' => 'Carla Dias',
            ],
        ];
    }
}

class FakeRelatorioLivrosRepository extends RelatorioLivrosRepository
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function getAll(): array
    {
        return $this->rows;
    }
}
