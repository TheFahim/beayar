import { downloadQuotationExcel } from './modules/excel-exporter';

document.addEventListener('DOMContentLoaded', () => {
    const downloadBtn = document.querySelector('#downloadExcelBtn');
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            // Get data from window object (populated in blade)
            const data = window.quotationData;
            
            // Check if technical mode is active
            // The button logic in blade is: x-data="{ isTechnical: false }"
            // We can try to access Alpine state or just check the text content as a fallback
            // But accessing Alpine state from outside can be tricky if we don't have the element reference
            // Let's try to find the toggle button and check its text as per the reference implementation
            const toggleBtnSpan = document.querySelector('button span[x-text*="isTechnical"]');
            let isTechnical = false;
            
            if (toggleBtnSpan) {
                // If the text says "Show Commercial", it means we are in Technical mode
                isTechnical = toggleBtnSpan.innerText.includes('Show Commercial');
            } else {
                // Fallback: try to access Alpine data if possible, or default to false
                // Note: Alpine components are not easily accessible globally unless exposed
            }

            if (!data) {
                console.error('Quotation data not found!');
                alert('Error: Quotation data missing.');
                return;
            }

            downloadQuotationExcel(data, isTechnical, this);
        });
    }
});
