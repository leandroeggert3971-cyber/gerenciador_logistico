
document.addEventListener("DOMContentLoaded", function () {

    const produtoSelect = document.getElementById("produto_select");
    const quantidadeInput = document.getElementById("quantidade_produto");
    const botaoAdicionar = document.getElementById("adicionar_produto");
    const listaSelecionados = document.getElementById("produtos_selecionados");

    let produtosAdicionados = [];

    botaoAdicionar.addEventListener("click", function () {

        const produtoId = produtoSelect.value;
        const produtoNome = produtoSelect.options[produtoSelect.selectedIndex].text;
        const quantidade = parseInt(quantidadeInput.value);

        if (produtoId === "") {
            alert("Selecione um produto.");
            return;
        }

        if (!Number.isInteger(quantidade) || quantidade < 1) {
            alert("Informe uma quantidade válida.");
            return;
        }

        if (produtosAdicionados.includes(produtoId)) {
            alert("Este produto já foi adicionado.");
            return;
        }

        produtosAdicionados.push(produtoId);

        if (listaSelecionados.querySelector("p")) {
            listaSelecionados.innerHTML = "";
        }

        const item = document.createElement("div");
        item.className = "alert alert-info d-flex justify-content-between align-items-center";

        item.innerHTML = `
            <span>${produtoNome} - Quantidade: ${quantidade}</span>
            <button type="button" class="btn btn-danger btn-sm">Remover</button>
            <input type="hidden" name="produtos[]" value="${produtoId}">
            <input type="hidden" name="quantidades[${produtoId}]" value="${quantidade}">
        `;

        item.querySelector("button").addEventListener("click", function () {
            produtosAdicionados = produtosAdicionados.filter(id => id !== produtoId);
            item.remove();

            if (produtosAdicionados.length === 0) {
                listaSelecionados.innerHTML = "<p>Nenhum produto adicionado.</p>";
            }
        });

        listaSelecionados.appendChild(item);

        produtoSelect.value = "";
        quantidadeInput.value = "1";
    });

});
