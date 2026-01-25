import { Datepicker } from "flowbite";

function formatDateToDDMMYYYY(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}



const $datepickerEl = document.getElementById('expense-datepicker');
const today = new Date();

const formattedDateToday = [
    String(today.getDate()).padStart(2, '0'),
    String(today.getMonth() + 1).padStart(2, '0'),
    today.getFullYear()
].join('/');

const weekBefore = new Date(today);
weekBefore.setDate(today.getDate() - 30);

const formattedDateWeekBefore = [
    String(weekBefore.getDate()).padStart(2, '0'),
    String(weekBefore.getMonth() + 1).padStart(2, '0'),
    weekBefore.getFullYear()
].join('/');

if ($datepickerEl && $datepickerEl.value) {
 $datepickerEl.value = formatDateToDDMMYYYY($datepickerEl.value);
}

if ($datepickerEl && !$datepickerEl.value) {
    $datepickerEl.value = formattedDateToday;
}


// optional options with default values and callback functions
const expenseOptions = {
    autohide: true,
    format: 'dd/mm/yyyy',
    maxDate: formattedDateToday,
    minDate: formattedDateWeekBefore,
    orientation: 'bottom',
    title: null,
    rangePicker: false,
    onShow: () => {},
    onHide: () => {},
};

const instanceOptions = {
  id: 'datepicker-custom-example',
  override: true
};


if ($datepickerEl) {
    const expenseDatepicker = new Datepicker($datepickerEl, expenseOptions);
}
