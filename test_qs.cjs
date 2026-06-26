const { JSDOM } = require('jsdom');
const dom = new JSDOM(`<img class="md:block">`);
try {
    const el = dom.window.document.querySelector('img.md\\:block');
    console.log("Success:", !!el);
} catch (e) {
    console.log("Error:", e.message);
}
