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
                            <img src="{{ asset('images/logo/renice-logo-down.png')}}" alt="Logo" class="img-fluid rounded" width="50%">
                        </div>
                        <div class="col-12 col-lg-6 col-sm-12">
                            <h2 class="h2">Informações do Novo Usuário</h2>
                            <div class="form-group row my-2">
                                <label for="CodigoUsuario" name="CodigoUsuario" class="col-sm-4 col-form-label"><b>Código:</b></label>
                                <div class="col-sm-8">
                                    <span id="CodigoUsuario" name="us_id"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="NomeUsuario" class="col-sm-4 col-form-label"><b>Nome:</b></label>
                                <div class="col-sm-8">
                                    <span id="NomeUsuario" name="name"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="CpfUsuario" class="col-sm-4 col-form-label"><b>CPF:</b></label>
                                <div class="col-sm-8">
                                    <span id="CpfUsuario" name="us_cpf"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="NascimentoUsuario" class="col-sm-4 col-form-label"><b>Data de Nascimento:</b></label>
                                <div class="col-sm-8">
                                    <span id="NascimentoUsuario" name="us_data_nasc"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="EmailUsuario" class="col-sm-4 col-form-label"><b>Email:</b></label>
                                <div class="col-sm-8">
                                    <span id="EmailUsuario" name="email"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="SenhaUsuario" class="col-sm-4 col-form-label"><b>Senha:</b></label>
                                <div class="col-sm-8">
                                    <span id="SenhaUsuario" name="password"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="AdmUsuario" class="col-sm-4 col-form-label"><b>Administrador:</b></label>
                                <div class="col-sm-8">
                                    <span id="AdmUsuario" name="us_adm"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="CriacaoUsuario" class="col-sm-4 col-form-label"><b>Data de Criação:</b></label>
                                <div class="col-sm-8">
                                    <span id="CriacaoUsuario" name="us_data_criacao"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                <button type="button" class="btn btn-lg btn-dark">Editar</button>
                <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
