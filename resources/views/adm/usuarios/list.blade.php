@section('titulo', 'Administração')


@extends('layouts.frame')
@section('content')
@include('layouts.components.dados-usuario-modal')
@include('layouts.components.novo-usuario-modal')
<div class="text-center">
    <h1 class="h1 mb-4">USUÁRIOS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-outline-secondary" onclick="window.history.back();">Voltar</button>
        <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#novo-usuario-modal">Novo Usuário</button>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Nome</th>
                    <th scope="col">CPF</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Exemplo de Produto 1 -->
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <!-- Exemplo de Produto 2 -->
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>001</td>
                    <td>Fulano</td>
                    <td>040.100.100-40</td>
                    <td>fulano@dominio.com</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>


                <!-- Pode adicionar mais linhas conforme necessário -->
            </tbody>
        </table>
    </div>
</div>
</div>



@endsection