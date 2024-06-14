@section('titulo', 'Administração')


@extends('layouts.frame')
@section('content')
@include('layouts.components.produto-modal')
<div class="text-center">
    <h1 class="h1 mb-4">VENDAS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-outline-dark" onclick="window.location.href='nova-compra.html';">Nova Venda</button>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Cód. Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço uni.</th>
                    <th scope="col">Data</th>
                    <th scope="col">Fornecedor</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Exemplo de Produto 1 -->
                <tr>
                    <td>001</td>
                    <td>001</td>
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                </tr>
                <!-- Exemplo de Produto 2 -->
                <tr>
                    <td>002</td>
                    <td>Produto 2</td>
                    <td>15</td>
                    <td>R$ 15,00</td>
                    <td>2024-06-12 11:45:00</td>
                </tr>
                <!-- Exemplo de Produto 4 -->
                <tr>
                    <td>004</td>
                    <td>Produto 4</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 5 -->
                <tr>
                    <td>005</td>
                    <td>Produto 5</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 6 -->
                <tr>
                    <td>006</td>
                    <td>Produto 6</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 7 -->
                <tr>
                    <td>007</td>
                    <td>Produto 7</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 8 -->
                <tr>
                    <td>008</td>
                    <td>Produto 8</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 1 -->
                <tr>
                    <td>001</td>
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                </tr>
                <!-- Exemplo de Produto 2 -->
                <tr>
                    <td>002</td>
                    <td>Produto 2</td>
                    <td>15</td>
                    <td>R$ 15,00</td>
                    <td>2024-06-12 11:45:00</td>
                </tr>
                <!-- Exemplo de Produto 4 -->
                <tr>
                    <td>004</td>
                    <td>Produto 4</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 5 -->
                <tr>
                    <td>005</td>
                    <td>Produto 5</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 6 -->
                <tr>
                    <td>006</td>
                    <td>Produto 6</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 7 -->
                <tr>
                    <td>007</td>
                    <td>Produto 7</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 8 -->
                <tr>
                    <td>008</td>
                    <td>Produto 8</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 1 -->
                <tr>
                    <td>001</td>
                    <td>Produto 1</td>
                    <td>10</td>
                    <td>R$ 10,00</td>
                    <td>2024-06-12 10:30:00</td>
                </tr>
                <!-- Exemplo de Produto 2 -->
                <tr>
                    <td>002</td>
                    <td>Produto 2</td>
                    <td>15</td>
                    <td>R$ 15,00</td>
                    <td>2024-06-12 11:45:00</td>
                </tr>
                <!-- Exemplo de Produto 4 -->
                <tr>
                    <td>004</td>
                    <td>Produto 4</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 5 -->
                <tr>
                    <td>005</td>
                    <td>Produto 5</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 6 -->
                <tr>
                    <td>006</td>
                    <td>Produto 6</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 7 -->
                <tr>
                    <td>007</td>
                    <td>Produto 7</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Exemplo de Produto 8 -->
                <tr>
                    <td>008</td>
                    <td>Produto 8</td>
                    <td>20</td>
                    <td>R$ 20,00</td>
                    <td>2024-06-12 13:20:00</td>
                </tr>
                <!-- Pode adicionar mais linhas conforme necessário -->
            </tbody>
        </table>
    </div>
</div>
</div>



@endsection