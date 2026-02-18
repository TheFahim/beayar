import 'suneditor/dist/css/suneditor.min.css';
import suneditor from 'suneditor';
import plugins from 'suneditor/src/plugins';

import katex from 'katex';
import 'katex/dist/katex.min.css'

let textAreaEditors = [];

// Configuration for SunEditor with full button list
const fullEditorConfig = {
    allowedClassNames: [
        'bg-blue-900',
        'text-white',
        'font-bold',
        'p-2',
        'mb-4',
        'list-disc',
        'list-inside',
        'text-sm',
        'space-y-2',
        'text-gray-700'
    ].join('|'), // Creates a regex-like string for allowed classes
    attributesWhitelist: {
        all: 'style,class'
    },
    minWidth: '100%',
    minHeight: '30vh',
    plugins: plugins,
    buttonList: [
        ['undo', 'redo'],
        ['font', 'fontSize', 'formatBlock'],
        ['bold', 'underline', 'italic', 'strike'],
        ['fontColor', 'hiliteColor'],
        ['removeFormat'],
        '/', // Line break
        ['outdent', 'indent'],
        ['align', 'horizontalRule', 'list', 'lineHeight'],
        ['table', 'link'],
        ['fullScreen', 'showBlocks'],
        ['preview']
    ],
    fontSize: [8, 9, 10, 11, 12, 14, 16, 18, 20], // Limit font sizes to max 20px
    defaultStyle: 'font-size: 14px;', // Default font size for pasted content
    pasteTagsWhitelist: 'p|div|br|span|strong|b|em|i|u|s|a|ul|ol|li|h1|h2|h3|h4|h5|h6',
    // Enhanced paste handling for Excel and other sources
    onPaste: function (e, cleanData, maxCharCount, core) {
        // Prevent default paste behavior
        e.preventDefault();

        // Get clipboard data
        const clipboardData = e.clipboardData || window.clipboardData;
        let pastedData = clipboardData.getData('text/html') || clipboardData.getData('text/plain');

        // Clean and normalize the pasted content
        if (pastedData) {
            // Remove all existing font-size styles and replace with 14px
            pastedData = pastedData.replace(/font-size:\s*[^;]+;?/gi, '');
            pastedData = pastedData.replace(/style\s*=\s*["']([^"']*?)["']/gi, function (match, styles) {
                const cleanStyles = styles.replace(/font-size:\s*[^;]+;?/gi, '').trim();
                const newStyles = cleanStyles ? `font-size: 14px; ${cleanStyles}` : 'font-size: 14px;';
                return `style="${newStyles}"`;
            });

            // Add font-size to elements that don't have style attribute
            pastedData = pastedData.replace(/<(p|div|span|h[1-6]|li)([^>]*?)>/gi, function (match, tag, attrs) {
                if (!attrs.includes('style=')) {
                    return `<${tag}${attrs} style="font-size: 14px;">`;
                }
                return match;
            });

            // If no HTML tags, wrap in paragraph with 14px font
            if (!/<[^>]+>/.test(pastedData)) {
                pastedData = `<p style="font-size: 14px;">${pastedData}</p>`;
            }

            // Insert the cleaned content
            core.insertHTML(pastedData);
        }

        return false; // Prevent default paste
    }
};

