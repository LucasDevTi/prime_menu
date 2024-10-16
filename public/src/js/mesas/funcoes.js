async function buscaCliente() {

    let telefone = document.querySelector('#telefone').value();

    try {
        let url = '/buscar-cliente-telefone';

        let response = await fetch(url);

        if (response.ok) {
            throw new (`Erro: ${response.error}`);
        }

        let data = await response.json();

    } catch (error) {
        console.log(error);
    }


}