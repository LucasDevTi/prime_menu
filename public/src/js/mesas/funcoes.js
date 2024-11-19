let selecionandoMesas = false;
let mesasSelecionadas = [];

// Array para armazenar os produtos
let produtos = [];
let mesaAtualMenu = [];
// Variável para o valor total
let valorTotal = 0;

async function buscaCliente() {
    const form = document.getElementById('telefoneForm');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('input[name="_token"]').value;

    const overlay = document.querySelector('.overlay');

    try {
        overlay.classList.remove('d-none');
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });

        if (!response.ok) {
            // Se a resposta não for ok, lança um erro
            const errorData = await response.json();
            throw new Error(`ERRO: ${errorData.message}`);
            overlay.classList.add('d-none');
        }

        const data = await response.json();

        const client = data.data;
        const tables = data.tables;
        const selectMesas = document.getElementById('mesas-disponiveis');
        selectMesas.innerHTML = '<option selected value="0">Selecione</option>';

        const inputNameclient = document.getElementById('client_name');
        const inputCpfCnpj = document.getElementById('client_cpf_cnpj');
        const inputRua = document.getElementById('client_rua');
        const inputBairro = document.getElementById('client_bairro');
        const inputNumber = document.getElementById('client_numero');
        const inputComplemento = document.getElementById('client_complemento');
        const inputPhone1 = document.getElementById('client_phone_1');
        const inputCellphone = document.getElementById('client_cellphone');
        const inputEmail = document.getElementById('client_email');
        const inputObs = document.getElementById('client_obs');

        if (data.code == 200) {

            inputNameclient.value = (client.name) ? client.name : '';

            inputCpfCnpj.value = (client.cpf_cnpj) ? client.cpf_cnpj : '';

            if (client.addresses) {
                inputRua.value = (client.addresses[0].street) ? client.addresses[0].street : '';

                inputBairro.value = (client.addresses[0].neighborhood) ? client.addresses[0].neighborhood : '';

                inputNumber.value = (client.addresses[0].number) ? client.addresses[0].number : '';

                inputComplemento.value = (client.addresses[0].complement) ? client.addresses[0].complement : '';
            } else {
                inputRua.value = '';

                inputBairro.value = '';

                inputNumber.value = '';

                inputComplemento.value = '';
            }

            inputCellphone.value = (client.cellphone) ? client.cellphone : '';
            inputEmail.value = (client.email) ? client.email : '';
            inputObs.value = (client.obs) ? client.obs : '';

            tables.forEach(table => {
                const option = document.createElement('option');  // Cria o elemento <option>
                option.value = table.id;                          // Define o valor como o id da mesa
                option.textContent = `Mesa: ${table.id} - ${table.description_status}`;    // Define o texto como o status da mesa
                selectMesas.appendChild(option);                  // Adiciona a opção ao <select>
            });
        }
        // }
        overlay.classList.add('d-none');
        $('#reserva-modal').modal('hide');
        $('#continua-reserva-cliente').modal('show');

    } catch (error) {
        overlay.classList.add('d-none');
    }
}

async function atualizarStatusMesa(mesa_id, status) {

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const mesa = document.querySelector(`div[data-id-mesa="${mesa_id}"]`);

    if (status == "1") {
        showItems(mesa_id);
    }

    try {
        const response = await fetch('/atualizar-status-mesa', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token, // Inclua o token CSRF para segurança
            },
            body: JSON.stringify({ mesa_id: mesa_id, novo_status: status }) // Exemplo de novo status
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`ERRO: ${errorData.message}`);
        }
        // console.log(response.json);

        const data = await response.json();

        if (data.success) {

            $('#opcoes_mesa').modal('hide');

            enableOptionTable(mesa, mesa_id, data.status)
        }

        // Aqui você pode fazer algo após a atualização, como recarregar a lista de mesas

    } catch (error) {
        console.error('Erro ao atualizar o status da mesa:', error);
    }
}

function disableOptionTable(table) {
    if (table) {
        table.removeAttribute('onClick');
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-secondary', 'btn-opcoes-mesa');
    }
}

function enableOptionTable(table, table_id, status) {
    if (status === "0") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-success', 'mesa');
    } else if (status === "1") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-info', 'mesa');
    } else if (status === "2") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-danger', 'mesa');
    } else if (status === "4") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-warning', 'mesa');
    }
    table.setAttribute('onClick', `showModalOptionsTable('${table_id}','${status}')`);

}