// Configuration for SunEditor without button controls (for quotations terms & conditions)
const minimalEditorConfig = {
    allowedClassNames: [
        'bg-blue-900',
        'text-white',
        'text-blue-600',
        'text-2xl',
        'font-bold',
        'font-semibold',
        'font-medium',
        'p-2',
        'p-6',
        'px-4',
        'py-3',
        'mb-4',
        'mb-6',
        'max-w-4xl',
        'mx-auto',
        'bg-white',
        'shadow-sm',
        'overflow-x-auto',
        'w-full',
        'table-fixed',
        'border-collapse',
        'text-sm',
        'text-center',
        'border',
        'border-gray-300',
        'align-top',
        'list-disc',
        'list-inside',
        'space-y-2',
        'text-gray-700'
    ].join('|'),
    attributesWhitelist: {
        all: 'style,class'
    },
    minWidth: '100%',
    minHeight: '30vh',
    plugins: plugins,
    buttonList: [], // No buttons for terms and conditions
    fontSize: [8, 9, 10, 11, 12, 14, 16, 18, 20], // Limit font sizes to max 20px
    defaultStyle: 'font-size: 14px;', // Default font size for pasted content
    pasteTagsWhitelist: 'p|div|br|span|strong|b|em|i|u|s|a|ul|ol|li|h1|h2|h3|h4|h5|h6|table|thead|tbody|tr|td|th|colgroup|col',
    // Enhanced paste handling for Excel and other sources
    onPaste: function (e, cleanData, maxCharCount, core) {
        // Prevent default paste behavior
        e.preventDefault();

        // Get clipboard data
        const clipboardData = e.clipboardData || window.clipboardData;
        let pastedData = clipboardData.getData('text/html') || clipboardData.getData('text/plain');

        // Clean and normalize the pasted content
        if (pastedData) {
            // Remove all existing font-size styles and replace with 14px
            pastedData = pastedData.replace(/font-size:\s*[^;]+;?/gi, '');
            pastedData = pastedData.replace(/style\s*=\s*["']([^"']*?)["']/gi, function (match, styles) {
                const cleanStyles = styles.replace(/font-size:\s*[^;]+;?/gi, '').trim();
                const newStyles = cleanStyles ? `font-size: 14px; ${cleanStyles}` : 'font-size: 14px;';
                return `style="${newStyles}"`;
            });

            // Add font-size to elements that don't have style attribute
            pastedData = pastedData.replace(/<(p|div|span|h[1-6]|li)([^>]*?)>/gi, function (match, tag, attrs) {
                if (!attrs.includes('style=')) {
                    return `<${tag}${attrs} style="font-size: 14px;">`;
                }
                return match;
            });

            // If no HTML tags, wrap in paragraph with 14px font
            if (!/<[^>]+>/.test(pastedData)) {
                pastedData = `<p style="font-size: 14px;">${pastedData}</p>`;
            }

            // Insert the cleaned content
            core.insertHTML(pastedData);
        }

        return false; // Prevent default paste
    }
};

// Configuration for SunEditor for Specifications (Clean, Minimal, Reduced Height)
const specificationsEditorConfig = {
    allowedClassNames: [
        'bg-blue-900',
        'text-white',
        'font-bold',
        'p-2',
        'mb-4',
        'list-disc',
        'list-inside',
        'text-sm',
        'space-y-2',
        'text-gray-700'
    ].join('|'),
    attributesWhitelist: {
        all: 'style,class'
    },
    minWidth: '100%',
    minHeight: '80px', // Reduced height (approx 1-2 rows)
    plugins: plugins,
    buttonList: [
        ['undo', 'redo'],
        ['align', 'horizontalRule', 'list', 'lineHeight'],
        ['bold', 'underline', 'italic'],
        ['fontColor', 'hiliteColor'],
        ['removeFormat'],
        ['link'],
        ['preview']
    ],
    fontSize: [8, 9, 10, 11, 12, 14, 16, 18, 20], // Limit font sizes to max 20px
    defaultStyle: 'font-size: 14px;', // Default font size for pasted content
    pasteTagsWhitelist: 'p|div|br|span|strong|b|em|i|u|s|a|ul|ol|li',
    onPaste: function (e, cleanData, maxCharCount, core) {
        // Prevent default paste behavior
        e.preventDefault();

        // Get clipboard data
        const clipboardData = e.clipboardData || window.clipboardData;
        let pastedData = clipboardData.getData('text/html') || clipboardData.getData('text/plain');

        // Clean and normalize the pasted content
        if (pastedData) {
            // Remove all existing font-size styles and replace with 14px
            pastedData = pastedData.replace(/font-size:\s*[^;]+;?/gi, '');
            pastedData = pastedData.replace(/style\s*=\s*["']([^"']*?)["']/gi, function (match, styles) {
                const cleanStyles = styles.replace(/font-size:\s*[^;]+;?/gi, '').trim();
                const newStyles = cleanStyles ? `font-size: 14px; ${cleanStyles}` : 'font-size: 14px;';
                return `style="${newStyles}"`;
            });

            // Add font-size to elements that don't have style attribute
            pastedData = pastedData.replace(/<(p|div|span|h[1-6]|li)([^>]*?)>/gi, function (match, tag, attrs) {
                if (!attrs.includes('style=')) {
                    return `<${tag}${attrs} style="font-size: 14px;">`;
                }
                return match;
            });

            // If no HTML tags, wrap in paragraph with 14px font
            if (!/<[^>]+>/.test(pastedData)) {
                pastedData = `<p style="font-size: 14px;">${pastedData}</p>`;
            }

            // Insert the cleaned content
            core.insertHTML(pastedData);
        }

        return false; // Prevent default paste
    }
};

