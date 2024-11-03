
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

        // if (data.data) {
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

        // console.log(client.address[0]);
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

            // inputPhone1.value = (client.phone_1) ? client.phone_1 : '';
            inputCellphone.value = (client.cellphone) ? client.cellphone : '';
            inputEmail.value = (client.email) ? client.email : '';
            inputObs.value = (client.obs) ? client.obs : '';

            tables.forEach(table => {
                const option = document.createElement('option');  // Cria o elemento <option>
                option.value = table.id;                          // Define o valor como o id da mesa
                option.textContent = `Mesa: ${table.id} - ${table.description_status}`;    // Define o texto como o status da mesa
                selectMesas.appendChild(option);                  // Adiciona a opção ao <select>
            });

            console.log(tables);
        }
        // }
        overlay.classList.add('d-none');
        $('#reserva-modal').modal('hide');
        $('#continua-reserva-cliente').modal('show');

    } catch (error) {
        overlay.classList.add('d-none');
    }
}


/* async function buscaEstadoMesa(mesa_id) {
    try {
        const response = await fetch(`/get-status-mesa?mesa_id=${mesa_id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`ERRO: ${errorData.message}`);
        }

        const data = await response.json();
        console.log(data.status); // Aqui você pode manipular a resposta como desejar

        if(data.status === 0){
            console.log("liberada");
        }

    } catch (error) {
        console.error(error); // Lidar com o erro aqui
    }
}
*/

async function atualizarStatusMesa(mesa_id) {

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const mesa = document.querySelector(`div[data-id-mesa="${mesa_id}"]`);

    try {
        const response = await fetch('/atualizar-status-mesa', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token, // Inclua o token CSRF para segurança
            },
            body: JSON.stringify({ mesa_id: mesa_id, novo_status: 1 }) // Exemplo de novo status
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`ERRO: ${errorData.message}`);
        }

        const data = await response.json();

        if(data.success){
            mesa.setAttribute('onClick', `acaoMesa('${mesa_id}','1')`);
            mesa.className = '';
            mesa.classList.add('info-box', 'bg-gradient-info', 'mesa');
        }

        // Aqui você pode fazer algo após a atualização, como recarregar a lista de mesas

    } catch (error) {
        console.error('Erro ao atualizar o status da mesa:', error);
    }
}


function acaoMesa(mesa_id, status) {

    console.log(status);
    if (status === "0") {
        const TableConfirm = confirm("Deseja ocupar essa mesa?");
        if (TableConfirm) {
            atualizarStatusMesa(mesa_id)
        }
    }
    // buscaEstadoMesa(mesa_id);
}
