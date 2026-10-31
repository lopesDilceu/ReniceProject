<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * @return list<string>
 */
function catalogNames(TestResponse $response): array
{
    return array_column($response->inertiaProps('products.data'), 'name');
}

describe('listing', function () {
    it('renders the catalog with ratings and without exact stock', function () {
        $product = Product::factory()->inStock(30)->create(['name' => 'Copo 300ml gelo médio', 'price_cents' => 1800]);
        Review::factory()->for($product)->create(['rating' => 5]);
        Review::factory()->for($product)->create(['rating' => 4]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('products/index')
                ->where('products.meta.total', 1)
                ->where('products.data.0', fn ($data) => $data['name'] === 'Copo 300ml gelo médio'
                    && $data['price_cents'] === 1800
                    && $data['rating'] === 4.5
                    && $data['reviews_count'] === 2
                    && $data['available'] === true
                    && $data['low_stock'] === false
                    && ! array_key_exists('stock', $data->all())));
    });

    it('paginates twelve products per page', function () {
        Product::factory(13)->create();

        $this->get(route('products.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('products.data', 1)
                ->where('products.meta.last_page', 2));
    });

    it('searches name and description, ignoring case', function () {
        Product::factory()->create(['name' => 'Copo Anime 500ml', 'description' => 'Tema anime']);
        Product::factory()->create(['name' => 'Copo Fresh 500ml', 'description' => 'Tema refrescante, laranja']);
        Product::factory()->create(['name' => 'Copo 300ml', 'description' => 'Gelo pequeno']);

        expect(catalogNames($this->get(route('products.index', ['q' => 'ANIME']))))->toBe(['Copo Anime 500ml'])
            ->and(catalogNames($this->get(route('products.index', ['q' => 'laranja']))))->toBe(['Copo Fresh 500ml']);
    });

    it('can show only available products', function () {
        Product::factory()->inStock()->create(['name' => 'Disponível']);
        Product::factory()->create(['name' => 'Esgotado']);

        expect(catalogNames($this->get(route('products.index', ['available' => 1]))))->toBe(['Disponível']);
    });

    it('sorts', function (string $sort, array $expected) {
        $cheap = Product::factory()->inStock()->create(['name' => 'Barato', 'price_cents' => 500]);
        $pricey = Product::factory()->inStock()->create(['name' => 'Caro', 'price_cents' => 3000]);
        Product::factory()->inStock()->create(['name' => 'Sem avaliação', 'price_cents' => 1500]);
        Product::factory()->create(['name' => 'Esgotado', 'price_cents' => 1000]);
        Review::factory()->for($cheap)->create(['rating' => 3]);
        Review::factory()->for($pricey)->create(['rating' => 5]);

        expect(catalogNames($this->get(route('products.index', ['sort' => $sort]))))->toBe($expected);
    })->with([
        'featured: available first, then rating' => ['featured', ['Caro', 'Barato', 'Sem avaliação', 'Esgotado']],
        'cheapest first' => ['price_asc', ['Barato', 'Esgotado', 'Sem avaliação', 'Caro']],
        'most expensive first' => ['price_desc', ['Caro', 'Sem avaliação', 'Esgotado', 'Barato']],
        'best rated, unrated last' => ['rating', ['Caro', 'Barato', 'Sem avaliação', 'Esgotado']],
    ]);

    it('rejects an unknown sort', function () {
        $this->get(route('products.index', ['sort' => 'DROP TABLE']))->assertSessionHasErrors('sort');
    });

    it('runs the same number of queries for 3 or 12 products', function () {
        $queriesFor = function (int $count): int {
            Product::query()->forceDelete();
            Product::factory($count)->inStock()->has(Review::factory()->count(2))->create();

            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get(route('products.index'))->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        expect($queriesFor(3))->toBe($queriesFor(12));
    })->issue(26);
});

describe('product page', function () {
    it('shows the product, its rating distribution and reviews', function () {
        $product = Product::factory()->inStock(3)->create(['slug' => 'copo-anime', 'description' => 'Tema anime.']);
        Review::factory()->for($product)->for(User::factory()->state(['name' => 'Maria Clara Souza']))->create(['rating' => 5]);
        Review::factory()->for($product)->create(['rating' => 5]);
        Review::factory()->for($product)->create(['rating' => 2]);

        $this->get(route('products.show', 'copo-anime'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('products/show')
                ->where('product.description', 'Tema anime.')
                ->where('product.low_stock', true)
                ->where('product.rating', 4)
                ->where('ratingDistribution', [
                    ['stars' => 5, 'count' => 2],
                    ['stars' => 4, 'count' => 0],
                    ['stars' => 3, 'count' => 0],
                    ['stars' => 2, 'count' => 1],
                    ['stars' => 1, 'count' => 0],
                ])
                ->has('reviews', 3)
                ->where('reviews', fn ($reviews) => collect($reviews)->contains('author', 'Maria S.')));
    });

    it('hides products removed from the catalog', function () {
        $product = Product::factory()->create(['slug' => 'removido']);
        $product->delete();

        $this->get(route('products.show', 'removido'))->assertNotFound();
    });

    it('returns 404 for an unknown product', function () {
        $this->get(route('products.show', 'nao-existe'))->assertNotFound();
    });
});

describe('v1 URLs', function () {
    it('redirects the old search to the catalog search', function () {
        $this->get('/produtos/pesquisa?query=gelo')->assertStatus(301)->assertRedirect(route('products.index', ['q' => 'gelo']));
    });

    it('redirects the old reviews page to the catalog', function () {
        $this->get('/avaliacoes')->assertStatus(301)->assertRedirect('/produtos');
    });
});
