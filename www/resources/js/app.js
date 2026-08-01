import './bootstrap';
import jQuery from 'jquery';
import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.full.min.js';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.$ = window.jQuery = jQuery;

const currencyMaskedInputs = document.querySelectorAll('input[data-currency-mask="brl"]');

const formatCurrencyDigitsToBrl = (digits) => {
	const normalized = (digits || '0').replace(/^0+(?=\d)/, '');
	const cents = normalized === '' ? 0 : Number.parseInt(normalized, 10);
	const value = cents / 100;

	return value.toLocaleString('pt-BR', {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2,
	});
};

for (const input of currencyMaskedInputs) {
	const initialDigits = (input.value || '').replace(/\D/g, '');
	input.value = formatCurrencyDigitsToBrl(initialDigits);

	input.addEventListener('input', () => {
		const digits = input.value.replace(/\D/g, '');
		input.value = formatCurrencyDigitsToBrl(digits);
	});

	input.addEventListener('blur', () => {
		const digits = input.value.replace(/\D/g, '');
		input.value = formatCurrencyDigitsToBrl(digits);
	});
}

const uiSelects = document.querySelectorAll('select[data-ui-select2="true"]');

for (const element of uiSelects) {
	const select = window.jQuery(element);

	if (select.hasClass('select2-hidden-accessible')) {
		continue;
	}

	const minForSearch = element.dataset.search === 'off' ? Number.POSITIVE_INFINITY : 8;
	const dropdownParentSelector = element.dataset.dropdownParent;
	const dropdownParent = dropdownParentSelector ? document.querySelector(dropdownParentSelector) : null;
	const placeholder = element.dataset.placeholder;

	select.select2({
		width: '100%',
		minimumResultsForSearch: minForSearch,
		placeholder,
		allowClear: element.dataset.allowClear === 'true',
		dropdownParent: dropdownParent ? window.jQuery(dropdownParent) : undefined,
	});
}

const sidebarShell = document.querySelector('[data-admin-sidebar-shell]');
const sidebar = document.querySelector('[data-admin-sidebar]');
const sidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');

if (sidebarShell && sidebar && sidebarToggle) {
	const storageKey = 'global-admin.sidebar-collapsed';
	const sidebarTopbar = document.querySelector('[data-admin-sidebar-topbar]');
	const contentElements = document.querySelectorAll('[data-admin-sidebar-content]');
	const labelElements = document.querySelectorAll('[data-admin-sidebar-label]');
	const linkElements = document.querySelectorAll('[data-admin-sidebar-link]');
	const logoutButton = document.querySelector('[data-admin-sidebar-logout]');
	const toggleIcon = document.querySelector('[data-admin-sidebar-toggle-icon]');

	const applySidebarState = (collapsed) => {
		sidebarShell.classList.toggle('md:grid-cols-[280px_1fr]', !collapsed);
		sidebarShell.classList.toggle('md:grid-cols-[56px_1fr]', collapsed);
		sidebar.classList.toggle('p-5', !collapsed);
		sidebar.classList.toggle('p-0', collapsed);

		if (sidebarTopbar) {
			sidebarTopbar.classList.toggle('items-start', !collapsed);
			sidebarTopbar.classList.toggle('items-center', collapsed);
			sidebarTopbar.classList.toggle('justify-between', !collapsed);
			sidebarTopbar.classList.toggle('justify-center', collapsed);
			sidebarTopbar.classList.toggle('h-full', collapsed);
			sidebarTopbar.classList.toggle('w-full', collapsed);
		}

		for (const element of contentElements) {
			element.classList.toggle('hidden', collapsed);
		}

		for (const label of labelElements) {
			label.classList.toggle('hidden', collapsed);
		}

		for (const link of linkElements) {
			link.classList.toggle('justify-center', collapsed);
			link.classList.toggle('px-3', collapsed);
			link.classList.toggle('px-4', !collapsed);
		}

		if (logoutButton) {
			logoutButton.classList.toggle('justify-center', collapsed);
			logoutButton.classList.toggle('justify-start', !collapsed);
		}

		sidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
		sidebarToggle.setAttribute(
			'aria-label',
			collapsed ? (sidebarToggle.dataset.expandLabel ?? 'Expand sidebar') : (sidebarToggle.dataset.collapseLabel ?? 'Collapse sidebar'),
		);

		if (toggleIcon) {
			toggleIcon.textContent = collapsed ? '→' : '←';
		}
	};

	const initialCollapsed = window.localStorage.getItem(storageKey) === '1';
	applySidebarState(initialCollapsed);

	sidebarToggle.addEventListener('click', () => {
		const collapsed = !sidebarShell.classList.contains('md:grid-cols-[56px_1fr]');
		applySidebarState(collapsed);
		window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
	});
}

const adminDeleteForms = document.querySelectorAll('form[data-admin-delete-confirm]');

for (const form of adminDeleteForms) {
	form.addEventListener('submit', async (event) => {
		if (form.dataset.confirmed === 'true') {
			return;
		}

		event.preventDefault();

		const adminName = form.dataset.adminName ?? '';
		const title = form.dataset.confirmTitle ?? 'Confirm deletion';
		const bodyTemplate = form.dataset.confirmText ?? 'You are about to delete :name.';
		const bodyText = bodyTemplate.replace(':name', adminName);
		const confirmLabel = form.dataset.confirmConfirm ?? 'Delete';
		const cancelLabel = form.dataset.confirmCancel ?? 'Cancel';

		const result = await Swal.fire({
			title,
			text: bodyText,
			icon: 'warning',
			showCancelButton: true,
			focusCancel: true,
			confirmButtonText: confirmLabel,
			cancelButtonText: cancelLabel,
			customClass: {
				popup: 'g-swal-popup',
				title: 'g-swal-title',
				htmlContainer: 'g-swal-body',
				actions: 'g-swal-actions',
				confirmButton: 'g-swal-confirm',
				cancelButton: 'g-swal-cancel',
			},
			buttonsStyling: false,
		});

		if (result.isConfirmed) {
			form.dataset.confirmed = 'true';
			HTMLFormElement.prototype.submit.call(form);
		}
	});
}
