import { DataTable } from 'simple-datatables';
import placeHolderImage from '../images/image-1@2x.jpg'


// Initialize DataTable after Alpine has started to avoid interfering with x-teleport processing
// This ensures Alpine directives (like teleport) are bound before the table DOM is mutated.
document.addEventListener('alpine:init', () => {
    if (document.getElementById("data-table-simple") && typeof DataTable !== 'undefined') {
        const teamMemberTable = new DataTable("#data-table-simple", {
            searchable: true,
            // perPageSelect: false
        });
        // MutationObserver to detect changes in the DOM
        imageObserver(teamMemberTable, 'data-table-simple');
    }
});


function imageObserver(table, tableId) {
    table.on('datatable.init', () => {
        loadImages();
    });

    // MutationObserver to detect changes in the DOM
    const observer = new MutationObserver(() => {
        loadImages();
    });

    const target = document.getElementById(tableId);
    if (target) {
        observer.observe(target, { childList: true, subtree: true });
    }
}

function loadImages() {
    const placeholders = document.querySelectorAll('.image-placeholder');

    placeholders.forEach(placeholder => {
        if (!placeholder.hasChildNodes()) {
            const img = document.createElement('img');
            img.src = placeholder.getAttribute('data-src');
            img.alt = "Image";
            img.loading = "lazy";
            img.className = "h-10 w-10 rounded-lg lazy-image";
            img.onerror = () => {
                img.src = placeHolderImage;
            }
            placeholder.innerHTML = "";
            placeholder.appendChild(img);
        }
    });
}
