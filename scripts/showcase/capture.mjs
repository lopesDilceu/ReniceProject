// Captures the screenshots in docs/showcase.md from a store freshly loaded
// with the demo seed (it places and pays one order as the demo customer).
// Usage: node scripts/showcase/capture.mjs [baseUrl] [outDir]
import { chromium } from 'playwright-core';
import { mkdirSync } from 'node:fs';

const [base = 'http://localhost:8000', out = 'docs/images/v2'] =
    process.argv.slice(2);
mkdirSync(out, { recursive: true });

const browser = await chromium.launch({
    // The installed Chrome: no browser download needed.
    channel: 'chrome',
});

const viewport = { width: 1280, height: 800 };

async function context(options = {}) {
    const ctx = await browser.newContext({
        viewport,
        locale: 'pt-BR',
        colorScheme: 'light',
        ...options,
    });
    return [ctx, await ctx.newPage()];
}

async function visit(page, path) {
    await page.goto(base + path, { waitUntil: 'networkidle' });
    await page.waitForTimeout(400);
}

async function shot(page, name, fullPage = false) {
    // Toasts are fixed to the viewport, so a full-page shot drops them mid-page.
    // The demo banner (DEMO_MODE) is left out too: the pictures show the store.
    await page.addStyleTag({
        content: '[data-sonner-toaster],[role=note]{display:none!important}',
    });
    await page.evaluate(() => window.scrollTo(0, 0));
    // Lets the navigation progress bar finish.
    await page.waitForTimeout(600);
    await page.screenshot({
        path: `${out}/${name}.jpg`,
        type: 'jpeg',
        quality: 82,
        fullPage,
    });
    console.log('saved', name);
}

async function login(page, email) {
    await visit(page, '/login');
    await page.fill('input[name=email]', email);
    await page.fill('input[name=password]', 'password');
    await page.click('button[type=submit]');
    await page.waitForURL((url) => !url.pathname.startsWith('/login'));
    await page.waitForLoadState('networkidle');
}

// Visitor.
{
    const [ctx, page] = await context();
    await visit(page, '/');
    await shot(page, '01-home');
    await visit(page, '/sobre');
    await shot(page, '02-about');
    await visit(page, '/produtos');
    await shot(page, '03-product-catalog');
    await visit(page, '/produtos/copo-300ml-gelo-medio');
    await shot(page, '05-product', true);
    await visit(page, '/login');
    await shot(page, '10-login');
    await ctx.close();
}

// Customer: cart, checkout, Pix, orders.
{
    const [ctx, page] = await context();
    await login(page, 'cliente@renice.test');
    for (const slug of [
        'copo-300ml-gelo-medio',
        'copo-anime-500ml-gelo-medio',
    ]) {
        await visit(page, `/produtos/${slug}`);
        await page
            .getByRole('button', { name: 'Adicionar ao carrinho' })
            .click();
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(500);
    }
    await visit(page, '/carrinho');
    await shot(page, '04-cart');
    await visit(page, '/checkout');
    await shot(page, '05-checkout');
    await page.getByRole('button', { name: 'Fazer pedido' }).click();
    await page.waitForURL(/\/pedidos\/\d+/);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(600);
    await shot(page, '06-order-pix', true);
    await page.getByRole('button', { name: 'Simular pagamento' }).click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1200);
    await shot(page, '06-order-paid', true);
    await visit(page, '/conta/pedidos');
    await shot(page, '07-my-orders');
    await visit(page, '/pedidos/1');
    await shot(page, '08-order-details', true);
    await visit(page, '/conta/seguranca');
    if (page.url().includes('confirm-password')) {
        await page.fill('input[name=password]', 'password');
        await page
            .getByRole('button', { name: 'Confirmar', exact: true })
            .click();
        await page.waitForURL(/conta\/seguranca/);
        await page.waitForLoadState('networkidle');
    }
    await shot(page, '11-account-security', true);
    await ctx.close();
}

// Admin.
{
    const [ctx, page] = await context();
    await login(page, 'admin@renice.test');
    await visit(page, '/admin');
    await shot(page, '12-admin-dashboard', true);
    await visit(page, '/admin/produtos');
    await shot(page, '13-admin-products');
    await visit(page, '/admin/produtos/novo');
    await shot(page, '14-admin-new-product', true);
    await visit(page, '/admin/pedidos');
    await shot(page, '15-admin-orders');
    await visit(page, '/admin/pedidos/1');
    await shot(page, '16-admin-order', true);
    await visit(page, '/admin/produtos/2/estoque');
    await shot(page, '17-admin-inventory', true);
    await visit(page, '/admin/avaliacoes');
    await shot(page, '18-admin-reviews');
    await visit(page, '/pulse');
    await page.waitForTimeout(1500);
    await shot(page, '19-pulse');
    await visit(page, '/docs/api');
    await page.waitForTimeout(1500);
    await shot(page, '20-api-docs');
    await ctx.close();
}

// Dark mode and phone.
{
    const [ctx, page] = await context({ colorScheme: 'dark' });
    await visit(page, '/');
    await shot(page, '21-home-dark');
    await visit(page, '/produtos');
    await shot(page, '22-catalog-dark');
    await ctx.close();

    const [phone, mobile] = await context({
        viewport: { width: 390, height: 844 },
        deviceScaleFactor: 2,
        isMobile: true,
        hasTouch: true,
    });
    await visit(mobile, '/produtos');
    await shot(mobile, '23-catalog-phone');
    await visit(mobile, '/produtos/copo-300ml-gelo-medio');
    await shot(mobile, '24-product-phone');
    await phone.close();
}

await browser.close();
