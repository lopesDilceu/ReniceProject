<div class="modal fade" id="produto-modal" tabindex="-1" aria-labelledby="produto-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
      <div class="modal-header border-bottom-0">
        <h1 class="modal-title h2">Produto teste</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-0">
        <div class="row my-2">
          <div class="col-12 col-lg-6 col-sm-12">
            <h2 class="h2">Informações do Produto</h2>
            <div class="form-group row my-2">
              <label for="nomeProduto" class="col-sm-4 col-form-label"><b>Nome do Produto:</b></label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="nomeProduto">
              </div>
            </div>
            <div class="form-group row my-2">
              <label for="descricaoProduto" class="col-sm-4 col-form-label"><b>Descrição do Produto:</b></label>
              <div class="col-sm-8">
                <textarea class="form-control" id="descricaoProduto" rows="3"></textarea>
              </div>
            </div>
            <div class="form-group row my-2">
              <label for="precoProduto" class="col-sm-4 col-form-label"><b>Preço do Produto:</b></label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="precoProduto">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
        <!-- <button type="button" class="btn btn-lg btn-indigo">Adicionar ao carrinho</button> -->
        <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>