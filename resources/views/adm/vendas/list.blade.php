@section('titulo', 'Vendas')

@extends('layouts.frame')
@section('content')
@include('layouts.components.produto-modal')
@include('layouts.components.detalhes-venda-modal')
<div class="text-center">
    <h1 class="h1 mb-4">VENDAS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-outline-dark" onclick="window.location.href='nova-compra.html';">Nova Venda</button>
    </div>
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Cód. Usuario</th>
                    <th scope="col">Nome Usuario</th>
                    <th scope="col">Status</th>
                    <th scope="col">Data</th>
                    <th scope="col">Total</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendas as $venda)
                    <tr>
                        <td>#00{{ $venda->ve_id }}</td>
                        <td>#0{{ $venda->ve_id_usuario }}</td>
                        <td>
                            @foreach ($users as $user)
                                @if ($user->us_id === $venda->ve_id_usuario)
                                    {{ $user->name }}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>{{ $venda->ve_status }}</td>
                        <td>{{ $venda->ve_data_venda }}</td>
                        <td>R${{ $venda->ve_total }}</td>
                        <td>
                            <button class="btn btn-outline-light ver-venda" data-bs-toggle="modal"
                            data-bs-target="#detalhes-venda-modal" data-id="{{ $venda->ve_id }}">
                                <img src="{{ asset('/images/icons/ver.png') }}" alt="Ver" width="24px" height="24px">
                            </button>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('script')
<script>
    $('.ver-venda').on('click', function() {
        const ve_id = $(this).data('id');

        const vendas = @json($vendas);
        const itensvenda = @json($itensvenda);
        const produtos = @json($produtos);

        let venda = vendas.find(venda => venda.ve_id === ve_id);
        let modal = $('#detalhes-venda-modal');

        modal.find('#codigoVenda').val(venda.ve_id);
        modal.find('#dataVenda').val(venda.ve_data_venda);

        let detalhesProdutos = $('#detalhes-produtos');
        detalhesProdutos.empty();

        itensvenda.forEach(item => {
            if (item.iv_id_venda === ve_id) {
                let produto = produtos.find(produto => produto.pr_id === item.iv_id_produto);
                let total = (item.iv_quantidade * item.iv_preco_unitario).toFixed(2).replace('.', ',');

                detalhesProdutos.append(`
                    <tr>
                        <td>#00${item.iv_id_produto}</td>
                        <td>${produto.pr_nome}</td>
                        <td>${item.iv_quantidade}</td>
                        <td>R$${item.iv_preco_unitario}</td>
                        <td>R$${total}</td>
                    </tr>
                `);
            }
        });

        modal.modal('show');
    });
</script>
@endpush
@endsection
