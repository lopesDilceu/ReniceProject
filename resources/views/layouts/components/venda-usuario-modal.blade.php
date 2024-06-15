<div class="modal fade" id="venda-usuario-modal" tabindex="-1" aria-labelledby="venda-usuario-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="venda-usuario-modal-label">Detalhes da Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <h6 class="fw-bold">Informações da Compra</h6>
                        <div class="mb-2">
                            <label for="codigoVenda" class="form-label"><b>Código da Compra:</b></label>
                            <input type="text" class="form-control" id="codigoVenda" readonly>
                        </div>
                        <div class="mb-2">
                            <label for="dataVenda" class="form-label"><b>Data da Compra:</b></label>
                            <input type="text" class="form-control" id="dataVenda" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <h6 class="fw-bold">Produtos Comprados</h6>
                        <div class="table-responsive border rounded" style="height: 400px; overflow-y: auto;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Código do Produto</th>
                                        <th scope="col">Nome do Produto</th>
                                        <th scope="col">Quantidade</th>
                                        <th scope="col">Preço Unitário</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Avaliar</th>
                                    </tr>
                                </thead>
                                <tbody id="detalhes-produtos">
                                    <!-- Aqui serão inseridos dinamicamente os detalhes dos produtos -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
