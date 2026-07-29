// Convert phone screenshots to the 9:16 ratio Play Console accepts.
//
// Phones capture at 9:20 (e.g. 1080x2400), which Play rejects with
// "crop it so that it's the correct aspect ratio". This pads each image
// onto a 1080x1920 canvas in ShulePRO navy instead of cropping, so
// nothing in the screenshot is lost.
//
//   1. Put your phone screenshots in ~/Downloads/shulepro-screenshots/raw/
//   2. node assets/fit-screenshots.js
//   3. Upload the results from ~/Downloads/shulepro-screenshots/
//
const sharp = require('sharp');
const path = require('path');
const fs = require('fs');
const os = require('os');

const BASE = path.join(os.homedir(), 'Downloads', 'shulepro-screenshots');
const IN = path.join(BASE, 'raw');
const CANVAS = { width: 1080, height: 1920, background: '#0f2942' };

if (!fs.existsSync(IN)) {
    fs.mkdirSync(IN, { recursive: true });
    console.log('Created ' + IN + '\nPut your phone screenshots there, then run this again.');
    process.exit(0);
}

const files = fs.readdirSync(IN).filter(f => /\.(png|jpe?g)$/i.test(f));
if (!files.length) {
    console.log('No images found in ' + IN);
    process.exit(0);
}

(async () => {
    for (const [i, file] of files.entries()) {
        const out = path.join(BASE, `${i + 3}-${path.parse(file).name}.png`);
        await sharp(path.join(IN, file))
            .resize(CANVAS.width, CANVAS.height, { fit: 'contain', background: CANVAS.background })
            .png()
            .toFile(out);
        console.log('fitted', path.basename(out), '1080x1920');
    }
    console.log('\nDone — upload everything in ' + BASE);
})();