function setupOption(table) {
    if (table) {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');
    }
}

function showModalOptionsTable(mesa_id, status) {

    const mesaOcupar = document.querySelector('#div-ocupar-mesa');
    const mesaVincular = document.querySelector('#div-vincular-mesa');
    const mesaFechar = document.querySelector('#div-fechar-mesa');
    const mesaTrocar = document.querySelector('#div-trocar-mesa');
    const mesaPagar = document.querySelector('#div-pagar-mesa');
    const mesaInativar = document.querySelector('#div-inativar-mesa');
    const mesaAdicionarItem = document.querySelector('#div-adicionar-item-mesa');

    // Define padrão as classes
    setupOption(mesaOcupar);
    setupOption(mesaVincular);
    setupOption(mesaFechar);
    setupOption(mesaTrocar);
    setupOption(mesaPagar);
    setupOption(mesaInativar);
    setupOption(mesaAdicionarItem);

    if (mesaVincular) {
        mesaVincular.setAttribute('onClick', `linkTables('${mesa_id}','0','true')`);
    }

    if (mesaInativar) {
        document.querySelector('#text-ativacao').innerHTML = "Inativar";
        mesaInativar.setAttribute('onClick', `changeStatusTable('${mesa_id}','4')`);
    }

    if (mesaFechar) {
        mesaFechar.setAttribute('onClick', `changeStatusTable('${mesa_id}','2')`);
    }

    if (mesaVincular) {
        mesaVincular.setAttribute('onClick', `getMesas ('${mesa_id}','2')`);
    }

    if (status === "0" || status === "3") {

        if (mesaOcupar) {
            mesaOcupar.setAttribute('onClick', `changeStatusTable('${mesa_id}','1')`);
        }

        disableOptionTable(mesaVincular);
        disableOptionTable(mesaFechar);
        disableOptionTable(mesaTrocar);
        disableOptionTable(mesaPagar);
        disableOptionTable(mesaAdicionarItem);

    } else if (status === "1") {

        if (mesaAdicionarItem) {
            mesaAdicionarItem.setAttribute('onClick', `showItems(${mesa_id})`);
        }

        disableOptionTable(mesaOcupar);
        disableOptionTable(mesaPagar);
        disableOptionTable(mesaInativar);
    } else if (status === "2") {

        disableOptionTable(mesaOcupar);
        disableOptionTable(mesaFechar);
        disableOptionTable(mesaVincular);
        disableOptionTable(mesaTrocar);
        disableOptionTable(mesaInativar);
        disableOptionTable(mesaAdicionarItem);

    } else if (status === "4") {
        if (mesaInativar) {
            document.querySelector('#text-ativacao').innerHTML = "Ativar";
            mesaInativar.setAttribute('onClick', `changeStatusTable('${mesa_id}','0')`);
        }

        disableOptionTable(mesaOcupar);
        disableOptionTable(mesaVincular);
        disableOptionTable(mesaFechar);
        disableOptionTable(mesaTrocar);
        disableOptionTable(mesaPagar);
        disableOptionTable(mesaAdicionarItem);

    }
    // else if(status === "1"){
    //     mesaOcupar.removeAttribute('onClick');
    //     mesaOcupar.className = '';
    //     mesaOcupar.classList.add('info-box', 'bg-gradient-secondary', 'btn-opcoes-mesa');
    // }

    $('#opcoes_mesa').modal('show');
}

function changeStatusTable(mesa_id, status) {
    atualizarStatusMesa(mesa_id, status);
}

function linkTables(table_id, table_id_link, showTables = true) {

    const tablesSelect = document.querySelector('#vincular-mesas-disponiveis');

    if (showTables) {
        tablesSelect.style.display = 'block';
    }
}

function ativarModoSelecao() {
    selecionandoMesas = true;
    mesasSelecionadas = []; // Limpa as mesas selecionadas
    document.getElementById('juntarMesasBtn').innerText = "Cancelar Seleção";
    document.getElementById('btn-juntar-mesas').style.display = 'block';
    document.getElementById('btn-reserva').style.display = 'none';

    // Troca o texto do botão para "Cancelar Seleção" e alterna ao clicar novamente
    document.getElementById('juntarMesasBtn').onclick = desativarModoSelecao;
}

