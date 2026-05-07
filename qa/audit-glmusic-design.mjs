import playwright from '/Users/luismorales/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/index.js';
import { writeFile, mkdir } from 'node:fs/promises';

const { chromium } = playwright;
const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const baseUrl = 'http://glmusic.local';

await mkdir('qa', { recursive: true });

const browser = await chromium.launch({
  executablePath: chromePath,
  headless: true,
});

async function auditViewport(name, width, height) {
  const page = await browser.newPage({ viewport: { width, height }, deviceScaleFactor: 1 });
  await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  await page.screenshot({ path: `qa/glmusic-${name}-home.png`, fullPage: true });

  const data = await page.evaluate(() => {
    const css = (el) => window.getComputedStyle(el);
    const pick = (el) => {
      const s = css(el);
      return {
        tag: el.tagName.toLowerCase(),
        id: el.id || null,
        className: typeof el.className === 'string' ? el.className : null,
        text: (el.innerText || el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 220),
        color: s.color,
        background: s.backgroundImage !== 'none' ? s.backgroundImage : s.backgroundColor,
        fontFamily: s.fontFamily,
        fontSize: s.fontSize,
        fontWeight: s.fontWeight,
        lineHeight: s.lineHeight,
        borderRadius: s.borderRadius,
        padding: s.padding,
        margin: s.margin,
      };
    };

    const sections = [...document.querySelectorAll('section, main > div, #contenido > div, footer')]
      .filter((el) => {
        const r = el.getBoundingClientRect();
        return r.width > 50 && r.height > 40;
      })
      .slice(0, 24)
      .map((el) => {
        const r = el.getBoundingClientRect();
        return { ...pick(el), rect: { x: r.x, y: r.y, width: r.width, height: r.height } };
      });

    const headings = [...document.querySelectorAll('h1,h2,h3')]
      .filter((el) => (el.innerText || '').trim())
      .slice(0, 30)
      .map(pick);

    const buttons = [...document.querySelectorAll('a,button,input[type="submit"]')]
      .filter((el) => {
        const r = el.getBoundingClientRect();
        return r.width > 20 && r.height > 15 && (el.innerText || el.value || el.getAttribute('aria-label') || '').trim();
      })
      .slice(0, 40)
      .map((el) => ({ ...pick(el), href: el.href || null, value: el.value || null }));

    const nav = [...document.querySelectorAll('header a, .site-header a, nav a')]
      .filter((el) => (el.innerText || el.getAttribute('aria-label') || '').trim() || el.querySelector('img'))
      .slice(0, 30)
      .map((el) => ({ text: (el.innerText || el.getAttribute('aria-label') || 'logo').trim(), href: el.href || null }));

    const images = [...document.images]
      .filter((img) => img.getBoundingClientRect().width > 80 && img.getBoundingClientRect().height > 60)
      .slice(0, 30)
      .map((img) => {
        const r = img.getBoundingClientRect();
        return {
          src: img.currentSrc || img.src,
          alt: img.alt || null,
          rect: { x: r.x, y: r.y, width: r.width, height: r.height },
          objectFit: css(img).objectFit,
        };
      });

    const bodyStyle = css(document.body);
    const header = document.querySelector('header, .site-header');
    const footer = document.querySelector('footer');

    return {
      title: document.title,
      url: location.href,
      body: {
        color: bodyStyle.color,
        background: bodyStyle.backgroundColor,
        fontFamily: bodyStyle.fontFamily,
        fontSize: bodyStyle.fontSize,
      },
      header: header ? pick(header) : null,
      footer: footer ? pick(footer) : null,
      nav,
      sections,
      headings,
      buttons,
      images,
      viewport: { width: innerWidth, height: innerHeight, scrollHeight: document.documentElement.scrollHeight },
    };
  });

  await writeFile(`qa/glmusic-${name}-audit.json`, JSON.stringify(data, null, 2));
  await page.close();
  return data;
}

const desktop = await auditViewport('desktop', 1440, 1200);
const mobile = await auditViewport('mobile', 390, 1200);

const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
const links = await page.evaluate(() => [...new Map([...document.querySelectorAll('a[href]')]
  .map((a) => [a.href, (a.innerText || a.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ')])
  .filter(([href]) => href.startsWith(location.origin))
  .slice(0, 80)).entries()].map(([href, text]) => ({ href, text })));
await writeFile('qa/glmusic-links.json', JSON.stringify(links, null, 2));

const likelyPages = links.filter((link) => /curso|tienda|shop|producto|favorito|login|mi-cuenta|account|blog|libreria|plan/i.test(`${link.href} ${link.text}`)).slice(0, 8);
const pageAudits = [];
for (const link of likelyPages) {
  try {
    await page.goto(link.href, { waitUntil: 'networkidle', timeout: 30000 });
    const slug = new URL(link.href).pathname.replace(/^\/|\/$/g, '').replace(/[^a-z0-9-]+/gi, '-') || 'home';
    await page.screenshot({ path: `qa/glmusic-page-${slug}.png`, fullPage: false });
    pageAudits.push({
      ...link,
      title: await page.title(),
      snapshot: await page.evaluate(() => ({
        h1: [...document.querySelectorAll('h1')].map((el) => el.innerText.trim()).filter(Boolean).slice(0, 3),
        h2: [...document.querySelectorAll('h2')].map((el) => el.innerText.trim()).filter(Boolean).slice(0, 8),
        bodyClass: document.body.className,
      })),
    });
  } catch (error) {
    pageAudits.push({ ...link, error: error.message });
  }
}
await writeFile('qa/glmusic-page-audits.json', JSON.stringify(pageAudits, null, 2));
await page.close();

await browser.close();

console.log(JSON.stringify({
  desktop: { title: desktop.title, sections: desktop.sections.length, headings: desktop.headings.length },
  mobile: { title: mobile.title, sections: mobile.sections.length, headings: mobile.headings.length },
  likelyPages: pageAudits.length,
}, null, 2));
