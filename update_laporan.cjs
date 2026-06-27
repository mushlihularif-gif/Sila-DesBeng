const fs=require('fs'); 
const lines=fs.readFileSync('D:/laragon/www/SilaDesBeng/resources/views/lurah/laporan/index.blade.php', 'utf8').split('\n'); 
const top=lines.slice(0, 20).join('\n'); 
const bottom=lines.slice(255).join('\n'); 
const mid=`
    <div id="laporan-container">
        @include('lurah.laporan.partials.laporan_content')
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('laporan-container');
    
    function attachAjaxEvents() {
        // Filter links
        const filterLinks = container.querySelectorAll('.d-flex.gap-2.mb-4.overflow-auto a');
        filterLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchData(this.href);
            });
        });

        // Pagination links
        const paginationLinks = container.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchData(this.href);
            });
        });

        // Filter Form
        const filterForm = container.querySelector('form');
        if (filterForm) {
            // Select changes trigger submit
            const selects = filterForm.querySelectorAll('select');
            selects.forEach(select => {
                select.addEventListener('change', () => filterForm.dispatchEvent(new Event('submit')));
            });

            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const params = new URLSearchParams(formData);
                const url = this.action + '?' + params.toString();
                fetchData(url);
            });
        }
    }

    function fetchData(url) {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            
            attachAjaxEvents();
            
            // Re-init count-up
            if(typeof animateValue !== 'undefined') {
                const countUps = container.querySelectorAll('.count-up');
                countUps.forEach(function(el) {
                    let endVal = parseInt(el.getAttribute('data-value')) || 0;
                    animateValue(el, 0, endVal, 1000);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        });
    }

    attachAjaxEvents();
});
</script>
`;
fs.writeFileSync('D:/laragon/www/SilaDesBeng/resources/views/lurah/laporan/index.blade.php', top + mid);
