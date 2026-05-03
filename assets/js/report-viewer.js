import { buildReportQuery, csvEscape, validateReportFilters } from './report-viewer-utils.js';

(() => {
    const state = {
        data: [],
        meta: { total: 0 },
        authors: [],
        years: [],
        searchTimer: null,
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(value || 0);
    }

    function setHidden(element, hidden) {
        if (element) {
            element.classList.toggle('hidden', hidden);
        }
    }

    function updateStatus(message) {
        const status = document.getElementById('report-status');
        if (status) {
            status.textContent = message;
        }
    }

    function updateTotal() {
        const total = document.getElementById('report-total');
        if (total) {
            total.textContent = String(state.meta.total || 0);
        }
    }

    function setFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(`${fieldId}-error`);

        if (!field || !error) {
            return;
        }

        if (message) {
            field.classList.add('border-danger');
            field.classList.add('focus:border-danger');
            field.classList.add('focus:ring-danger/10');
            field.setAttribute('aria-invalid', 'true');
            error.textContent = message;
            error.classList.remove('hidden');
            return;
        }

        field.classList.remove('border-danger');
        field.classList.remove('focus:border-danger');
        field.classList.remove('focus:ring-danger/10');
        field.removeAttribute('aria-invalid');
        error.textContent = '';
        error.classList.add('hidden');
    }

    function clearFieldErrors() {
        ['report-search', 'report-author', 'report-year', 'report-sort'].forEach((fieldId) => {
            setFieldError(fieldId, '');
        });
    }

    function validateFilters() {
        const search = document.getElementById('report-search');
        const author = document.getElementById('report-author');
        const year = document.getElementById('report-year');
        const sort = document.getElementById('report-sort');
        const validation = validateReportFilters({
            search: search.value,
            authorId: author.value,
            year: year.value,
            sort: sort.value,
            authors: [...author.options]
                .map((option) => option.value)
                .filter((value) => value !== ''),
            years: [...year.options]
                .map((option) => option.value)
                .filter((value) => value !== ''),
        });

        clearFieldErrors();
        Object.entries(validation.errors).forEach(([fieldId, message]) => {
            setFieldError(fieldId, message);
        });

        return validation;
    }

    function buildQuery() {
        return buildReportQuery({
            search: document.getElementById('report-search').value,
            authorId: document.getElementById('report-author').value,
            year: document.getElementById('report-year').value,
            sort: document.getElementById('report-sort').value,
        });
    }

    function renderAuthorOptions() {
        const select = document.getElementById('report-author');
        const currentValue = select.value;

        select.innerHTML = '<option value="">Todos os autores</option>';

        state.authors.forEach((author) => {
            const option = document.createElement('option');
            option.value = String(author.autor_id);
            option.textContent = author.autor_nome;
            select.appendChild(option);
        });

        if ([...select.options].some((option) => option.value === currentValue)) {
            select.value = currentValue;
        }
    }

    function renderYearOptions() {
        const select = document.getElementById('report-year');
        const currentValue = select.value;

        select.innerHTML = '<option value="">Todos os anos</option>';

        state.years.forEach((year) => {
            const option = document.createElement('option');
            option.value = String(year);
            option.textContent = String(year);
            select.appendChild(option);
        });

        if ([...select.options].some((option) => option.value === currentValue)) {
            select.value = currentValue;
        }
    }

    function updateAuthorAndYearPools(payload) {
        payload.data.forEach((group) => {
            if (!state.authors.some((author) => author.autor_id === group.autor_id)) {
                state.authors.push({
                    autor_id: group.autor_id,
                    autor_nome: group.autor_nome,
                });
            }

            group.livros.forEach((book) => {
                const year = Number(book.anoPublicacao || 0);
                if (year > 0 && !state.years.includes(year)) {
                    state.years.push(year);
                }
            });
        });

        state.authors.sort((left, right) => left.autor_nome.localeCompare(right.autor_nome, 'pt-BR'));
        state.years.sort((left, right) => right - left);
    }

    function renderGroups() {
        const groupsRoot = document.getElementById('report-groups');
        const empty = document.getElementById('report-empty');

        if (state.data.length === 0) {
            groupsRoot.innerHTML = '';
            setHidden(empty, false);
            return;
        }

        setHidden(empty, true);
        groupsRoot.innerHTML = state.data
            .map((group) => `
                <section class="border-b border-secondary/10 pb-6 last:border-b-0">
                    <div class="mb-4">
                        <h2 class="font-display text-2xl text-ink">${escapeHtml(group.autor_nome)}</h2>
                        <p class="text-sm text-secondary">${group.livros.length} livro${group.livros.length > 1 ? 's' : ''}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-left text-sm">
                            <thead>
                                <tr class="border-b border-secondary/15 text-secondary">
                                    <th class="px-3 py-2 font-semibold">ID</th>
                                    <th class="px-3 py-2 font-semibold">Título</th>
                                    <th class="px-3 py-2 font-semibold">Editora</th>
                                    <th class="px-3 py-2 font-semibold">Ano</th>
                                    <th class="px-3 py-2 font-semibold">Valor</th>
                                    <th class="px-3 py-2 font-semibold">Autores</th>
                                    <th class="px-3 py-2 font-semibold">Assuntos</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${group.livros.map((book) => `
                                    <tr class="border-b border-secondary/10 align-top last:border-b-0">
                                        <td class="px-3 py-3 text-ink">${book.id}</td>
                                        <td class="px-3 py-3 text-ink">${escapeHtml(book.titulo)}</td>
                                        <td class="px-3 py-3 text-secondary">${escapeHtml(book.editora)}</td>
                                        <td class="px-3 py-3 text-secondary">${escapeHtml(book.anoPublicacao)}</td>
                                        <td class="px-3 py-3 text-secondary">${formatCurrency(Number(book.valor || 0))}</td>
                                        <td class="px-3 py-3 text-secondary">${escapeHtml(book.autores_livro || '')}</td>
                                        <td class="px-3 py-3 text-secondary">${escapeHtml(book.assuntos_livro || '')}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </section>
            `)
            .join('');
    }

    async function fetchReport() {
        const loading = document.getElementById('report-loading');
        const error = document.getElementById('report-error');
        const validation = validateFilters();

        if (!validation.valid) {
            state.data = [];
            state.meta = { total: 0 };
            renderGroups();
            updateTotal();
            setHidden(error, true);
            updateStatus('Corrija os filtros destacados para consultar o relatório.');
            return;
        }

        setHidden(loading, false);
        setHidden(error, true);
        updateStatus('Carregando relatório...');

        try {
            const response = await fetch(buildQuery(), {
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Não foi possível carregar o relatório.');
            }

            state.data = payload.data || [];
            state.meta = payload.meta || { total: 0 };
            updateAuthorAndYearPools(payload);
            renderAuthorOptions();
            renderYearOptions();
            renderGroups();
            updateTotal();
            updateStatus(`Relatório carregado com ${state.meta.total || 0} autor(es).`);
        } catch (exception) {
            state.data = [];
            state.meta = { total: 0 };
            renderGroups();
            updateTotal();
            error.textContent = exception.message || 'Falha inesperada ao carregar o relatório.';
            setHidden(error, false);
            updateStatus('Falha ao consultar a API do relatório.');
        } finally {
            setHidden(loading, true);
        }
    }

    function exportCsv() {
        const header = ['autor_nome', 'id', 'titulo', 'editora', 'anoPublicacao', 'valor', 'assuntos_livro', 'autores_livro'];
        const lines = [header.map(csvEscape).join(';')];

        state.data.forEach((group) => {
            group.livros.forEach((book) => {
                lines.push([
                    group.autor_nome,
                    book.id,
                    book.titulo,
                    book.editora,
                    book.anoPublicacao,
                    Number(book.valor || 0).toFixed(2),
                    book.assuntos_livro || '',
                    book.autores_livro || '',
                ].map(csvEscape).join(';'));
            });
        });

        const blob = new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `relatorio-livros-${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function bindEvents() {
        document.getElementById('report-search').addEventListener('input', () => {
            validateFilters();
            clearTimeout(state.searchTimer);
            state.searchTimer = setTimeout(fetchReport, 250);
        });

        document.getElementById('report-author').addEventListener('change', () => {
            validateFilters();
            fetchReport();
        });
        document.getElementById('report-year').addEventListener('change', () => {
            validateFilters();
            fetchReport();
        });
        document.getElementById('report-sort').addEventListener('change', () => {
            validateFilters();
            fetchReport();
        });

        document.getElementById('report-clear').addEventListener('click', () => {
            document.getElementById('report-search').value = '';
            document.getElementById('report-author').value = '';
            document.getElementById('report-year').value = '';
            document.getElementById('report-sort').value = 'title';
            clearFieldErrors();
            fetchReport();
        });

        document.getElementById('report-reload').addEventListener('click', fetchReport);
        document.getElementById('report-export').addEventListener('click', exportCsv);
        document.getElementById('report-print').addEventListener('click', () => window.print());
    }

    function boot() {
        if (!document.getElementById('report-groups')) {
            return;
        }

        bindEvents();
        fetchReport();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
