@extends('layouts.frame')

@section('titulo', 'Carrinho')

@section('content')
    <h1 class="h1 mb-4">CARRINHO</h1>
    <!-- Tabela de Carrinho -->
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
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
                @forelse($itenscarrinho as $itemcarrinho)
                <tr>
                    <td class="align-middle">
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                <img src="{{ asset($produto->pr_foto) }}" alt="" class="rounded-circle" width="60" height="60"> 
                                @break
                            @endif
                        @endforeach
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                {{ $produto->pr_nome }}
                                @break
                            @endif
                        @endforeach
                    </td>
                    <td class="align-middle">
                        <div class="quantity-controls">
                            <button class="btn btn-outline-secondary btn-sm decrement" data-id="{{ $itemcarrinho->ic_id }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/>
                                </svg>
                            </button>
                            <input type="number" name="quantidade" value="{{ $itemcarrinho->ic_quantidade }}" min="1" class="form-control quantidade" data-id="{{ $itemcarrinho->ic_id }}" style="width: 60px; display: inline;">
                            <button class="btn btn-outline-secondary btn-sm increment" data-id="{{ $itemcarrinho->ic_id }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                    <td class="align-middle">R$
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                {{ $produto->pr_preco }}
                                @break
                            @endif
                        @endforeach
                    </td>
                    <td class="align-middle">
                        R$ <span class="total-item">{{ number_format($itemcarrinho->ic_quantidade * $produto->pr_preco, 2, ',', '.') }}</span>
                    </td>
                    <td class="align-middle">
                        <form action="{{ route('usuario.carrinho.destroy', $itemcarrinho->ic_id) }}" method="post" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-light mb-1 mb-md-0" onclick="return confirm('Tem certeza que deseja excluir?')"><img src="{{ asset('/images/icons/deletar.png') }}" alt="Excluir" width="24px" height="24px"></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">Não há itens no carrinho.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="1">Total</th>
                    <td>
                        @php
                            $totalQuantidade = 0;
                            $totalValor = 0;
                        @endphp

                        @foreach($itenscarrinho as $itemcarrinho)
                            @foreach ($produtos as $produto)
                                @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                    @php
                                        $totalQuantidade += $itemcarrinho->ic_quantidade;
                                        $totalValor += $itemcarrinho->ic_quantidade * $produto->pr_preco;
                                    @endphp
                                @endif
                            @endforeach
                        @endforeach

                        <span id="total-quantidade">{{ $totalQuantidade }}</span>
                    </td>
                    <th colspan="1">-</th>
                    <td>
                        R$ <span id="total-valor">{{ number_format($totalValor, 2, ',', '.') }}</span>
                    </td>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Botão de Pagamento -->
    <div class="text-end mt-3">
        <a href="{{ route('produtos') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        @if ($itenscarrinho->isEmpty())
            <a href="#" class="btn btn-lg btn-outline-dark disabled" aria-disabled="true">Pagamento</a>
        @else
            <a href="{{ route('usuario.pagamento') }}" class="btn btn-lg btn-outline-dark">Pagamento</a>
        @endif
    </div>

@push('script')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function updateQuantity(id, quantidade) {
                var token = '{{ csrf_token() }}';
                
                $.ajax({
                    url: '{{ url("/carrinho") }}/' + id,
                    type: 'PATCH',
                    data: {
                        _token: token,
                        quantidade: quantidade
                    },
                    success: function(response) {
                        // Atualizar total do item
                        var precoUnitario = parseFloat(response.preco_unitario);
                        var totalItem = (quantidade * precoUnitario).toFixed(2).replace('.', ',');
                        $('input[data-id="' + id + '"]').closest('tr').find('.total-item').text(totalItem);

                        // Atualizar total geral
                        $('#total-quantidade').text(response.total_quantidade);
                        $('#total-valor').text(response.total_valor.replace('.', ','));
                    }
                });
            }

            $('.increment').on('click', function() {
                var input = $(this).siblings('input');
                var quantidade = parseInt(input.val()) + 1;
                input.val(quantidade);
                updateQuantity(input.data('id'), quantidade);
            });

            $('.decrement').on('click', function() {
                var input = $(this).siblings('input');
                var quantidade = parseInt(input.val()) - 1;
                if (quantidade >= 1) {
                    input.val(quantidade);
                    updateQuantity(input.data('id'), quantidade);
                }
            });

            $('.quantidade').on('change', function() {
                var quantidade = $(this).val();
                updateQuantity($(this).data('id'), quantidade);
            });
        });
    </script>
@endpush
@endsection
