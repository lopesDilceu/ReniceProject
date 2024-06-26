<div class="modal fade" id="dados-usuario-modal" tabindex="-1" aria-labelledby="dados-usuario-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="dados-usuario-modal-label">Usuário</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="row my-2">
                    <div class="col-12 col-lg-6 col-sm-12 text-center mb-3 mb-md-3 mb-lg-0">
                        <img id="UserProfileImage" src="" alt="Logo" class="img-fluid rounded" width="40%">
                    </div>
                    <div class="col-12 col-lg-6 col-sm-12">
                        <h2 class="h2">Informações do Usuário</h2>
                        <div class="form-group row my-2">
                            <label for="CodigoUsuario" class="col-sm-12 col-form-label"><b>Código:</b></label>
                            <div class="col-sm-12 form-control">
                                <span id="CodigoUsuario"></span>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="NomeUsuario" class="col-sm-12 col-form-label"><b>Nome:</b></label>
                            <div class="col-sm-12 form-control">
                                <span id="NomeUsuario"></span>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="CpfUsuario" class="col-sm-12 col-form-label"><b>CPF:</b></label>
                            <div class="col-sm-12 form-control">
                                <span id="CpfUsuario"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-12 col-sm-12">
                        <div class="row g-2">
                            <div class="col-md-6 col-sm-12">
                                <label for="NascimentoUsuario" class="col-sm-12 col-form-label"><b>Data de Nascimento:</b></label>
                                <div class="col-sm-12 form-control">
                                    <span id="NascimentoUsuario"></span>
                                </div>
                                <label for="CriacaoUsuario" class="col-sm-12 col-form-label"><b>Data de Criação:</b></label>
                                <div class="col-sm-12 form-control">
                                    <span id="CriacaoUsuario"></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label for="EmailUsuario" class="col-sm-12 col-form-label"><b>Email:</b></label>
                                <div class="col-sm-12 form-control">
                                    <span id="EmailUsuario"></span>
                                </div>
                                <label for="AdmUsuario" class="col-sm-12 col-form-label"><b>Administrador:</b></label>
                            <div class="col-sm-12 form-control">
                                <span id="AdmUsuario"></span>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                <button type="button" class="btn btn-lg btn-dark" id="editButton">Editar</button>
                <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
