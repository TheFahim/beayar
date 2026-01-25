import { Datepicker } from "flowbite";

/**
 * A helper function to format a date string or Date object to 'dd/mm/yyyy'.
 * @param {string | Date} dateString - The date to format.
 * @returns {string} The formatted date.
 */
function formatDateToDDMMYYYY(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// --- Start of Changes ---

// 1. Select all elements with the class 'flowbite-datepicker'
// This returns a NodeList, which is like an array of elements.
const datepickerElements = document.querySelectorAll('.flowbite-datepicker');

// Define the options that will be used for all datepicker instances
const options = {
    autohide: true,
    format: 'dd/mm/yyyy',
    orientation: 'bottom',
    title: null,
    rangePicker: false,
    autoSelectToday: false,
};

// 2. Loop through each selected element and initialize a Datepicker on it
datepickerElements.forEach($datepickerEl => {
    // This code block runs for each individual element found

    // If the input has a pre-existing value (e.g., from a server),
    // make sure it's formatted correctly on page load.
    if ($datepickerEl.value) {
        $datepickerEl.value = formatDateToDDMMYYYY($datepickerEl.value);
    }

    // Optional: If you want to set a default value for empty fields,
    // you can uncomment the block below.
    /*
    const today = new Date();
    const formattedDateToday = formatDateToDDMMYYYY(today);
    if (!$datepickerEl.value) {
        $datepickerEl.value = formattedDateToday;
    }
    */

    // 3. Initialize a new Datepicker instance for the current element
    // passing the element and the shared options.
    new Datepicker($datepickerEl, options);
});
