@extends('layouts.frame')
@section('titulo', 'Visualizar Cadastro')

@push('style')
<style>
  body {
    font-family: Akkurat-Mono, monospace;
  }
</style>
@endpush

@section('content')
<div class="container-sm container-md container-lg p-5 text-center mt-3 mb-3">
  <div>
    <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
      <img src="{{asset('images/logo/renice-logo-side.png')}}" alt="renice-logo" width="18%" class="mb-4">
    </a>
    <h1 class="h3 mb-2">DADOS DO USUÁRIO</h1>
    <h2 class="h5 text-start">Dados Pessoais</h2>
    <div class="row g-2">
      <div class="col-md-7 col-sm-12">
        <input type="text" class="form-control mb-2" id="nome" placeholder="Nome" value="Nome do usuário">
        <input type="text" class="form-control" id="telefone" placeholder="Telefone" value="Telefone do usuário">
      </div>
      <div class="col-md col-sm-12">
        <input type="text" class="form-control mb-2" id="cpf" placeholder="CPF" value="CPF do usuário">
        <input type="date" class="form-control mb-2" id="data-nasc" placeholder="Data de Nascimento" value="Data de Nascimento do usuário">
      </div>
    </div>
    <h2 class="h5 text-start mt-2">Endereço</h2>
    <div class="row g-2">
      <div class="col-md col-sm-12">
        <input type="text" class="form-control" id="cep" placeholder="CEP" value="CEP do usuário">
      </div>
      <div class="col-md-9 col-sm-12">
        <input type="text" class="form-control" id="rua" placeholder="Rua" value="Rua do usuário">
      </div>
      <div class="col-md col-sm-12">
        <input type="text" class="form-control mb-2" id="numero" placeholder="Número" value="Número do usuário">
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <input type="text" class="form-control mb-2" id="bairro" placeholder="Bairro" value="Bairro do usuário">
      </div>
    </div>
    <div class="row g-2">
      <div class="col-md-7 col-sm-12">
        <input type="text" class="form-control mb-2" id="cidade" placeholder="Cidade" value="Cidade do usuário">
      </div>
      <div class="col-md col-sm-12">
        <input type="text" class="form-control mb-2" id="estado" placeholder="Estado" value="Estado do usuário">
      </div>
    </div>
    <h2 class="h5 text-start mt-2">Dados de Login</h2>
    <input type="email" class="form-control mb-2" id="email" placeholder="Email" value="email@exemplo.com">
    <input type="password" class="form-control mb-4" id="senha" placeholder="Senha" value="senha">
    <div class="d-grid d-md-flex gap-2 justify-content-md-end">
      <a href="{{ route('home')}}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
      <a href="{{ route('usuarios.edit')}}" class="w-80 btn btn-lg btn-dark"><i class="bi bi-arrow-left"></i>Editar</a>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    $('#editar').on('click', function() {
      // Redireciona para a página de edição
      window.location.href = "{{ route('usuarios.edit') }}";
    });
  });
</script>
@endpush