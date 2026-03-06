// Enhanced sidebar functionality for modern UI
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll behavior for internal navigation
    const sidebarLinks = document.querySelectorAll('a[href^="/"]');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state to clicked link
            this.style.opacity = '0.7';
            this.style.transform = 'scale(0.98)';
            
            // Reset after a short delay (in case navigation doesn't happen immediately)
            setTimeout(() => {
                this.style.opacity = '';
                this.style.transform = '';
            }, 300);
        });
    });

    // Add keyboard navigation support
    const sidebarItems = document.querySelectorAll('.sidebar-link, button[aria-expanded]');
    sidebarItems.forEach((item, index) => {
        item.setAttribute('data-index', index);
        
        item.addEventListener('keydown', function(e) {
            const currentIndex = parseInt(this.getAttribute('data-index'));
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    const nextItem = sidebarItems[currentIndex + 1];
                    if (nextItem) nextItem.focus();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    const prevItem = sidebarItems[currentIndex - 1];
                    if (prevItem) prevItem.focus();
                    break;
                case 'Enter':
                case ' ':
                    if (this.tagName === 'BUTTON') {
                        e.preventDefault();
                        this.click();
                    }
                    break;
            }
        });
    });

    // Add focus visible styles for better accessibility
    const style = document.createElement('style');
    style.textContent = `
        .sidebar-link:focus-visible,
        button[aria-expanded]:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    `;
    document.head.appendChild(style);
});
