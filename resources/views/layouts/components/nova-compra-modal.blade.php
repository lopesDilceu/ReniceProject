<div class="modal fade" id="nova-compra-modal" tabindex="-1" aria-labelledby="nova-compra-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
            <div class="modal-header border-bottom-0">
                <h1 class="modal-title h2" id="nova-compra-modal-label">Nova Compra</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCompra" action="{{ route('adm.compras.store')}}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="row my-2">
                        <div class="col-12 col-lg-6 col-sm-12 text-center mb-3 mb-md-3 mb-lg-0">
                            <!-- Aqui pode adicionar uma imagem ou informações sobre o produto, se desejar -->
                            <img src="{{ asset('images/logo/renice-logo-down.png') }}" alt="Produto" class="img-fluid rounded" width="30%">
                        </div>
                        <div class="col-12 col-lg-6 col-sm-12">
                            <h2 class="h2">Informações da Compra</h2>
                            <div class="form-group row my-2">
                                <label for="produtoCodigo" class="col-sm-4 col-form-label"><b>Produto:</b></label>
                                <div class="col-sm-8">
                                    <select id="produtoCodigo" class="form-control" name="co_id_produto">
                                        @forelse ($produtos as $produto)
                                            <option value="{{ $produto->pr_id}}">{{$produto->pr_nome}}</option>
                                        @empty
                                            <option value="0">Nenhum produto encontrado</option>
                                        @endforelse
                                    </select>
                                </div>
                                <!-- <div class="col-sm-8">
                                    <input type="text" class="form-control" id="produtoCodigo" name="co_id_produto">
                                </div> -->
                            </div>
                            <div class="form-group row my-2">
                                <label for="quantidade" class="col-sm-4 col-form-label"><b>Quantidade:</b></label>
                                <div class="col-sm-8">
                                    <input type="number" class="form-control" id="quantidade" name="co_quantidade">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="precoUnitario" class="col-sm-4 col-form-label"><b>Preço Unitário:</b></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="precoUnitario" name="co_preco_unitario">
                                </div>
                            </div>
                            <div class="form-group row my-2">
                                <label for="fornecedor" class="col-sm-4 col-form-label"><b>Fornecedor: </b></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="co_fornecedor" name="co_fornecedor">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
                    <button type="submit" class="btn btn-lg btn-dark">Salvar</button>
                    <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function() {
        $('#precoUnitario').maskMoney({
            prefix: 'R$ ',
            thousands: '.',
            decimal: ',',
            affixesStay: false,
            allowZero: true,
            precision: 2,
            reverse: true
        });

        $('#formCompra').submit(function() {
            var unmaskedValue = $('#precoUnitario').maskMoney('unmasked')[0];
            $('#precoUnitario').val(unmaskedValue);
            return true; 
        });
    });
</script>

@endpush


<!--Exemplo de rota dinamica para select-->


                {{-- const radioSemVinculo = document.getElementById('opcaoSemVinculo');
                const radioComVinculo = document.getElementById('opcaoComVinculo');
                const selectProjeto = document.getElementById('selectProjeto');

                // Hide the select element by default since the first radio button is pre-selected
                selectProjeto.style.display = 'none';

                // Add event listeners to the radio buttons
                radioSemVinculo.addEventListener('click', () => {
                    selectProjeto.style.display = 'none';
                });

                radioComVinculo.addEventListener('click', () => {
                    selectProjeto.style.display = 'block';
                });

                $(document).ready(function() {
                    $('#selectProjeto').on('change', function() {
                        var projetoID = $(this).val();
                        $('#form_acao').attr('action', '{{ route('acao.save', ':projetoID') }}'
                            .replace(':projetoID', projetoID));
                    });
                });
                <label class="radio-inline">
                    <input required type="radio" name="vinculoProjeto" id="opcaoSemVinculo"> Ação sem
                    vínculo a projetos
                </label>
                <label class="radio-inline">
                    <input required type="radio" name="vinculoProjeto" id="opcaoComVinculo"> Vincular a
                    um projeto
                </label>

                {{ Form::open(['id' => 'form_acao', 'method' => 'post']) }}
                <select id="selectProjeto" class="form-control" style="margin-top: 5px">
                    <option value="">Sem vínculo</option>
                    @forelse ($projetos as $projeto)
                        <option value="{{ $projeto->id }}">{{ str_limit($projeto->titulo, 80) }}
                        </option>
                    @empty
                        <option value="0">Nenhum projeto encontrado</option>
                    @endforelse
                </select> --}}