function desativarModoSelecao() {
    selecionandoMesas = false;
    mesasSelecionadas = []; // Limpa a seleção
    document.getElementById('juntarMesasBtn').innerText = "Juntar Mesas";
    document.getElementById('juntarMesasBtn').onclick = ativarModoSelecao;

    document.getElementById('btn-juntar-mesas').style.display = 'none';
    document.getElementById('btn-reserva').style.display = 'block';

    // Remove a classe de seleção de todas as mesas
    const mesas = document.querySelectorAll('.mesa-selecionada');
    mesas.forEach(mesa => {
        mesa.classList.remove('mesa-selecionada');
    });
}

function acaoMesa(mesaElemento) {
    const status = mesaElemento.dataset.status;

    if (selecionandoMesas && (status == '0' || status == '1')) {
        selecionarMesa(mesaElemento);
    } else {
        const mesaId = mesaElemento.dataset.idMesa;
        showModalOptionsTable(mesaId, status);
    }
}

function selecionarMesa(mesaElemento) {
    const mesaId = mesaElemento.dataset.idMesa;

    if (mesasSelecionadas.includes(mesaId)) {
        // Se a mesa já está selecionada, removê-la da seleção
        mesasSelecionadas = mesasSelecionadas.filter(id => id !== mesaId);
        mesaElemento.classList.remove("mesa-selecionada");
    } else {
        // Adiciona a mesa à lista de selecionadas
        mesasSelecionadas.push(mesaId);
        mesaElemento.classList.add("mesa-selecionada");
    }

    console.log("Mesas Selecionadas:", mesasSelecionadas);
}

async function juntarMesas() {

    if (mesasSelecionadas.length === 0) {
        $('.alert-success-small-modal').hide();
        $('.alert-error-small-modal').show();
        $('.alert-error-small-modal').html("Precisa de uma mesa principal");
        return
    }

    const mesaPrincipal = $('#mesa_principal').val();

    if (mesasSelecionadas.includes(mesaPrincipal)) {

        resetModal();

        const overlay = document.querySelector('.overlay');
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            overlay.classList.remove('d-none');
            const response = await fetch('/link-tables', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token, // Inclua o token CSRF para segurança
                },
                body: JSON.stringify({ mesasSelecionadas: mesasSelecionadas, mesaPrincipal: mesaPrincipal })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(`ERRO: ${errorData.message}`);
            }

            const data = await response.json();

            if (data.success) {

                location.reload();

                // $('#opcoes_mesa').modal('hide');

                // enableOptionTable(mesa, mesa_id, data.status)
            }
        } catch (error) {
            console.error('Erro ao atualizar o status da mesa:', error);
        }

    } else {
        $('.alert-success-small-modal').hide();
        $('.alert-error-small-modal').show();
        $('.alert-error-small-modal').html("A mesa principal precisa ser uma  das mesas selecionadas.");
        return
    }
    // console.log("Mesas para juntar:", mesasSelecionadas);
}

function resetModal() {
    $('.alert-success-small-modal').hide();
    $('.alert-error-small-modal').hide();
}

