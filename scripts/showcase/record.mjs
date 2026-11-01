// Records the purchase flow (catalog → cart → Pix → paid) as the GIF in the
// README. Needs ffmpeg on the PATH, and a store loaded with the demo seed.
// Usage: node scripts/showcase/record.mjs [baseUrl] [gifPath]
import { chromium } from 'playwright-core';
import { execFileSync } from 'node:child_process';
import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const [
    base = 'http://localhost:8000',
    gif = 'docs/images/v2/purchase-flow.gif',
] = process.argv.slice(2);
const out = mkdtempSync(join(tmpdir(), 'renice-frames-'));

const browser = await chromium.launch({
    // The installed Chrome: no browser download needed.
    channel: 'chrome',
});
const ctx = await browser.newContext({
    viewport: { width: 1280, height: 800 },
    locale: 'pt-BR',
});

// A visible cursor: screencasts do not draw the real one.
await ctx.addInitScript(() => {
    addEventListener('DOMContentLoaded', () => {
        const dot = document.createElement('div');
        dot.style.cssText =
            'position:fixed;z-index:99999;width:18px;height:18px;margin:-9px 0 0 -9px;border-radius:50%;background:rgba(15,118,110,.45);border:2px solid #0f766e;pointer-events:none;left:-50px;top:-50px;transition:transform .1s';
        document.body.append(dot);
        addEventListener(
            'mousemove',
            (e) => {
                dot.style.left = e.clientX + 'px';
                dot.style.top = e.clientY + 'px';
            },
            true,
        );
        addEventListener(
            'mousedown',
            () => (dot.style.transform = 'scale(.7)'),
            true,
        );
        addEventListener('mouseup', () => (dot.style.transform = ''), true);
    });
});

const page = await ctx.newPage();
let mouse = { x: 640, y: 400 };

async function login() {
    await page.goto(base + '/login', { waitUntil: 'networkidle' });
    await page.fill('input[name=email]', 'cliente@renice.test');
    await page.fill('input[name=password]', 'password');
    await page.click('button[type=submit]');
    await page.waitForURL((url) => !url.pathname.startsWith('/login'));
}

async function click(locator, pause = 900) {
    await locator.scrollIntoViewIfNeeded();
    const box = await locator.boundingBox();
    const target = { x: box.x + box.width / 2, y: box.y + box.height / 2 };
    await page.mouse.move(mouse.x, mouse.y);
    await page.mouse.move(target.x, target.y, { steps: 18 });
    mouse = target;
    await page.waitForTimeout(250);
    await page.mouse.down();
    await page.mouse.up();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(pause);
}

await login();
await page.goto(base + '/produtos', { waitUntil: 'networkidle' });

const frames = [];
const cdp = await ctx.newCDPSession(page);
cdp.on('Page.screencastFrame', async ({ data, metadata, sessionId }) => {
    const file = `f${String(frames.length).padStart(4, '0')}.jpg`;
    writeFileSync(`${out}/${file}`, Buffer.from(data, 'base64'));
    frames.push({ file, at: metadata.timestamp });
    await cdp.send('Page.screencastFrameAck', { sessionId }).catch(() => {});
});
await cdp.send('Page.startScreencast', {
    format: 'jpeg',
    quality: 85,
    maxWidth: 1280,
    maxHeight: 800,
});

await page.mouse.move(mouse.x, mouse.y);
await page.waitForTimeout(1500);
await click(page.getByText('Copo Anime 500ml gelo médio').first(), 1500);
await click(page.getByRole('button', { name: 'Adicionar ao carrinho' }), 1500);
await click(page.getByRole('link', { name: /Carrinho/ }), 1300);
await click(page.getByRole('link', { name: 'Finalizar compra' }), 1500);
await click(page.getByRole('button', { name: 'Fazer pedido' }), 2500);
await click(page.getByRole('button', { name: 'Simular pagamento' }), 1500);
await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
await page.waitForTimeout(3000);

await cdp.send('Page.stopScreencast');
await browser.close();

// ffmpeg concat list: each frame lasts until the next one arrives.
const list = frames.map((frame, i) => {
    const next = frames[i + 1]?.at ?? frame.at + 2;
    return `file '${frame.file}'\nduration ${Math.max(0.02, next - frame.at).toFixed(3)}`;
});
writeFileSync(
    `${out}/list.txt`,
    list.join('\n') + `\nfile '${frames.at(-1).file}'\n`,
);

// 12 fps at 960px with one shared palette keeps the GIF near 1 MB.
execFileSync('ffmpeg', [
    '-v',
    'error',
    '-y',
    '-f',
    'concat',
    '-safe',
    '0',
    '-i',
    join(out, 'list.txt'),
    '-vf',
    'fps=12,scale=960:-1:flags=lanczos,split[a][b];[a]palettegen=max_colors=160:stats_mode=diff[p];[b][p]paletteuse=dither=bayer:bayer_scale=4:diff_mode=rectangle',
    gif,
]);
rmSync(out, { recursive: true, force: true });
console.log(
    `${gif}: ${frames.length} frames, ${(frames.at(-1).at - frames[0].at).toFixed(1)}s`,
);
