const fs = require('fs');
const file = 'resources/views/beranda/index.blade.php';
let content = fs.readFileSync(file, 'utf8');

// Fix the AJAX search section insertion
const oldAjax = `                        sectionIds.forEach(id => {
                            const oldEl = document.getElementById(id);
                            const newEl = newDoc.getElementById(id);
                            if (oldEl && newEl) {
                                oldEl.innerHTML = newEl.innerHTML;
                                updatedAny = true;
                            } else if (oldEl && !newEl) {
                                oldEl.innerHTML = ''; // Clear if removed
                                updatedAny = true;
                            }
                        });`;

const newAjax = `                        sectionIds.forEach(id => {
                            const oldEl = document.getElementById(id);
                            const newEl = newDoc.getElementById(id);
                            if (oldEl && newEl) {
                                oldEl.innerHTML = newEl.innerHTML;
                                updatedAny = true;
                            } else if (oldEl && !newEl) {
                                oldEl.innerHTML = ''; // Clear if removed
                                updatedAny = true;
                            } else if (!oldEl && newEl) {
                                // If search-results-section didn't exist before, insert it before populer-section
                                if (id === 'search-results-section') {
                                    const populer = document.getElementById('populer-section');
                                    if (populer) {
                                        const wrapper = document.createElement('div');
                                        wrapper.id = 'search-results-section';
                                        wrapper.className = newEl.className;
                                        wrapper.innerHTML = newEl.innerHTML;
                                        populer.parentNode.insertBefore(wrapper, populer);
                                        updatedAny = true;
                                    }
                                }
                            }
                        });`;

content = content.replace(oldAjax, newAjax);
fs.writeFileSync(file, content, 'utf8');
console.log("AJAX insertion fixed");
