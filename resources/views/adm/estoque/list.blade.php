@section('titulo', 'Estoque')


@extends('layouts.frame')
@section('content')
@include('layouts.components.produto-modal')
<div class="text-center">
    <h1 class="h1 mb-4">ESTOQUE</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código do Produto</th>
                    <th scope="col">Nome do Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço</th>
                    <th scope="col">Última Atualização</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estoques as $estoque)
                    <tr>
                        <td>00{{ $estoque->es_id_produto }}</td>
                        <td>{{ $estoque->es_nome_produto }}</td>
                        <td>00{{ $estoque->es_quantidade }}</td>
                        <td>
                            @foreach ($produtos as $produto)
                                @if ($produto->pr_id === $estoque->es_id_produto)
                                    {{ $produto->pr_preco }}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>{{ $estoque->es_data_atualizacao }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>



@endsection