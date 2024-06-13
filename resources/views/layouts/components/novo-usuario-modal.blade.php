<div class="modal fade" id="novo-usuario-modal" tabindex="-1" aria-labelledby="novo-usuario-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="novo-usuario-modal-label">Novo Usuário</h1>
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
                            <label for="newUserCodigo" class="col-sm-4 col-form-label"><b>Código:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserCodigo">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserNome" class="col-sm-4 col-form-label"><b>Nome:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserNome">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserCpf" class="col-sm-4 col-form-label"><b>CPF:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserCpf">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserDataNascimento" class="col-sm-4 col-form-label"><b>Data de Nascimento:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserDataNascimento">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserEmail" class="col-sm-4 col-form-label"><b>Email:</b></label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" id="newUserEmail">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserSenha" class="col-sm-4 col-form-label"><b>Senha:</b></label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" id="newUserSenha">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserDataCriacao" class="col-sm-4 col-form-label"><b>Data de Criação:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserDataCriacao">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserTelefone" class="col-sm-4 col-form-label"><b>Telefone:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserTelefone">
                            </div>
                        </div>
                        <h3 class="h3">Endereço</h3>
                        <div class="form-group row my-2">
                            <label for="newUserCep" class="col-sm-4 col-form-label"><b>CEP:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserCep">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserEstado" class="col-sm-4 col-form-label"><b>Estado:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserEstado">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserCidade" class="col-sm-4 col-form-label"><b>Cidade:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserCidade">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserRua" class="col-sm-4 col-form-label"><b>Rua:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserRua">
                            </div>
                        </div>
                        <div class="form-group row my-2">
                            <label for="newUserNumero" class="col-sm-4 col-form-label"><b>Número:</b></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="newUserNumero">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                <button type="button" class="btn btn-lg btn-dark" id="saveNewUser">Salvar</button>
                <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        document.getElementById('saveNewUser').addEventListener('click', function() {
            const newUser = {
                codigo: document.getElementById('newUserCodigo').value,
                nome: document.getElementById('newUserNome').value,
                cpf: document.getElementById('newUserCpf').value,
                dataNascimento: document.getElementById('newUserDataNascimento').value,
                email: document.getElementById('newUserEmail').value,
                senha: document.getElementById('newUserSenha').value,
                dataCriacao: document.getElementById('newUserDataCriacao').value,
                telefone: document.getElementById('newUserTelefone').value,
                cep: document.getElementById('newUserCep').value,
                estado: document.getElementById('newUserEstado').value,
                cidade: document.getElementById('newUserCidade').value,
                rua: document.getElementById('newUserRua').value,
                numero: document.getElementById('newUserNumero').value
            };

            console.log('New User Data:', newUser);
            // Aqui você pode adicionar o código para enviar os dados para o servidor

            // Fechar o modal após salvar
            const novoUsuarioModal = new bootstrap.Modal(document.getElementById('novo-usuario-modal'));
            novoUsuarioModal.hide();
        });
    });
</script>
