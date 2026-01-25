import Swal from 'sweetalert2';

document.addEventListener('alpine:init', () => {
    Alpine.data('dragableImage', () => ({
        id: 'dragable',
        files: [],
        isDragging: false,
        inputElement: null,

        init() {
            this.id = this.$el.getAttribute('data-id') || this.id;
            this.inputElement = document.querySelector(`#${this.id}`);

            document.addEventListener('paste', (event) => {
                if (event.clipboardData && event.clipboardData.files.length > 0) {
                    this.addFiles(event.clipboardData.files);
                }
            });

            const oldFilePath = this.inputElement.getAttribute('data-old');


            if (oldFilePath) {
                let oldFileArray;

                try {
                    const decodedFilePath = oldFilePath
                        .replace(/&quot;/g, '"') // Decode double quotes
                        .replace(/&amp;/g, '&'); // Decode ampersands

                    // Parse the value into an array if it's a valid JSON string
                    oldFileArray = JSON.parse(decodedFilePath);


                    // Ensure it's an array
                    if (!Array.isArray(oldFileArray)) {
                        oldFileArray = [oldFileArray]; // Convert single value to an array
                    }
                } catch (error) {
                    // If parsing fails, treat it as a single string and wrap it in an array
                    oldFileArray = [oldFilePath];
                }


                // Process the array
                oldFileArray.forEach((path) => {
                    this.fetchFileFromPath(path);
                });
            }

        },

        async fetchFileFromPath(path) {
            try {
                const response = await fetch(path);
                const blob = await response.blob();
                const fileName = path.split('/').pop(); // Extract file name from the path
                const file = new File([blob], fileName, { type: blob.type });
                this.files.push(file);

                // Update the native input element with the fetched file
                this.updateNativeInput();
            } catch (error) {
                console.error('Error fetching file:', error);
            }
        },


        handleDrop(event) {
            this.isDragging = false;
            this.addFiles(event.dataTransfer.files);
        },

        handleFileSelect(event) {
            this.addFiles(event.target.files);
        },

        addFiles(newFiles) {
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg', 'application/zip'];
            const filesToAdd = Array.from(newFiles).filter(file => allowedTypes.includes(file.type));



            if (filesToAdd.length === 0) {
                // If no valid files are selected, do not remove old files
                this.updateNativeInput();
                return;
            }

            if (!this.inputElement.hasAttribute('multiple')) {
                this.files = [...filesToAdd];
            } else {
                this.files = [...this.files, ...filesToAdd];
            }

            this.updateNativeInput();
        },

        removeFile(index, event) {
            event.preventDefault();
            this.files = this.files.filter((_, i) => i !== index);
            this.updateNativeInput();
        },

        updateNativeInput() {
            // Create a new DataTransfer object
            const dataTransfer = new DataTransfer();

            // Add all current files to it
            this.files.forEach(file => {
                dataTransfer.items.add(file);
            });

            // Set the new FileList to the input
            this.inputElement.files = dataTransfer.files;

        },

        fileAddNotAllowed(msg) {

            Swal.fire({
                title: msg,
                icon: 'info',
                width: '40em',
                background: 'rgb(55 65 81)',
                customClass: {
                    title: 'text-white',
                },
                showConfirmButton: false,
                timer: 1500
            })
        },

        clearState() {
            this.files = [];
            this.isDragging = false;
            if (this.inputElement) {
                this.inputElement.value = '';
                try {
                    const dataTransfer = new DataTransfer();
                    this.inputElement.files = dataTransfer.files;
                } catch (e) { }
            }
        }
    }));
});
