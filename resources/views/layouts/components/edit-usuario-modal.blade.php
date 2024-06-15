<div class="modal fade" id="edit-usuario-modal" tabindex="-1" aria-labelledby="edit-usuario-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="edit-usuario-modal-label">Editar Usuário</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-0">
                    <div class="row my-2">
                        <div class="col-12 col-lg-6 col-sm-12 text-center mb-3 mb-md-3 mb-lg-0">
                            <img src="{{ asset('images/logo/renice-logo-down.png')}}" alt="Logo" class="img-fluid rounded" width="50%">
                        </div>
                        <div class="col-12 col-lg-6 col-sm-12">
                            <h2 class="h2">Informações do Usuário</h2>
                            <div class="form-group row my-2">
                                <label for="CodigoUsuario" name="CodigoUsuario" class="col-sm-4 col-form-label"><b>Código:</b></label>
                                <div class="col-sm-8">
                                    <span id="CodigoUsuario" name="us_id"></span>
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editNomeUsuario" class="col-sm-4 col-form-label"><b>Nome:</b></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="editNomeUsuario" name="name">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editCpfUsuario" class="col-sm-4 col-form-label"><b>CPF:</b></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="editCpfUsuario" name="us_cpf">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editNascUsuario" class="col-sm-4 col-form-label"><b>Data de Nascimento:</b></label>
                                <div class="col-sm-8">
                                    <input type="date" class="form-control" id="editNascUsuario" name="us_data_nasc">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editEmailUsuario" class="col-sm-4 col-form-label"><b>Email:</b></label>
                                <div class="col-sm-8">
                                    <input type="email" class="form-control" id="editEmailUsuario" name="email">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editSenhaUsuario" class="col-sm-4 col-form-label"><b>Senha:</b></label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" id="editSenhaUsuario" name="password">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editAdmUsuario" class="col-sm-4 col-form-label"><b>Administrador:</b></label>
                                <div class="col-sm-8">
                                    <input type="number" class="form-control" id="editAdmUsuario" name="us_adm">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="editCriacaoUsuario" class="col-sm-4 col-form-label"><b>Data de Criação:</b></label>
                                <div class="col-sm-8">
                                    <span id="CriacaoUsuario" name="us_data_criacao"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                    <button type="submit" class="btn btn-lg btn-dark" id="saveEditUser">Salvar</button>
                    <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </form>
        </div>
    </div>
</div>
