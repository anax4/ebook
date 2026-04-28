(() => {
    const input = document.getElementById('valor');

    if (!input) {
        return;
    }

    const formatar = (valor) => {
        const digitos = String(valor ?? '').replace(/\D/g, '');

        if (digitos === '') {
            return '';
        }

        return (Number.parseInt(digitos, 10) / 100).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const aplicarMascara = () => {
        input.value = formatar(input.value);

        if (typeof input.setSelectionRange === 'function') {
            const fim = input.value.length;
            input.setSelectionRange(fim, fim);
        }
    };

    aplicarMascara();
    input.addEventListener('focus', aplicarMascara);
    input.addEventListener('input', aplicarMascara);
    input.addEventListener('blur', aplicarMascara);
})();