// Function to initialize editors for textareas
function initializeEditors() {
    // Find all textareas that start with 'text-area'
    const textareas = document.querySelectorAll('textarea[id^="text-area"]');

    textareas.forEach(textarea => {
        if (!textarea.dataset.suneditorInitialized) {
            try {
                let config = fullEditorConfig;

                // Check if this is a terms and conditions textarea (quotations)
                if (textarea.name && textarea.name.includes('terms_conditions')) {
                    config = minimalEditorConfig;
                }
                // Check if this is a specifications textarea
                else if (textarea.name && textarea.name.includes('specifications')) {
                    config = specificationsEditorConfig;
                }

                const editor = suneditor.create(textarea, config);

                textAreaEditors.push({
                    id: textarea.id,
                    editor: editor,
                    element: textarea
                });
                textarea.dataset.suneditorInitialized = 'true';
            } catch (error) {
                console.warn('Failed to initialize SunEditor for:', textarea.id, error);
            }
        }
    });
}

// Initialize editors on DOM ready
document.addEventListener('DOMContentLoaded', initializeEditors);

// Also initialize when new content is added (for dynamic content)
const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) { // Element node
                    const textareas = node.querySelectorAll ? node.querySelectorAll('textarea[id^="text-area"]') : [];
                    if (textareas.length > 0) {
                        setTimeout(initializeEditors, 100); // Small delay to ensure DOM is ready
                    }
                }
            });
        }
    });
});

// Start observing
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Function to set editor content by ID
function setEditorContent(textareaId, content) {
    const editorObj = textAreaEditors.find(e => e.id === textareaId);
    if (editorObj) {
        editorObj.editor.setContents(content);
        return true;
    }
    return false;
}

// Function to get editor content by ID
function getEditorContent(textareaId) {
    const editorObj = textAreaEditors.find(e => e.id === textareaId);
    return editorObj ? editorObj.editor.getContents() : '';
}

// Function to get all editors content
function getAllEditorsContent() {
    const content = {};
    textAreaEditors.forEach(editorObj => {
        content[editorObj.id] = editorObj.editor.getContents();
    });
    return content;
}

// Function to check if editor content is empty
function isEditorEmpty(textareaId) {
    const editorObj = textAreaEditors.find(e => e.id === textareaId);
    if (editorObj) {
        // Get text content without HTML tags
        const text = editorObj.editor.getText().trim();
        // Get raw HTML content
        const content = editorObj.editor.getContents();
        // Check for media elements (images, videos, etc) or tables
        const hasMedia = /<img|<iframe|<video|<audio|<table/i.test(content);

        // It's empty if text is empty AND no media/tables present
        return text.length === 0 && !hasMedia;
    }
    return true;
}

// Function to save editor content to textarea
function saveEditorContent(textareaId) {
    const editorObj = textAreaEditors.find(e => e.id === textareaId);
    if (editorObj) {
        editorObj.editor.save();
    }
}

// Export functions for external use
window.sunEditorUtils = {
    getEditorContent,
    getAllEditorsContent,
    setEditorContent,
    initializeEditors,
    isEditorEmpty,
    saveEditorContent
};

export default textAreaEditors;
