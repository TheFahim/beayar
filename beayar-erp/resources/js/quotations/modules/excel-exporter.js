import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

export default class ExcelExporter {
    constructor(data, isTechnical = false) {
        this.data = data;
        this.isTechnical = isTechnical;
    }

    async generate() {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Quotation');

        this.setupStyles();
        this.setupColumns(worksheet);
        await this.setupHeader(workbook, worksheet);
        this.setupBillToShipTo(worksheet);
        this.setupQuotationInfo(worksheet);
        
        const tableStartRow = await this.setupProductTable(workbook, worksheet);
        
        if (!this.isTechnical) {
            this.setupTotals(worksheet, tableStartRow);
        } else {
            // If technical, we might need to adjust current row index or just leave it
            // The setupProductTable returns the next available row index
        }

        // We need to track the last row used to place terms and signature
        // Since setupTotals and setupProductTable are dynamic, we need a way to track current row.
        // Let's refactor slightly to track currentRow as a class property or return it.
    }
    
    // ... implementing methods ...
}

export async function downloadQuotationExcel(data, isTechnical, btnElement) {
    const originalText = btnElement.innerHTML;
    
    try {
        btnElement.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';
        btnElement.disabled = true;

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Quotation');

        // --- Styles ---
        const boldFont = { name: 'Calibri', size: 11, bold: true };
        const normalFont = { name: 'Calibri', size: 11 };
        const titleFont = { name: 'Calibri', size: 14, bold: true };
        const headerFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E40AF' } }; // Blue-800
        const headerFont = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FFFFFFFF' } }; // White
        const borderStyle = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
        const centerAlign = { vertical: 'middle', horizontal: 'center', wrapText: true };
        const leftAlign = { vertical: 'middle', horizontal: 'left', wrapText: true };
        const rightAlign = { vertical: 'middle', horizontal: 'right' };

        // --- Columns Setup ---
        worksheet.columns = [
            { key: 'item', width: 25 },
            { key: 'spec', width: 40 },
            { key: 'qty', width: 8 },
            { key: 'unit', width: 8 },
            { key: 'delivery', width: 12 },
            { key: 'sample', width: 15 },
            { key: 'price', width: 15 },
            { key: 'total', width: 15 },
        ];

        // --- Header Section ---
        let logoId = null;
        if (data.logo_url) {
            try {
                const response = await fetch(data.logo_url);
                const buffer = await response.arrayBuffer();
                logoId = workbook.addImage({
                    buffer: buffer,
                    extension: 'png',
                });
            } catch (e) {
                console.warn('Failed to load logo', e);
            }
        }

        // Company Info Text (Right Aligned)
        worksheet.mergeCells('D1:H1');
        worksheet.getCell('D1').value = 'Malek Mansion (Ground), 128 Motijheel C/A, Dhaka-1000';
        worksheet.getCell('D1').alignment = rightAlign;
        worksheet.getCell('D1').font = { size: 9, color: { argb: 'FF4B5563' } };

        worksheet.mergeCells('D2:H2');
        worksheet.getCell('D2').value = 'ataur@optimech.com.bd, ataur.optimech@gmail.com';
        worksheet.getCell('D2').alignment = rightAlign;
        worksheet.getCell('D2').font = { size: 9, color: { argb: 'FF4B5563' } };

        worksheet.mergeCells('D3:H3');
        worksheet.getCell('D3').value = '+8801841176747, +8801712117558';
        worksheet.getCell('D3').alignment = rightAlign;
        worksheet.getCell('D3').font = { size: 9, color: { argb: 'FF4B5563' } };

        worksheet.mergeCells('D4:H4');
        worksheet.getCell('D4').value = 'www.optimech.com.bd';
        worksheet.getCell('D4').alignment = rightAlign;
        worksheet.getCell('D4').font = { size: 9, color: { argb: 'FF4B5563' } };

        if (logoId !== null) {
            worksheet.addImage(logoId, {
                tl: { col: 0, row: 0 },
                ext: { width: 150, height: 60 }
            });
        }

        worksheet.addRow([]); // Spacer

        // --- Bill To / Ship To ---
        const startRow = 6;
        worksheet.mergeCells(`A${startRow}:C${startRow}`);
        worksheet.getCell(`A${startRow}`).value = 'BILL TO';
        worksheet.getCell(`A${startRow}`).font = { ...boldFont, color: { argb: 'FF4B5563' } };

        const billToStart = startRow + 1;
        worksheet.mergeCells(`A${billToStart}:C${billToStart}`);
        worksheet.getCell(`A${billToStart}`).value = data.customer.name;
        worksheet.getCell(`A${billToStart}`).font = boldFont;

        let currentRow = billToStart + 1;
        const customerFields = [
            data.customer.designation, 
            data.customer.department, 
            data.customer.company,
            data.customer.address, 
            data.customer.phone, 
            data.customer.email
        ];
        if (data.customer.attention) customerFields.push('Attention: ' + data.customer.attention);

        customerFields.forEach(text => {
            if (text) {
                worksheet.mergeCells(`A${currentRow}:C${currentRow}`);
                worksheet.getCell(`A${currentRow}`).value = text;
                worksheet.getCell(`A${currentRow}`).font = { size: 10, color: { argb: 'FF4B5563' } };
                currentRow++;
            }
        });

        worksheet.getCell(`D${startRow}`).value = 'SHIP TO';
        worksheet.getCell(`D${startRow}`).font = { ...boldFont, color: { argb: 'FF4B5563' } };

        let shipRow = startRow + 1;
        const shipFields = [data.shipTo.company, data.shipTo.address];
        shipFields.forEach(text => {
            if (text) {
                worksheet.mergeCells(`D${shipRow}:E${shipRow}`);
                worksheet.getCell(`D${shipRow}`).value = text;
                worksheet.getCell(`D${shipRow}`).font = { size: 10, color: { argb: 'FF4B5563' } };
                worksheet.getCell(`D${shipRow}`).alignment = { wrapText: true, vertical: 'top' };
                shipRow++;
            }
        });

        // Quotation Info (Right Side)
        worksheet.mergeCells(`F${startRow}:H${startRow}`);
        const titlePrefix = data.revisionNo !== 'R00' ? 'REVISED ' : '';
        const titleType = isTechnical ? 'TECHNICAL' : 'COMMERCIAL';
        worksheet.getCell(`F${startRow}`).value = `${titlePrefix}${titleType} QUOTATION`;
        worksheet.getCell(`F${startRow}`).font = { ...boldFont, size: 12 };
        worksheet.getCell(`F${startRow}`).alignment = rightAlign;

        const qNoRow = startRow + 1;
        worksheet.mergeCells(`F${qNoRow}:H${qNoRow}`);
        worksheet.getCell(`F${qNoRow}`).value = `${data.quotationNo}${data.revisionNo !== 'R00' ? ' (' + data.revisionNo + ')' : ''}`;
        worksheet.getCell(`F${qNoRow}`).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF3B82F6' } }; // Blue-500
        worksheet.getCell(`F${qNoRow}`).font = { color: { argb: 'FFFFFFFF' }, bold: true };
        worksheet.getCell(`F${qNoRow}`).alignment = { horizontal: 'center', vertical: 'middle' };

        const dateRow = qNoRow + 1;
        worksheet.getCell(`F${dateRow}`).value = 'Date';
        worksheet.mergeCells(`G${dateRow}:H${dateRow}`);
        worksheet.getCell(`G${dateRow}`).value = data.date;
        worksheet.getCell(`G${dateRow}`).alignment = rightAlign;

        const validRow = dateRow + 1;
        worksheet.getCell(`F${validRow}`).value = 'Validity';
        worksheet.mergeCells(`G${validRow}:H${validRow}`);
        worksheet.getCell(`G${validRow}`).value = `(${data.validityDays} days) ${data.validity}`;
        worksheet.getCell(`G${validRow}`).alignment = rightAlign;

        // Ensure we move past the tallest block
        let tableStartRow = Math.max(currentRow, shipRow, validRow) + 2;

        // --- Product Table ---
        const headerRow = worksheet.getRow(tableStartRow);
        headerRow.values = ['Item Name', 'Specification', 'Qty', 'Unit', 'Delivery', 'Sample Photo', 'Unit Price', 'Total'];
        headerRow.eachCell((cell) => {
            cell.fill = headerFill;
            cell.font = headerFont;
            cell.alignment = centerAlign;
            cell.border = borderStyle;
        });

        if (isTechnical) {
            worksheet.getColumn('price').hidden = true;
            worksheet.getColumn('total').hidden = true;
        }

        let currentRowIdx = tableStartRow + 1;

        for (const product of data.products) {
            const row = worksheet.getRow(currentRowIdx);

            // Item Name Column (A)
            let nameText = `Name: ${product.name}`;
            if (product.size) nameText += `\nSize: ${product.size}`;
            if (product.req_no) nameText += `\nReq/PR: ${product.req_no}`;
            row.getCell(1).value = nameText;
            row.getCell(1).alignment = leftAlign;

            // Specification (B)
            if (!product.skip_spec) {
                let specText = product.specification;
                if (product.brand_origin) specText += `\nBrand/Origin: ${product.brand_origin}`;
                if (product.add_spec) specText += `\n${product.add_spec}`;
                row.getCell(2).value = specText;
            }

            // Qty (C)
            row.getCell(3).value = product.quantity;
            // Unit (D)
            row.getCell(4).value = product.unit;
            // Delivery (E)
            row.getCell(5).value = product.delivery_time;

            // Image (F)
            if (!product.skip_image && product.image_url) {
                try {
                    const imgResp = await fetch(product.image_url);
                    const imgBuff = await imgResp.arrayBuffer();
                    const imgId = workbook.addImage({
                        buffer: imgBuff,
                        extension: 'png',
                    });
                    product._imageId = imgId;
                } catch (e) {
                    row.getCell(6).value = 'Image Error';
                }
            } else if (!product.skip_image && !product.image_url) {
                row.getCell(6).value = 'N/A';
            }

            // Price (G)
            row.getCell(7).value = product.unit_price;
            row.getCell(7).numFmt = '#,##0.00';

            // Total (H)
            row.getCell(8).value = product.total;
            row.getCell(8).numFmt = '#,##0.00';

            row.eachCell((cell) => {
                cell.border = borderStyle;
                if (cell.col !== 1 && cell.col !== 2) cell.alignment = centerAlign;
                if (cell.col === 7 || cell.col === 8) cell.alignment = rightAlign;
            });

            currentRowIdx++;
        }

        // Apply Merges
        data.products.forEach((p, index) => {
            const startR = tableStartRow + 1 + index;
            // Spec Merge
            if (p.spec_rowspan > 1) {
                worksheet.mergeCells(startR, 2, startR + p.spec_rowspan - 1, 2);
            }
            // Image Merge
            if (p.image_rowspan > 1) {
                worksheet.mergeCells(startR, 6, startR + p.image_rowspan - 1, 6);
            }

            // Add Image to Cell
            if (!p.skip_image && p._imageId !== undefined) {
                const endR = startR + p.image_rowspan - 1;
                worksheet.addImage(p._imageId, {
                    tl: { col: 5, row: startR - 1 },
                    br: { col: 6, row: endR },
                    editAs: 'oneCell'
                });
            }
        });

        // --- Totals ---
        if (!isTechnical) {
            let totalRow = currentRowIdx;
            // Subtotal
            worksheet.mergeCells(`A${totalRow}:D${totalRow}`);
            worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
            worksheet.getCell(`E${totalRow}`).value = 'Subtotal';
            worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
            worksheet.getCell(`E${totalRow}`).border = borderStyle;

            worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
            worksheet.getCell(`G${totalRow}`).value = data.subtotal;
            worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
            worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
            worksheet.getCell(`G${totalRow}`).border = borderStyle;
            totalRow++;

            // Discount
            if (data.discount_amount) {
                worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                worksheet.getCell(`E${totalRow}`).value = 'Discount';
                worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`E${totalRow}`).border = borderStyle;

                worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                worksheet.getCell(`G${totalRow}`).value = data.discount_amount;
                worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`G${totalRow}`).border = borderStyle;
                totalRow++;

                worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                worksheet.getCell(`E${totalRow}`).value = 'Discount Less Subtotal';
                worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`E${totalRow}`).border = borderStyle;

                worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                worksheet.getCell(`G${totalRow}`).value = data.subtotal - data.discount_amount;
                worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`G${totalRow}`).border = borderStyle;
                totalRow++;
            }

            // Shipping
            if (data.shipping) {
                worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                worksheet.getCell(`E${totalRow}`).value = 'Shipping';
                worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`E${totalRow}`).border = borderStyle;

                worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                worksheet.getCell(`G${totalRow}`).value = data.shipping;
                worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`G${totalRow}`).border = borderStyle;
                totalRow++;
            }

            // VAT
            if (data.vat_amount) {
                worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                worksheet.getCell(`E${totalRow}`).value = `VAT (${data.vat_percentage}%)`;
                worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`E${totalRow}`).border = borderStyle;

                worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                worksheet.getCell(`G${totalRow}`).value = data.vat_amount;
                worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                worksheet.getCell(`G${totalRow}`).border = borderStyle;
                totalRow++;
            }

            // Grand Total
            worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
            worksheet.getCell(`E${totalRow}`).value = 'Grand Total';
            worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
            worksheet.getCell(`E${totalRow}`).font = boldFont;
            worksheet.getCell(`E${totalRow}`).border = borderStyle;

            worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
            worksheet.getCell(`G${totalRow}`).value = data.total;
            worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
            worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
            worksheet.getCell(`G${totalRow}`).font = boldFont;
            worksheet.getCell(`G${totalRow}`).border = borderStyle;

            currentRowIdx = totalRow + 2;
        } else {
            currentRowIdx += 2;
        }

        // --- Terms and Signature ---
        const termsRow = currentRowIdx;
        worksheet.mergeCells(`A${termsRow}:E${termsRow + 5}`);
        const termsCell = worksheet.getCell(`A${termsRow}`);
        
        // Strip HTML tags from terms and conditions
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = data.terms_conditions || '';
        const plainTerms = tempDiv.textContent || tempDiv.innerText || '';
        
        termsCell.value = plainTerms;
        termsCell.alignment = { vertical: 'top', horizontal: 'left', wrapText: true };
        termsCell.border = borderStyle;

        // Signature & Seal (if available)
        try {
            if (data.seal_url) {
                const sealResp = await fetch(data.seal_url);
                if (sealResp.ok) {
                    const sealBuff = await sealResp.arrayBuffer();
                    const sealId = workbook.addImage({
                        buffer: sealBuff,
                        extension: 'jpg',
                    });
                    worksheet.addImage(sealId, {
                        tl: { col: 5, row: termsRow },
                        ext: { width: 80, height: 80 }
                    });
                }
            }
        } catch (e) {}

        try {
            if (data.signature_url) {
                const sigResp = await fetch(data.signature_url);
                if (sigResp.ok) {
                    const sigBuff = await sigResp.arrayBuffer();
                    const sigId = workbook.addImage({
                        buffer: sigBuff,
                        extension: 'jpg',
                    });
                    worksheet.addImage(sigId, {
                        tl: { col: 6, row: termsRow },
                        ext: { width: 100, height: 50 }
                    });
                }
            }
        } catch (e) {}

        worksheet.getCell(`G${termsRow + 4}`).value = "Mohammad Ataur Rahman";
        worksheet.getCell(`G${termsRow + 4}`).font = { size: 10 };
        worksheet.getCell(`G${termsRow + 4}`).alignment = centerAlign;

        worksheet.getCell(`G${termsRow + 5}`).value = "Authorized By";
        worksheet.getCell(`G${termsRow + 5}`).font = { size: 10, italic: true };
        worksheet.getCell(`G${termsRow + 5}`).alignment = centerAlign;

        // Download
        const buffer = await workbook.xlsx.writeBuffer();
        const fileName = `Quotation_${data.quotationNo}_${data.revisionNo}.xlsx`;
        saveAs(new Blob([buffer]), fileName);

    } catch (error) {
        console.error('Excel Generation Error:', error);
        alert('Failed to generate Excel file. Please try again.');
    } finally {
        btnElement.innerHTML = originalText;
        btnElement.disabled = false;
    }
}
