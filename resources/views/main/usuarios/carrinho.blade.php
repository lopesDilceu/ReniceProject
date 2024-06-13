@extends('layouts.frame')

@section('titulo', 'Carrinho')

@section('content')
    <h1 class="h1 mb-4">Carrinho</h1>
    <!-- Tabela de Carrinho -->
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço Unitário</th>
                    <th scope="col">Total</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linha do Carrinho - Produto 1 -->
                <tr>
                    <td>Produto 1</td>
                    <td>2</td>
                    <td>R$ 10,00</td>
                    <td>R$ 20,00</td>
                    <td>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <!-- Linha do Carrinho - Produto 2 -->
                <tr>
                    <td>Produto 2</td>
                    <td>1</td>
                    <td>R$ 15,00</td>
                    <td>R$ 15,00</td>
                    <td>
                        <form action="" method="post" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger mb-1 mb-md-0"
                                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <!-- Adicione mais linhas conforme necessário para outros produtos -->
            </tbody>
            <tfoot>
                <tr>
                    <th col >Total</th>
                    <th col >3</th>
                    <th col >-</th>
                    <td>R$ 35,00</td>
                    <th col ></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Botão de Pagamento -->
    <div class="text-end mt-3">
        <a href="{{ route('produtos')}}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        <a href="{{ route('usuarios.pagamento') }}" class="btn btn-lg btn-outline-dark">Pagamento</a>
    </div>
@endsection
