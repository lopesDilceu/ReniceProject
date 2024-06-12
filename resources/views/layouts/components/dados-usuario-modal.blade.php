<div class="modal fade" id="dados-usuario-modal" tabindex="-1" aria-labelledby="dados-usuario-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content d-flex rounded-3 py-2 px-1 px-md-3 shadow">
        <div class="modal-header border-bottom-0">
          <h1 class="modal-title h2">Usuário</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body py-0">
            <div class="row my-2">
                <div class="col-12 col-lg-6 col-sm-12 text-center mb-3 mb-md-3 mb-lg-0">
                        <img src="{{ asset('images/logo/renice-logo-down.png')}}" alt="" class="img-fluid rounded">
                </div>
                <div class="col-12 col-lg-6 col-sm-12">
                    <h2 class="h2">R$ 05,00</h2>
                    <div class="form-group row my-2">
                      <label for="productQuantity" class="mb-2"><b>Quantidade</b></label>
                      <div class="col-3 col-sm-2">
                          <input type="number" class="form-control" id="productQuantity" name="productQuantity" min="1" max="100" step="1" value="1">
                      </div>
                  </div>
                  <p>TESTE</p>
                </div>
            </div>
           
        </div>
        <div class="modal-footer flex-column align-items-stretch w-100 gap-2 pb-3 border-top-0">
          <button type="button" class="btn btn-lg btn-indigo">Editar</button>
          <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
</div>