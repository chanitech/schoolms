// Capture Play Store phone screenshots of the live ShulePRO site using the
// system Chrome. Public pages only — no credentials are ever entered here.
//
//   node assets/shoot-screenshots.js
//
// Output is exactly 1080x1920 (9:16) because Play Console rejects anything
// outside the 16:9–9:16 range; modern phone screenshots (9:20) are refused.
//
const puppeteer = require('puppeteer-core');
const sharp = require('sharp');
const path = require('path');
const fs = require('fs');
const os = require('os');

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const OUT = path.join(os.homedir(), 'Downloads', 'shulepro-screenshots');
const BASE = 'https://schoolms.chanitech.co.tz';

const VIEWPORT = { width: 412, height: 915, deviceScaleFactor: 3, isMobile: true, hasTouch: true };
const CANVAS = { width: 1080, height: 1920, background: '#0f2942' }; // 9:16, ShulePRO navy

const SHOTS = [
    { file: '1-parent-signin.png', url: `${BASE}/guardian/login` },
    { file: '2-staff-signin.png',  url: `${BASE}/login` },
];

(async () => {
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

        const raw = await page.screenshot({ fullPage: true });
        const out = path.join(OUT, shot.file);

        // Fit the capture inside a 9:16 canvas, padding with the app's own
        // navy so the bars read as part of the design.
        await sharp(raw)
            .resize(CANVAS.width, CANVAS.height, { fit: 'contain', background: CANVAS.background })
            .png()
            .toFile(out);

        const meta = await sharp(out).metadata();
        console.log('saved', out, `${meta.width}x${meta.height}`);
    }

    await browser.close();
})();
