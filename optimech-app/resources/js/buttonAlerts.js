import Swal from 'sweetalert2';

import  textAreaEditors  from './sunEditor';

document.addEventListener('DOMContentLoaded', function() {
    // Handle delete buttons
    document.addEventListener('click', function(event) {
        if (event.target.matches('.delete-button') || event.target.closest('.delete-button')) {
            event.preventDefault();
            
            const button = event.target.matches('.delete-button') ? event.target : event.target.closest('.delete-button');
            
            // Check if button has a form attribute, otherwise use closest form
            let form;
            if (button.hasAttribute('form')) {
                form = document.getElementById(button.getAttribute('form'));
            } else {
                form = button.closest('form');
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                background: 'rgb(55 65 81)',
                customClass: {
                    title: 'text-white',
                    htmlContainer: '!text-white',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    // Handle save buttons - sync all SunEditor content before form submission
    document.addEventListener('click', function(event) {
        if (event.target.matches('.save-button') || event.target.closest('.save-button')) {
            event.preventDefault();
            
            const button = event.target.matches('.save-button') ? event.target : event.target.closest('.save-button');
            const form = button.closest('form');
            
            // Show SweetAlert confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save this data?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!',
                background: 'rgb(55 65 81)',
                customClass: {
                    title: 'text-white',
                    htmlContainer: '!text-white',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Sync all SunEditor content to their respective textareas
                    if (window.sunEditorUtils) {
                        const allContent = window.sunEditorUtils.getAllEditorsContent();
                        
                        Object.keys(allContent).forEach(textareaId => {
                            const textarea = document.getElementById(textareaId);
                            if (textarea) {
                                textarea.value = allContent[textareaId];
                            }
                        });
                    }
                    
                    // Submit the form after syncing content
                    if (form) {
                        form.submit();
                    }
                }
            });
        }
    });

    // Handle form submissions - ensure all editor content is synced
    document.addEventListener('submit', function(event) {
        const form = event.target;
        
        // Sync all SunEditor content to their respective textareas before submission
        if (window.sunEditorUtils) {
            const allContent = window.sunEditorUtils.getAllEditorsContent();
            
            Object.keys(allContent).forEach(textareaId => {
                const textarea = document.getElementById(textareaId);
                if (textarea) {
                    textarea.value = allContent[textareaId];
                }
            });
        }
    });

    const disableButton = document.getElementById('disable');

    if (disableButton) {
        const dataUrl = disableButton.getAttribute('data-url');

        disableButton.addEventListener('click', function(event){
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to disable the User!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                background: 'rgb(55 65 81)',
                customClass: {
                    title: 'text-white',
                    htmlContainer: '!text-white',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to a certain URL
                    window.location.href = dataUrl;
                }
            });
        });
    }



});
