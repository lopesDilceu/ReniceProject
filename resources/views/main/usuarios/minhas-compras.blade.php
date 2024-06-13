@extends('layouts.frame')

@section('titulo', 'Minhas Compras')
@section('content')
@include('layouts.components.detalhes-venda-modal')
    <h1 class="h1 mb-4">Minhas Compras</h1>
    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-outline-secondary" onclick="window.history.back();">Voltar</button>
</div>
    <!-- Tabela de Compras do Usuário -->
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código da Compra</th>
                    <th scope="col">Data da Compra</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total da Compra</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linha da Compra 1 -->
                <tr>
                    <td>#123456</td>
                    <td>01/07/2024</td>
                    <td>Pendente</td>
                    <td>R$ 35,00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#detalhes-venda-modal">Ver</button>
                    </td>
                </tr>
                <!-- Linha da Compra 2 -->
                <tr>
                    <td>#123457</td>
                    <td>05/07/2024</td>
                    <td>Concluída</td>
                    <td>R$ 42,00</td>
                    <td>
                        <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#detalhes-venda-modal">Ver</button>
                    </td>
                </tr>
                <!-- Adicione mais linhas conforme necessário para outras compras -->
            </tbody>
        </table>
    </div>
@endsection
