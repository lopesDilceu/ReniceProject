@section('titulo', 'Home')

@extends('layouts.frame')
@section('content')
@include('layouts.components.nova-compra-modal')
<div class="text-center">
    <h1 class="h1 mb-4">COMPRAS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-outline-secondary" onclick="window.history.back();">Voltar</button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#nova-compra-modal">Nova Compra</button>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Cód. Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Fornecedor</th>
                    <th scope="col">Preço uni.</th>
                    <th scope="col">Data</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Exemplo de Produto 1 -->
                <tr>
                    <td>001</td>
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>Tambasa</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#avaliacao-modal">Ver</button>
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
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>Tambasa</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#avaliacao-modal">Ver</button>
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
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>Tambasa</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#avaliacao-modal">Ver</button>
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
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>Tambasa</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#avaliacao-modal">Ver</button>
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
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>Tambasa</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#avaliacao-modal">Ver</button>
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
                
                <!-- Pode adicionar mais linhas conforme necessário -->
            </tbody>
        </table>
    </div>
</div>

</div>



@endsection