document.getElementById('search-input').addEventListener('input', function (event) {
    let searchTerm = event.target.value.toLowerCase(); // Obtém o termo de pesquisa em minúsculas
    let products = document.querySelectorAll('.content-products'); // Seleciona todos os produtos

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

function showModalProdEdit(name, ingredients, price, id) {
    const inputProdName = document.getElementById('product_name');
    const inputProdIngredients = document.getElementById('ingredientes');
    const inputProdPrice = document.getElementById('product_price');
    const inputIdProduto = document.getElementById('id_produto');

    inputProdName.value = name;
    inputProdIngredients.value = ingredients;
    inputProdPrice.value = price;

    document.getElementById('productsForm').action = '/editProduct';
    inputIdProduto.value = id;

    $('#cad-produtos-modal').modal('show');
}


function addProduct(id_produto, valor, mesa_id) {
    // Verifica se o produto já existe no array
    let produto = produtos.find(p => p.id === id_produto);

    if (produto) {
        // Se o produto já existe, aumenta a quantidade e recalcula o valor total
        produto.quantidade += 1;
    } else {
        // Se o produto não existir, cria um novo objeto e adiciona ao array
        produto = { id: id_produto, valor: valor, quantidade: 1 };
        produtos.push(produto);
        mesaAtualMenu = mesa_id;
    }

    // Recalcular o valor total
    valorTotal += valor;

    // Atualiza o valor no input
    const inputProduct = document.getElementById(`qtde_${id_produto}`);
    inputProduct.value = produto.quantidade;

    // Atualiza o valor total na tela
    const spanValorTotal = document.getElementById('valor-total');
    spanValorTotal.textContent = `R$ ${valorTotal.toFixed(2)}`;

    // console.log(produtos);
}

function rmvProduct(id_produto, valor, mesa_id) {
    // Encontra o produto no array
    let produto = produtos.find(p => p.id === id_produto);

    if (produto && produto.quantidade > 0) {
        // Se o produto existir e a quantidade for maior que 0, diminui a quantidade
        produto.quantidade -= 1;

        // Recalcular o valor total
        valorTotal -= valor;

        // Atualiza o valor no input
        const inputProduct = document.getElementById(`qtde_${id_produto}`);
        inputProduct.value = produto.quantidade;

        // Atualiza o valor total na tela
        const spanValorTotal = document.getElementById('valor-total');
        spanValorTotal.textContent = `R$ ${valorTotal.toFixed(2)}`;

        // Se a quantidade chegar a 0, remove o produto do array
        if (produto.quantidade === 0) {
            produtos = produtos.filter(p => p.id !== id_produto);
        }

        mesaAtualMenu = mesa_id;

    }
}

async function setPedido(event) {
    // Impede o envio do formulário caso queira preencher o campo hidden antes
    event.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    // Converte o array de produtos para JSON
    const produtosJson = JSON.stringify(produtos);

    try {
        const response = await fetch('/set-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token, // Inclua o token CSRF para segurança
            },
            body: JSON.stringify({ productsData: produtosJson, mesa_id: mesaAtualMenu }) // Exemplo de novo status
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`ERRO: ${errorData.message}`);
        }

        const data = await response.json();

        if (data.success) {

            location.reload();
        }

        // Aqui você pode fazer algo após a atualização, como recarregar a lista de mesas

    } catch (error) {
        console.error('Erro ao atualizar o status da mesa:', error);
    }
    // Preenche o campo hidden com o JSON dos produtos
    document.getElementById('productsData').value = produtosJson;

    // Agora o formulário pode ser enviado normalmente
    this.submit(); // Isso submete o formulário após preencher o campo hidden
};

async function showItems(mesa_id) {

    $('#opcoes_mesa').modal('hide');
    $('#opcoes_produtos_modal').modal('show');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch('/get-produtos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token, // Inclua o token CSRF para segurança
            },
            body: JSON.stringify({}) // Exemplo de novo status
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`ERRO: ${errorData.message}`);
        }

        const data = await response.json();

        if (data.success) {

            const productContainer = document.getElementById('products-list');
            productContainer.innerHTML = '';

            data.data.forEach(product => {
                const productElement = `
                <div class="content-products col-sm-12">
                    <div class="div-products col-sm-8" id="products-list">
                        <div class="div-item-prod" style="flex:1;">
                            <div class="desc-product">
                                <h2>${product.name}</h2>
                                <span class="ingredients">${product.ingredients}</span>
                                <input type="text" class="form-control" style="width:100%;"  placeholder="Observações" />
                            </div>
                            <div class="price-product" style="margin-top:0px;">
                                R$ ${product.price}
                            </div>
                        </div>
                        <div class="qtde-item text-success">
                            <input type="number" id="qtde_${product.id}" value="0" class="text-success" disabled />
                        </div>
                    </div>
                    <div class="content-qtde">
                        <i class="fa fa-plus-circle plus-qtde" aria-hidden="true" onclick="addProduct(${product.id}, ${product.price}, ${mesa_id})"></i>
                        <i class="fa fa-minus-circle minus-qtde" aria-hidden="true" onclick="rmvProduct(${product.id}, ${product.price}, ${mesa_id})"></i>    
                    </div>
                </div>
            `;
                productContainer.insertAdjacentHTML('beforeend', productElement);
            });
        }

        // Aqui você pode fazer algo após a atualização, como recarregar a lista de mesas

    } catch (error) {
        console.error('Erro ao buscar produtos:', error);
    }
}