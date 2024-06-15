@extends('layouts.frame')
@section('titulo', 'Visualizar Cadastro')

@section('content')
<div class="container-sm container-md container-lg p-5 text-center mt-3 mb-3">
  <div>
    
    <h1 class="h3 mb-2">DADOS DE {{strtoupper($usuario->name)}}</h1>
    <h2 class="h5 text-start">Dados Pessoais</h2>
    <div class="text-start">
      <div class="row g-2">
        <div class="col-md-7 col-sm-12">
          <label for="nome" class="form-label">Nome:</label>
          <span id="nome" class="form-control mb-2">{{$usuario->name ?? '-----' }}</span>
          <label for="telefones" class="form-label">Telefones:</label>
          @foreach($telefones as $telefone)
        <span class="form-control mb-2">{{$telefone->te_numero ?? '-----' }}</span>
      @endforeach
        </div>
        <div class="col-md col-sm-12">
          <label for="cpf" class="form-label">CPF:</label>
          <span id="cpf" class="form-control mb-2">{{$usuario->us_cpf ?? '-----' }}</span>
          <label for="data-nasc" class="form-label">Data de Nascimento:</label>
          <span id="data-nasc" class="form-control mb-2">{{$usuario->us_data_nasc ?? '-----' }}</span>
        </div>
      </div>
      <h2 class="h5 text-start mt-2">Endereço</h2>
      <div class="row g-2">
        <div class="col-md col-sm-12">
          <label for="cep" class="form-label">CEP:</label>
          <span id="cep" class="form-control mb-2">{{ $endereco->en_cep ?? '-----' }}</span>
        </div>
        <div class="col-md-9 col-sm-12">
          <label for="logradouro" class="form-label">Logradouro:</label>
          <span id="logradouro" class="form-control mb-2">{{$endereco->en_logradouro ?? '-----' }}</span>
        </div>
        <div class="col-md col-sm-12">
          <label for="numero" class="form-label">Número:</label>
          <span id="numero" class="form-control mb-2">{{$endereco->en_numero ?? '-----' }}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <label for="bairro" class="form-label">Bairro:</label>
          <span id="bairro" class="form-control mb-2">{{$endereco->en_bairro ?? '-----' }}</span>
        </div>
      </div>
      <div class="row g-2">
        <div class="col-md-7 col-sm-12">
          <label for="cidade" class="form-label">Cidade:</label>
          <span id="cidade" class="form-control mb-2">{{$endereco->en_cidade ?? '-----' }}</span>
        </div>
        <div class="col-md col-sm-12">
          <label for="estado" class="form-label">Estado:</label>
          <span id="estado" class="form-control mb-2">{{$endereco->en_estado ?? '-----' }}</span>
        </div>
      </div>
      <h2 class="h5 text-start mt-2">Dados de Login</h2>
      <label for="email" class="form-label">Email:</label>
      <span id="email" class="form-control mb-2">{{$usuario->email ?? '-----' }}</span>
      <div class="d-grid d-md-flex gap-2 justify-content-md-end">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
      </div>
    </div>
  </div>
</div>

@endsection

@push('script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
    const senhaInput = document.getElementById('senha');
    const btnTogglePassword = document.getElementById('btnTogglePassword');

    btnTogglePassword.addEventListener('click', function () {
      if (senhaInput.type === 'password') {
      senhaInput.type = 'text';
      btnTogglePassword.textContent = 'Esconder Senha';
      } else {
      senhaInput.type = 'password';
      btnTogglePassword.textContent = 'Mostrar Senha';
      }
    });
    });
  </script>
@endpush