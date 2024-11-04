
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
        console.log(response.json);

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
    table.removeAttribute('onClick');
    table.className = '';
    table.classList.add('info-box', 'bg-gradient-secondary', 'btn-opcoes-mesa');
}

function enableOptionTable(table, table_id, status) {
    if (status === "0") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-success', 'mesa');
    } else if (status === "1") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-info', 'mesa');
    } else if (status === "4") {
        table.className = '';
        table.classList.add('info-box', 'bg-gradient-warning', 'mesa');
    }
    table.setAttribute('onClick', `showModalOptionsTable('${table_id}','${status}')`);

}

function showModalOptionsTable(mesa_id, status) {

    const mesaOcupar = document.querySelector('#div-ocupar-mesa');
    const mesaVincular = document.querySelector('#div-vincular-mesa');
    const mesaFechar = document.querySelector('#div-fechar-mesa');
    const mesaTrocar = document.querySelector('#div-trocar-mesa');
    const mesaPagar = document.querySelector('#div-pagar-mesa');
    const mesaInativar = document.querySelector('#div-inativar-mesa');

    // Define padrão as classes
    mesaOcupar.className = '';
    mesaOcupar.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');

    mesaVincular.className = '';
    mesaVincular.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');

    mesaFechar.className = '';
    mesaFechar.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');

    mesaTrocar.className = '';
    mesaTrocar.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');

    mesaPagar.className = '';
    mesaPagar.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');

    mesaInativar.className = '';
    mesaInativar.classList.add('info-box', 'bg-gradient-info', 'btn-opcoes-mesa');
    document.querySelector('#text-ativacao').innerHTML = "Inativar";

    mesaInativar.setAttribute('onClick', `changeStatusTable('${mesa_id}','4')`);

    if (status === "0" || status === "3") {

        mesaOcupar.setAttribute('onClick', `changeStatusTable('${mesa_id}','1')`);

        disableOptionTable(mesaVincular);
        disableOptionTable(mesaFechar);
        disableOptionTable(mesaTrocar);
        disableOptionTable(mesaPagar);

    } else if (status === "1") {
        disableOptionTable(mesaOcupar);
        disableOptionTable(mesaPagar);
        disableOptionTable(mesaInativar);
    } else if (status === "2") {
        disableOptionTable(mesaOcupar);
        disableOptionTable(mesaFechar);
        disableOptionTable(mesaVincular);
        disableOptionTable(mesaTrocar);
        disableOptionTable(mesaInativar);
    } else if (status === "4") {
        document.querySelector('#text-ativacao').innerHTML = "Ativar";
        mesaInativar.setAttribute('onClick', `changeStatusTable('${mesa_id}','0')`);

        disableOptionTable(mesaOcupar);
        disableOptionTable(mesaVincular);
        disableOptionTable(mesaFechar);
        disableOptionTable(mesaTrocar);
        disableOptionTable(mesaPagar);
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

