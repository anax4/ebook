const REPORT_ENDPOINT = '/api/relatorio-livros';
const ALLOWED_SORTS = ['title', 'year', 'value'];

function normalizeOptionValues(options = []) {
    return options.map((option) => String(option ?? ''));
}

export function validateReportFilters({
    search = '',
    authorId = '',
    year = '',
    sort = '',
    authors = [],
    years = [],
} = {}) {
    const errors = {};
    const normalizedSearch = String(search ?? '').trim();
    const normalizedAuthorId = String(authorId ?? '');
    const normalizedYear = String(year ?? '');
    const normalizedSort = String(sort ?? '');
    const authorOptions = normalizeOptionValues(authors);
    const yearOptions = normalizeOptionValues(years);

    if (normalizedSearch.length > 120) {
        errors['report-search'] = 'A busca deve ter no máximo 120 caracteres.';
    }

    if (normalizedAuthorId !== '' && !authorOptions.includes(normalizedAuthorId)) {
        errors['report-author'] = 'Selecione um autor válido.';
    }

    if (normalizedYear !== '' && !yearOptions.includes(normalizedYear)) {
        errors['report-year'] = 'Selecione um ano válido.';
    }

    if (!ALLOWED_SORTS.includes(normalizedSort)) {
        errors['report-sort'] = 'Selecione uma ordenação válida.';
    }

    return {
        valid: Object.keys(errors).length === 0,
        errors,
    };
}

export function buildReportQuery({
    search = '',
    authorId = '',
    year = '',
    sort = '',
} = {}) {
    const params = new URLSearchParams();
    const normalizedSearch = String(search ?? '').trim();
    const normalizedAuthorId = String(authorId ?? '');
    const normalizedYear = String(year ?? '');
    const normalizedSort = String(sort ?? '');

    if (normalizedSearch !== '') {
        params.set('search', normalizedSearch);
    }

    if (normalizedAuthorId !== '') {
        params.set('autor_id', normalizedAuthorId);
    }

    if (normalizedYear !== '') {
        params.set('anoPublicacao', normalizedYear);
    }

    if (normalizedSort !== '') {
        params.set('sort', normalizedSort);
    }

    const query = params.toString();
    return query ? `${REPORT_ENDPOINT}?${query}` : REPORT_ENDPOINT;
}

export function csvEscape(value) {
    const text = String(value ?? '').replace(/"/g, '""');
    return /[;"\n]/.test(text) ? `"${text}"` : text;
}
