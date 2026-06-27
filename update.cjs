const fs=require('fs'); 
const lines=fs.readFileSync('D:/laragon/www/SilaDesBeng/resources/views/admin/aktivitas/requests.blade.php', 'utf8').split('\n'); 
const top=lines.slice(0, 41).join('\n'); 
const bottom=lines.slice(1120).join('\n'); 
const mid='\n    <div id="requests-container">\n        @include(\'admin.aktivitas.partials.requests_content\')\n    </div>\n</div>\n'; 
fs.writeFileSync('D:/laragon/www/SilaDesBeng/resources/views/admin/aktivitas/requests.blade.php', top + mid + bottom);
