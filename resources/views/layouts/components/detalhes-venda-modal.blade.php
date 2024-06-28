<div class="modal fade" id="detalhes-venda-modal" tabindex="-1" aria-labelledby="detalhes-venda-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="detalhes-venda-modal-label">Detalhes da Compra</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="row my-2">
                    <div class="col-12">
                        <h2 class="h2">Informações da Compra</h2>
                        <div class="form-group row my-2">
                            <label for="codigoVenda" class="col-sm-4 col-form-label"><b>Código da Compra:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="codigoVenda" readonly name="ve_id">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="dataVenda" class="col-sm-4 col-form-label"><b>Data da Compra:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dataVenda" readonly name="ve_data_venda">
                            </div>
                        </div>
                        <h2 class="h2">Produtos Comprados</h2>
                        <div class="table-responsive border  rounded" style="height: 400px; overflow-y: auto;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Código</th>
                                        <th scope="col">Produto</th>
                                        <th scope="col">Quantidade</th>
                                        <th scope="col">Preço Unitário</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="detalhes-produtos">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
