const fs = require('fs');
const content = fs.readFileSync('resources/views/beranda/index.blade.php', 'utf8');
const match = content.match(/const BerandaPage = \{([\s\S]*?)\};\s*\/\/\s*Initialize/);
if (match) {
    fs.writeFileSync('test_js.js', 'const BerandaPage = {' + match[1] + '};', 'utf8');
    console.log('Extracted JS');
} else {
    console.log('Not found');
}
