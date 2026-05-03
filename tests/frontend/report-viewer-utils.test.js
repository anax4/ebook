import { describe, expect, it } from 'vitest';

import {
    buildReportQuery,
    csvEscape,
    validateReportFilters,
} from '../../assets/js/report-viewer-utils.js';

describe('validateReportFilters', () => {
    it('returns an error when the search exceeds 120 characters', () => {
        const result = validateReportFilters({
            search: 'a'.repeat(121),
            sort: 'title',
        });

        expect(result).toEqual({
            valid: false,
            errors: {
                'report-search': 'A busca deve ter no máximo 120 caracteres.',
            },
        });
    });

    it('returns an error when the sort is not allowed', () => {
        const result = validateReportFilters({
            search: 'Clean Code',
            sort: 'random',
        });

        expect(result).toEqual({
            valid: false,
            errors: {
                'report-sort': 'Selecione uma ordenação válida.',
            },
        });
    });
});

describe('buildReportQuery', () => {
    it('builds the report query with only the filled filters', () => {
        const result = buildReportQuery({
            search: 'clean code',
            authorId: '3',
            year: '2024',
            sort: 'year',
        });

        expect(result).toBe('/api/relatorio-livros?search=clean+code&autor_id=3&anoPublicacao=2024&sort=year');
    });
});

describe('csvEscape', () => {
    it('wraps delimiters and quotes so the CSV remains valid', () => {
        expect(csvEscape('Livro; "A"')).toBe('"Livro; ""A"""');
    });
});
