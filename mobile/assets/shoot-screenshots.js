// Capture Play Store phone screenshots of the live ShulePRO site using the
// system Chrome. Public pages only — no credentials are ever entered here.
//
//   node assets/shoot-screenshots.js
//
const puppeteer = require('puppeteer-core');
const path = require('path');
const os = require('os');

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const OUT = path.join(os.homedir(), 'Downloads', 'shulepro-screenshots');
const BASE = 'https://schoolms.chanitech.co.tz';

// Pixel 7-ish viewport at 3x so output is 1236x2745 — well above Play's
// 1080px promotion threshold and inside its 3840px ceiling.
const VIEWPORT = { width: 412, height: 915, deviceScaleFactor: 3, isMobile: true, hasTouch: true };

const SHOTS = [
    { file: '1-parent-signin.png', url: `${BASE}/guardian/login` },
    { file: '2-staff-signin.png',  url: `${BASE}/login` },
];

(async () => {
    const fs = require('fs');
    fs.mkdirSync(OUT, { recursive: true });

    const browser = await puppeteer.launch({
        executablePath: CHROME,
        headless: 'new',
        args: ['--hide-scrollbars', '--no-sandbox'],
    });

    const page = await browser.newPage();
    await page.setViewport(VIEWPORT);
    await page.setUserAgent(
        'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36'
    );

    for (const shot of SHOTS) {
        await page.goto(shot.url, { waitUntil: 'networkidle2', timeout: 60000 });
        await new Promise(r => setTimeout(r, 1200)); // let fonts/animations settle
        const out = path.join(OUT, shot.file);
        await page.screenshot({ path: out });
        console.log('saved', out);
    }

    await browser.close();
})();
