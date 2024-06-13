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
                        <h2 class="h2">Dados do Usuário</h2>
                        <div class="form-group row my-2">
                            <label for="userCodigo" class="col-sm-4 col-form-label"><b>Código:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userCodigo" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userNome" class="col-sm-4 col-form-label"><b>Nome:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userNome" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userCpf" class="col-sm-4 col-form-label"><b>CPF:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userCpf" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userDataNascimento" class="col-sm-4 col-form-label"><b>Data de Nascimento:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userDataNascimento" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userEmail" class="col-sm-4 col-form-label"><b>Email:</b></label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" id="userEmail" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userSenha" class="col-sm-4 col-form-label"><b>Senha:</b></label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" id="userSenha" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userDataCriacao" class="col-sm-4 col-form-label"><b>Data de Criação:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userDataCriacao" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userTelefone" class="col-sm-4 col-form-label"><b>Telefone:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userTelefone" value="" readonly>
                            </div>
                        </div>
                        <h3 class="h3">Endereço</h3>
                        <div class="form-group row my-2">
                            <label for="userCep" class="col-sm-4 col-form-label"><b>CEP:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userCep" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userEstado" class="col-sm-4 col-form-label"><b>Estado:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userEstado" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userCidade" class="col-sm-4 col-form-label"><b>Cidade:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userCidade" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userRua" class="col-sm-4 col-form-label"><b>Rua:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userRua" value="" readonly>
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="userNumero" class="col-sm-4 col-form-label"><b>Número:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="userNumero" value="" readonly>
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
