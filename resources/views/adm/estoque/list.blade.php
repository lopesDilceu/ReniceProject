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
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço</th>
                    <th scope="col">Última Atualização</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estoques as $estoque)
                    <tr>
                        <td class="align-middle">#0{{ $estoque->es_id_produto }}</td>
                        <td class="align-middle">
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $estoque->es_id_produto)
                                <img src="{{ asset($produto->pr_foto) }}" alt="" class="rounded-circle" width="60" height="60"> 
                                @break
                            @endif
                        @endforeach
                            {{ $estoque->es_nome_produto }}
                        </td>
                        <td class="align-middle">{{ $estoque->es_quantidade }}</td>
                        <td class="align-middle">
                            @foreach ($produtos as $produto)
                                @if ($produto->pr_id === $estoque->es_id_produto)
                                    R${{number_format($produto->pr_preco, 2, ',', '.')}}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td class="align-middle">{{ date_format(date_create($estoque->es_data_atualizacao), 'd/m/Y H:i') }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>



@endsection