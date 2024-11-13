function formatCurrency(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    value = (value / 100).toFixed(2);           // Divide por 100 para obter os centavos e mantém 2 casas decimais
    value = value.replace('.', ',');            // Substitui o ponto decimal por vírgula
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Adiciona o ponto como separador de milhar
    input.value = `R$ ${value}`;                // Adiciona o símbolo de Real (R$) ao início
}