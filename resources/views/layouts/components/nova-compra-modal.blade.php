<div class="modal fade" id="nova-compra-modal" tabindex="-1" aria-labelledby="nova-compra-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="nova-compra-modal-label">Nova Compra</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="row my-2">
                    <div class="col-12 col-lg-6 col-sm-12 text-center mb-3 mb-md-3 mb-lg-0">
                        <!-- Aqui pode adicionar uma imagem ou informações sobre o produto, se desejar -->
                        <img src="{{ asset('images/logo/renice-logo-down.png')}}" alt="Logo" class="img-fluid rounded" width="50%">
                        imagem do produto
                    </div>
                    <div class="col-12 col-lg-6 col-sm-12">
                        <h2 class="h2">Informações da Compra</h2>
                        <div class="form-group row my-2">
                            <label for="produtoCodigo" class="col-sm-4 col-form-label"><b>Código do Produto:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="produtoCodigo">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="quantidade" class="col-sm-4 col-form-label"><b>Quantidade:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="quantidade">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="precoUnitario" class="col-sm-4 col-form-label"><b>Preço Unitário:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="precoUnitario">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                <button type="button" class="btn btn-lg btn-dark" id="saveNovaCompra">Salvar</button>
                <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- <script>
    document.addEventListener('DOMContentLoaded', (event) => {
        document.getElementById('saveNovaCompra').addEventListener('click', function() {
            const novaCompra = {
                codigoProduto: document.getElementById('produtoCodigo').value,
                quantidade: document.getElementById('quantidade').value,
                precoUnitario: document.getElementById('precoUnitario').value
            };

            console.log('Nova Compra:', novaCompra);
            // Aqui você pode adicionar o código para enviar os dados para o servidor

            // Fechar o modal após salvar
            const novaCompraModal = new bootstrap.Modal(document.getElementById('nova-compra-modal'));
            novaCompraModal.hide();
        });
    });
</script> -->