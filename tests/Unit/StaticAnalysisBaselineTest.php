<?php

/*
| The Larastan baseline exists only for v1 code that is waiting to be
| rewritten. Regenerating it can silently absorb errors from new code, so
| this test fails if any baselined file is not on the legacy list below.
| Remove entries from the list as legacy files are deleted.
*/

const LEGACY_FILES = [
    'app/helpers.php',
    'app/Http/Controllers/AvaliacaoController.php',
    'app/Http/Controllers/CompraController.php',
    'app/Http/Controllers/EnderecoController.php',
    'app/Http/Controllers/EstoqueController.php',
    'app/Http/Controllers/ItensVendaController.php',
    'app/Http/Controllers/ProdutoController.php',
    'app/Http/Controllers/TelefoneController.php',
    'app/Http/Controllers/UserController.php',
    'app/Http/Controllers/VendaController.php',
    'app/Models/Avaliacao.php',
    'app/Models/Carrinho.php',
    'app/Models/Compra.php',
    'app/Models/Endereco.php',
    'app/Models/Estoque.php',
    'app/Models/ItensCarrinho.php',
    'app/Models/ItensVenda.php',
    'app/Models/Produto.php',
    'app/Models/Telefone.php',
    'app/Models/Venda.php',
];

it('baselines only legacy v1 files', function () {
    preg_match_all('/^\s+path: (.+)$/m', (string) file_get_contents(__DIR__.'/../../phpstan-baseline.neon'), $matches);

    expect(array_diff(array_unique($matches[1]), LEGACY_FILES))->toBe([]);
});
