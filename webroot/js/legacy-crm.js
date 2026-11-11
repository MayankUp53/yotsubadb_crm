/* Original CRM form behavior, initialized after the shared jQuery assets. */
(function ($) {
    'use strict';
    window.numOnly = function (event) {
        return !event || event.ctrlKey || event.metaKey || /^[0-9]$/.test(event.key)
            || ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key);
    };
    $(function () {
        if ($.fn.datepicker) $('.legacy-crm-page .datepicker').each(function () {
            $(this).datepicker({ dateFormat: this.name === 'date_want' ? 'yy-mm-dd' : 'yy/mm/dd' });
        });
        if ($.fn.datetimepicker) {
            $.datetimepicker.setLocale('ja');
            $('.legacy-crm-page .datetimepicker').each(function () {
                this.value = this.value.replace(/-/g, '/');
                $(this).datetimepicker({ step: 15, format: 'Y/m/d H:i' });
            });
        }
        if ($.fn.timepicker) $('.legacy-crm-page .timepicker_from, .legacy-crm-page .timepicker_to').timepicker({ step: 30, timeFormat: 'H:i' });
        $('.legacy-crm-page form').on('keydown', 'input:not([type=submit])', function (event) {
            if (event.key === 'Enter') event.preventDefault();
        });
        $('.legacy-crm-page').on('input change', '.age, .dob', function () {
            const form = this.form;
            const year = new Date().getFullYear();
            const other = form.querySelector(this.classList.contains('age') ? '.dob' : '.age');
            if (this.classList.contains('dob') && this.value.length < 4) { if (other) other.value = ''; return; }
            if (other) other.value = this.value !== '' && Number(this.value) >= 0 && Number(this.value) <= year ? year - Number(this.value) : '';
        });
        $('.legacy-crm-page').on('change', 'input[name="day_service_days[]"]', function () {
            $(this.form).find('.day_service_times').val($(this.form).find('input[name="day_service_days[]"]:checked').length);
        });
        $('.legacy-crm-page').on('input change', '[class*=product_name], [class*=quantity], [class*=price]', function () {
            const match = this.name.match(/^(product_name|quantity|unit_price|price)([1-5])$/);
            if (!match || !this.form) return;
            const form = this.form;
            const index = match[2];
            const field = key => form.querySelector('[name="' + key + '"]');
            if (match[1] === 'product_name') {
                const price = document.querySelector('input[type="hidden"][name="' + this.value + '"]');
                if (field('unit_price' + index)) field('unit_price' + index).value = price ? price.value : '';
            }
            if (match[1] !== 'price' && field('price' + index)) {
                field('price' + index).value = Number(field('quantity' + index)?.value || 0) * Number(field('unit_price' + index)?.value || 0);
            }
            if (field('total_price')) field('total_price').value = [1, 2, 3, 4, 5].reduce((total, i) => total + Number(field('price' + i)?.value || 0), 0);
        });
    });
})(jQuery);
