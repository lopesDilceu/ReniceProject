@extends('layouts.frame')
@section('titulo', 'Editar Cadastro')

@section('content')
<div class="container-sm container-md container-lg p-5 text-center mt-3 mb-3">
  <div>
    
    <h1 class="h3 mb-2">EDITAR DADOS DE {{strtoupper($usuario->name)}}</h1>
    @if (isset($usuario->us_foto))
      <img src="{{asset($usuario->us_foto)}}" alt="foto de {{$usuario->name}}" class="rounded-circle border" width="180px" height="180px">
    @else
      <img src="{{asset('images/perfil.png')}}" alt="foto de {{$usuario->name}}" class="rounded-circle border" width="180px" height="180px">
    @endif
    <form id="formEdit" action="{{ route('usuarios.edit', [$usuario->us_id]) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      
      <h2 class="h5 text-start">Dados Pessoais</h2>
      <div class="text-start">
        <div class="row g-2">
          <div class="col-md-7 col-sm-12">
            <label for="nome" class="form-label">Nome:</label>
            <input type="text" id="nome" name="name" class="form-control mb-2" value="{{$usuario->name ?? '-----' }}">
            
            <label for="telefones" class="form-label">Telefones:</label>
            @foreach($telefones as $telefone)
              <input type="text" class="form-control mb-2" name="telefones[]" value="{{$telefone->te_numero ?? '-----' }}">
            @endforeach
          </div>
          <div class="col-md col-sm-12">
            <label for="cpf" class="form-label">CPF:</label>
            <input type="text" id="cpf" name="us_cpf" class="form-control mb-2" value="{{$usuario->us_cpf  ?? '-----' }}">
            
            <label for="data-nasc" class="form-label">Data de Nascimento:</label>
            <input type="date" id="data-nasc" name="us_data_nasc" class="form-control mb-2" value="{{$usuario->us_data_nasc ?? '-----' }}">
          </div>
        </div>
        
        <h2 class="h5 text-start mt-2">Endereço</h2>
        <div class="row g-2">
          <div class="col-md col-sm-12">
            <label for="cep" class="form-label">CEP:</label>
            <input type="text" id="cep" name="en_cep" class="form-control mb-2" value="{{ $endereco->en_cep ?? '-----' }}">
          </div>
          <div class="col-md-9 col-sm-12">
            <label for="logradouro" class="form-label">Logradouro:</label>
            <input type="text" id="rua" name="en_logradouro" class="form-control mb-2" value="{{$endereco->en_logradouro ?? '-----' }}">
          </div>
          <div class="col-md col-sm-12">
            <label for="numero" class="form-label">Número:</label>
            <input type="text" id="numero" name="en_numero" class="form-control mb-2" value="{{$endereco->en_numero ?? '-----' }}">
          </div>
        </div>
        
        <div class="row">
          <div class="col-12">
            <label for="bairro" class="form-label">Bairro:</label>
            <input type="text" id="bairro" name="en_bairro" class="form-control mb-2" value="{{$endereco->en_bairro ?? '-----' }}">
          </div>
        </div>
        
        <div class="row g-2">
          <div class="col-md-7 col-sm-12">
            <label for="cidade" class="form-label">Cidade:</label>
            <input type="text" id="cidade" name="en_cidade" class="form-control mb-2" value="{{$endereco->en_cidade ?? '-----' }}">
          </div>
          <div class="col-md col-sm-12">
            <label for="estado" class="form-label">Estado:</label>
            <input type="text" id="estado" name="en_estado" class="form-control mb-2" value="{{$endereco->en_estado ?? '-----' }}">
          </div>
        </div>
        
        <h2 class="h5 text-start mt-2">Dados de Login</h2>
        <label for="email" class="form-label">Email:</label>
        <input type="email" id="email" name="email" class="form-control mb-2" value="{{$usuario->email ?? '-----' }}">
        
        <!-- Campo para upload de foto -->
        <label for="us_foto" class="form-label">Foto:</label>
        <input type="file" id="us_foto" name="us_foto" class="form-control mb-2" value="{{$usuario->us_foto ?? '-----' }}">

        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
          <button type="submit" class="btn btn-outline-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
          <a href="{{ route('minha-conta') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        </div>
      </div>
    </form>
  </div>
</div>

@push('script')
<script>
    $('#cpf').mask('000.000.000-00');
    $('#telefone').mask('(00) 00000-0000');
    $('#cep').mask('00000-000');
    $('#formEdit').submit(function(event) {
        // Remover a máscara para enviar o valor correto
        $('#cpf').unmask();
        $('#telefone').unmask();
        $('#cep').unmask();
    });

    $('#cep').blur(function() {
        let cep = $('#cep').unmask().val();
        $('#cep').mask('00000-000');
        $.get('https://viacep.com.br/ws/'+ cep +'/json/', function(dados) {
            $('#rua').val(dados.logradouro);
            $('#bairro').val(dados.bairro);
            $('#cidade').val(dados.localidade);
            $('#estado').val(dados.uf);
        });
    });

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

</script>


@endpush

@endsection