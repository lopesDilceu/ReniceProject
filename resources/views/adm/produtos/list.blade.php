@section('titulo', 'Administração')

@extends('layouts.frame')
@section('content')
@include('layouts.components.novo-produto-modal')
@include('layouts.components.produto-modal')
@include('layouts.components.edit-produto-modal')
<div class="text-center">
    <h1 class="h1 mb-4">PRODUTOS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-outline-secondary" onclick="window.history.back();">Voltar</button>
        <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#novo-produto-modal">Novo Produto</button>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código do Produto</th>
                    <th scope="col">Nome do Produto</th>
                    <th scope="col">Preço</th>
                    <th scope="col">Data de Cadastro</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Exemplo de Produto 1 -->
                <tr>
                    <td>001</td>
                    <td>Produto 1</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#produto-modal">Ver</button>
                        <button class="btn btn-dark" data-bs-toggle="modal"
                            data-bs-target="#edit-produto-modal">Editar</button>
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
                    <td>Produto 1</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#produto-modal">Ver</button>
                        <button class="btn btn-dark" data-bs-toggle="modal"
                            data-bs-target="#edit-produto-modal">Editar</button>
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
                    <td>Produto 1</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#produto-modal">Ver</button>
                        <button class="btn btn-dark" data-bs-toggle="modal"
                            data-bs-target="#edit-produto-modal">Editar</button>
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
                    <td>Produto 1</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#produto-modal">Ver</button>
                        <button class="btn btn-dark" data-bs-toggle="modal"
                            data-bs-target="#edit-produto-modal">Editar</button>
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