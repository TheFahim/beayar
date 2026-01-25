import {DataTable} from 'simple-datatables';
import placeHolderImage from '../images/image-1@2x.jpg'


if (document.getElementById("team-members") && typeof DataTable !== 'undefined') {
    const teamMemberTable = new DataTable("#team-members", {
        searchable: true,
        // perPageSelect: false
    });
    // MutationObserver to detect changes in the DOM
    imageObserver(teamMemberTable, 'team-members');
}
if (document.getElementById("publication") && typeof DataTable !== 'undefined') {
    const pubTable = new DataTable("#publication", {
        searchable: true,
        // perPageSelect: false
    });
    // MutationObserver to detect changes in the DOM
    imageObserver(pubTable, 'publication');
}
if (document.getElementById("pub-area") && typeof DataTable !== 'undefined') {
    const pubAreaTable = new DataTable("#pub-area", {
        searchable: true,
        // perPageSelect: false
    });
    // MutationObserver to detect changes in the DOM
    // imageObserver(teamMemberTable, 'pubAreaTable');
}
if (document.getElementById("gallery") && typeof DataTable !== 'undefined') {
    const galleryTable = new DataTable("#gallery", {
        searchable: true,
        // perPageSelect: false
    });
    // MutationObserver to detect changes in the DOM
    imageObserver(galleryTable, 'gallery');
}

if (document.getElementById("news") && typeof DataTable !== 'undefined') {
    const newsTable = new DataTable("#news", {
        searchable: true,
        // perPageSelect: false
    });
    // MutationObserver to detect changes in the DOM
    imageObserver(newsTable, 'news');
}

if (document.getElementById("contacts") && typeof DataTable !== 'undefined') {
    const contactsTable = new DataTable("#contacts", {
        searchable: true,
        // perPageSelect: false
    });

}

if (document.getElementById("permissions") && typeof DataTable !== 'undefined') {
    const permissionTable = new DataTable("#permissions", {
        searchable: true,
        // perPageSelect: false
    });

}

if (document.getElementById("resources") && typeof DataTable !== 'undefined') {
    const resourcesTable = new DataTable("#resources", {
        searchable: true,
        // perPageSelect: false
    });

}
if (document.getElementById("blogs") && typeof DataTable !== 'undefined') {
    const blogsTable = new DataTable("#blogs", {
        searchable: true,
        // perPageSelect: false
    });

    imageObserver(blogsTable, 'blogs');


}
if (document.getElementById("services") && typeof DataTable !== 'undefined') {
    const serviceTable = new DataTable("#services", {
        searchable: true,
        // perPageSelect: false
    });

    imageObserver(serviceTable, 'services');


}

if (document.getElementById("users") && typeof DataTable !== 'undefined') {
    const userTable = new DataTable("#users", {
        searchable: true,
        // perPageSelect: false
    });

    imageObserver(userTable, 'users');


}

if (document.getElementById("tech-table") && typeof DataTable !== 'undefined') {
    const techTable = new DataTable("#tech-table", {
        searchable: true,
        // perPageSelect: false
    });
    imageObserver(techTable, 'tech-table');

}



function imageObserver(table, tableId){
    table.on('datatable.init', () => {
        loadImages();
    });

    // MutationObserver to detect changes in the DOM
    const observer = new MutationObserver(() => {
        loadImages();
    });

    observer.observe(document.getElementById(tableId), { childList: true, subtree: true });

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
