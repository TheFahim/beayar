/**
 * Sidebar Dropdown Auto-Open Functionality
 * Automatically opens sidebar dropdowns when a child route is active
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all dropdown containers in the sidebar
    const dropdownContainers = document.querySelectorAll('[data-collapse-toggle]');
    
    dropdownContainers.forEach(button => {
        const dropdownId = button.getAttribute('data-collapse-toggle');
        const dropdown = document.getElementById(dropdownId);
        
        if (dropdown) {
            // Check if any child link in this dropdown is active
            const activeLinks = dropdown.querySelectorAll('a.bg-gray-100, a.dark\\:bg-gray-700');
            
            if (activeLinks.length > 0) {
                // Remove the 'hidden' class to show the dropdown
                dropdown.classList.remove('hidden');
                
                // Update the button's aria-expanded attribute
                button.setAttribute('aria-expanded', 'true');
                
                // Optionally, add a visual indicator to the button that it's expanded
                button.classList.add('bg-gray-100', 'dark:bg-gray-700');
                
                // Rotate the arrow icon if it exists
                const arrowIcon = button.querySelector('svg:last-child');
                if (arrowIcon) {
                    arrowIcon.style.transform = 'rotate(180deg)';
                }
            }
        }
    });
});