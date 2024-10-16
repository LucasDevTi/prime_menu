document.getElementById('telefoneForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    let url = '/buscar-cliente-telefone';
    const csrfToken = document.querySelector('input[name="_token"]').value;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });

        if (response.ok) {
            throw new (`ERRO: ${response.error}`);
        }

        const data = await response.json();
        console.log(data);
    } catch (error) {

    }
});

