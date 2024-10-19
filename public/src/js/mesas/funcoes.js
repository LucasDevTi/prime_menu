
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

        console.log(data.data);
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

            inputRua.value = (client.address[0].street) ? client.address[0].street : '';
            inputBairro.value = (client.address[0].neighborhood) ? client.address[0].neighborhood : '';
            inputNumber.value = (client.address[0].number) ? client.address[0].number : '';
            inputComplemento.value = (client.address[0].complement) ? client.address[0].complement : '';
            inputPhone1.value = (client.phone_1) ? client.phone_1 : '';
            inputCellphone.value = (client.cellphone) ? client.cellphone : '';
            inputEmail.value = (client.email) ? client.email : '';
            inputObs.value = (client.obs) ? client.obs : '';
        }

        overlay.classList.add('d-none');
        $('#reserva-modal').modal('hide');
        $('#continua-reserva-cliente').modal('show');
    } catch (error) {
        overlay.classList.add('d-none');
    }


}

