@extends('layouts.frame')

@section('titulo', 'Minhas Compras')
@section('content')
@include('layouts.components.venda-usuario-modal')
    <h1 class="h1 mb-4">Minhas Compras</h1>
    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-outline-secondary" onclick="window.history.back();">Voltar</button>
    </div>
    <!-- Tabela de Compras do Usuário -->
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código da Compra</th>
                    <th scope="col">Data da Compra</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total da Compra</th>
                    <th scope="col">Ação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendas as $venda)
                <tr>
                    <td class="align-middle">#0{{ $venda->ve_id }}</td>
                    <td class="align-middle">{{date_format(date_create($venda->ve_data_venda), 'd/m/Y H:i')}}</td>
                    <td class="align-middle">{{ $venda->ve_status }}</td>
                    <td class="align-middle">R${{ $venda->ve_total }}</td>
                    <td class="align-middle">
                        <button class="btn btn-outline-light ver-venda" data-bs-toggle="modal"
                                data-bs-target="#venda-usuario-modal" data-id="{{ $venda->ve_id }}">
                                <img src="{{asset('/images/icons/ver.png')}}" alt="Ver" width="24px" height="24px">
                        </button>
                        
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhuma compra encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>


@push('script')
<script>
    $('.ver-venda').on('click', function() {
        const ve_id = $(this).data('id');

        const vendas = @json($vendas);
        const itensvenda = @json($itensvenda);
        const produtos = @json($produtos);
        const baseUrl = "{{ asset('') }}";

        let venda = vendas.find(v => v.ve_id === ve_id);
        let modal = $('#venda-usuario-modal');

        modal.find('#codigoVenda').val(venda.ve_id);
        modal.find('#dataVenda').val(venda.ve_data_venda);

        let detalhesProdutos = $('#detalhes-produtos');
        detalhesProdutos.empty();

        itensvenda.forEach(item => {
            if (item.iv_id_venda === ve_id) {
                let produto = produtos.find(prod => prod.pr_id === item.iv_id_produto);
                let total = (item.iv_quantidade * item.iv_preco_unitario).toFixed(2).replace('.', ',');

                detalhesProdutos.append(`
                    <tr>
                        <td class="align-middle">#0${item.iv_id_produto}</td>
                        <td class="align-middle"><img src="${baseUrl}${produto.pr_foto}" alt="${produto.pr_nome}" class="rounded-circle" width="50px" height="50px"> ${produto.pr_nome}</td>
                        <td class="align-middle">${item.iv_quantidade}</td>
                        <td class="align-middle">R$${item.iv_preco_unitario}</td>
                        <td class="align-middle">R$${total}</td>
                        <td class="align-middle">
                            <a href="{{ url('/avaliar-produto') }}/${item.iv_id_produto}" class="btn btn-outline-success"><img src="{{asset('/images/icons/like.png')}}" alt="Avaliar" width="24px" height="24px"></a>
                        </td>
                    </tr>
                `);
            }
        });

        modal.modal('show');
    });
</script>
@endpush


@endsection
