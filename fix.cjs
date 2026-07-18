const fs = require('fs');
let content = fs.readFileSync('resources/views/admin/layouts/master.blade.php', 'utf8');
let lines = content.split('\n');

// 824 is index 823, 984 is index 983
lines.splice(823, 984 - 824 + 1);

fs.writeFileSync('resources/views/admin/layouts/master.blade.php', lines.join('\n'));
console.log('Fixed exactly by line number');
