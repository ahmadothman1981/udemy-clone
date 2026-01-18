try {
    const fs = require('fs');

    const en = JSON.parse(fs.readFileSync('resources/js/locales/en.json', 'utf8'));
    console.log('EN JSON is valid.');

    const ar = JSON.parse(fs.readFileSync('resources/js/locales/ar.json', 'utf8'));
    console.log('AR JSON is valid.');

} catch (e) {
    console.error('JSON Validation Error:', e.message);
}
