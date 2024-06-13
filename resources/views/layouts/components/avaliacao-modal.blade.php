<div class="modal fade" id="avaliacao-modal" tabindex="-1" aria-labelledby="avaliacao-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="avaliacao-modal-label">Avaliação</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="row my-2">
                    <div class="col-12 col-lg-6 col-sm-12 text-center mb-3 mb-md-3 mb-lg-0">
                        <img src="{{ asset('images/logo/renice-logo-down.png')}}" alt="Logo" class="img-fluid rounded" width="50%">
                    </div>
                    <div class="col-12 col-lg-6 col-sm-12">
                        <h2 class="h2">Dados da Avaliação</h2>
                        <div class="form-group row my-2">
                            <label for="produtoCodigo" class="col-sm-4 col-form-label"><b>Código do Produto:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="produtoCodigo" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="usuarioCodigo" class="col-sm-4 col-form-label"><b>Código do Usuário:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="usuarioCodigo" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="usuarioNome" class="col-sm-4 col-form-label"><b>Nome do Usuário:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="usuarioNome" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="nota" class="col-sm-4 col-form-label"><b>Nota:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="nota" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="comentario" class="col-sm-4 col-form-label"><b>Comentário:</b></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" id="comentario" rows="3" readonly></textarea>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="dataAvaliacao" class="col-sm-4 col-form-label"><b>Data da Avaliação:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dataAvaliacao" value="" readonly>
                            </div>
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
