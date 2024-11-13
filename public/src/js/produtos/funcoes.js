function formatCurrency(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    value = (value / 100).toFixed(2);           // Divide por 100 para obter os centavos e mantém 2 casas decimais
    value = value.replace('.', ',');            // Substitui o ponto decimal por vírgula
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Adiciona o ponto como separador de milhar
    input.value = `R$ ${value}`;                // Adiciona o símbolo de Real (R$) ao início
}

document.getElementById('search-input').addEventListener('input', function (event) {
    let searchTerm = event.target.value.toLowerCase(); // Obtém o termo de pesquisa em minúsculas
    let products = document.querySelectorAll('.div-products'); // Seleciona todos os produtos

    products.forEach(function (product) {
        let productName = product.querySelector('.desc-product h2').textContent.toLowerCase(); // Nome do produto
        // Verifica se o nome do produto inclui o termo de pesquisa
        if (productName.includes(searchTerm)) {
            product.style.display = ''; // Exibe o produto
        } else {
            product.style.display = 'none'; // Oculta o produto
        }
    });
});