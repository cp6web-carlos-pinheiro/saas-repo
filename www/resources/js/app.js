import './bootstrap';
import jQuery from 'jquery';
import 'select2/dist/css/select2.min.css';
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

const durationMaskedInputs = document.querySelectorAll('input[data-duration-mask="true"]');

const formatMinutesToDuration = (minutes) => {
	const totalMinutes = Math.max(0, Math.round(Number(minutes) || 0));
	const hours = Math.floor(totalMinutes / 60).toString().padStart(2, '0');
	const remainingMinutes = (totalMinutes % 60).toString().padStart(2, '0');

	return `${hours}:${remainingMinutes}`;
};

const normalizeDuration = (value) => {
	const trimmed = String(value ?? '').trim();
	const match = trimmed.match(/^(\d+):(\d{1,2})$/);

	if (match) {
		return formatMinutesToDuration((Number(match[1]) * 60) + Number(match[2]));
	}

	return formatMinutesToDuration(Number(trimmed.replace(',', '.')));
};

const maskDurationDigits = (value) => {
	const digits = String(value ?? '').replace(/\D/g, '');

	if (digits === '') {
		return '';
	}

	const padded = digits.padStart(4, '0');
	const hours = padded.slice(0, -2).replace(/^0+(?=\d{2})/, '');
	const minutes = padded.slice(-2);

	return `${hours}:${minutes}`;
};

for (const input of durationMaskedInputs) {
	input.value = normalizeDuration(input.value);

	input.addEventListener('input', () => {
		input.value = maskDurationDigits(input.value);
	});

	input.addEventListener('blur', () => {
		input.value = normalizeDuration(input.value);
	});

	input.form?.addEventListener('submit', () => {
		const normalized = normalizeDuration(input.value);
		const [hours, minutes] = normalized.split(':').map(Number);
		input.value = String((hours * 60) + minutes);
	});
}

const taxIdMaskedInputs = document.querySelectorAll('input[data-tax-id-mask="true"]');

const maskCpf = (digits) => {
	const value = digits.slice(0, 11);

	return value
		.replace(/(\d{3})(\d)/, '$1.$2')
		.replace(/(\d{3})(\d)/, '$1.$2')
		.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
};

const maskCnpj = (digits) => {
	const value = digits.slice(0, 14);

	return value
		.replace(/^(\d{2})(\d)/, '$1.$2')
		.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
		.replace(/\.(\d{3})(\d)/, '.$1/$2')
		.replace(/(\d{4})(\d)/, '$1-$2');
};

const applyTaxIdMask = (input) => {
	const digits = input.value.replace(/\D/g, '');
	const personTypeSourceId = input.dataset.personTypeSource;
	const personTypeElement = personTypeSourceId ? document.getElementById(personTypeSourceId) : null;
	const personType = personTypeElement instanceof HTMLSelectElement ? personTypeElement.value : 'PJ';

	input.value = personType === 'PF' ? maskCpf(digits) : maskCnpj(digits);
};

for (const input of taxIdMaskedInputs) {
	applyTaxIdMask(input);

	input.addEventListener('input', () => {
		applyTaxIdMask(input);
	});

	const personTypeSourceId = input.dataset.personTypeSource;
	const personTypeElement = personTypeSourceId ? document.getElementById(personTypeSourceId) : null;

	if (personTypeElement instanceof HTMLSelectElement) {
		personTypeElement.addEventListener('change', () => {
			applyTaxIdMask(input);
		});
	}
}

const uiTranslations = window.uiTranslations ?? {};

const select2Language = {
	errorLoading: () => uiTranslations.selectErrorLoading ?? '',
	inputTooShort: (args) => {
		const remaining = Math.max(0, (args.minimum ?? 1) - (args.input?.length ?? 0));

		return remaining === 1
			? uiTranslations.selectInputTooShortOne
			: String(uiTranslations.selectInputTooShortMany ?? '').replace(':count', remaining);
	},
	loadingMore: () => uiTranslations.selectLoadingMore ?? '',
	noResults: () => uiTranslations.selectNoResults ?? '',
	searching: () => uiTranslations.selectSearching ?? '',
};

const initializeClientSideTableSorting = () => {
	const locale = document.documentElement.lang || undefined;
	const collator = new Intl.Collator(locale, { numeric: true, sensitivity: 'base' });

	const comparableValue = (text) => {
		const value = text.trim();
		const dateMatch = value.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);

		if (dateMatch) {
			return Number(`${dateMatch[3]}${dateMatch[2]}${dateMatch[1]}${dateMatch[4] ?? '00'}${dateMatch[5] ?? '00'}`);
		}

		const durationMatch = value.match(/^(\d+):(\d{2})$/);

		if (durationMatch) {
			return (Number(durationMatch[1]) * 60) + Number(durationMatch[2]);
		}

		const numericText = value
			.replace(/R\$\s*/g, '')
			.replace(/\s*%$/, '')
			.replace(/\./g, '')
			.replace(',', '.');

		if (/^-?\d+(?:\.\d+)?$/.test(numericText)) {
			return Number(numericText);
		}

		return value;
	};

	for (const table of document.querySelectorAll('table')) {
		const headerRow = table.tHead?.rows[table.tHead.rows.length - 1];
		const body = table.tBodies[0];

		if (!headerRow || !body) {
			continue;
		}

		const dataRows = Array.from(body.rows).filter((row) => row.cells.length === headerRow.cells.length && !row.querySelector('td[colspan]'));

		if (dataRows.length < 2) {
			continue;
		}

		Array.from(headerRow.cells).forEach((header, columnIndex) => {
			const label = header.textContent.trim();
			const sampleCell = dataRows[0]?.cells[columnIndex];

			if (label === '' || header.querySelector('a, button') || sampleCell?.querySelector('a, button, form')) {
				return;
			}

			const icon = document.createElement('span');
			icon.className = 'ml-1';
			icon.setAttribute('aria-hidden', 'true');
			icon.textContent = '↕';
			header.append(icon);
			header.classList.add('cursor-pointer', 'select-none', 'hover:text-[#1a73e8]');
			header.setAttribute('role', 'button');
			header.setAttribute('tabindex', '0');
			header.setAttribute('aria-sort', 'none');
			header.setAttribute('title', String(uiTranslations.sortBy ?? '').replace(':column', label));

			const sortRows = () => {
				const ascending = header.getAttribute('aria-sort') !== 'ascending';

				for (const otherHeader of headerRow.cells) {
					if (otherHeader !== header && otherHeader.dataset.clientSortable === 'true') {
						otherHeader.setAttribute('aria-sort', 'none');
						otherHeader.querySelector('[data-sort-icon]')?.replaceChildren('↕');
					}
				}

				const rows = Array.from(body.rows).filter((row) => row.cells.length === headerRow.cells.length && !row.querySelector('td[colspan]'));
				rows.sort((left, right) => {
					const leftValue = comparableValue(left.cells[columnIndex]?.textContent ?? '');
					const rightValue = comparableValue(right.cells[columnIndex]?.textContent ?? '');
					const comparison = typeof leftValue === 'number' && typeof rightValue === 'number'
						? leftValue - rightValue
						: collator.compare(String(leftValue), String(rightValue));

					return ascending ? comparison : -comparison;
				});

				for (const row of rows) {
					body.append(row);
				}

				header.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
				header.setAttribute('aria-label', `${label}. ${ascending ? uiTranslations.sortedAscending : uiTranslations.sortedDescending}`);
				icon.textContent = ascending ? '↑' : '↓';
			};

			header.dataset.clientSortable = 'true';
			icon.dataset.sortIcon = 'true';
			header.addEventListener('click', sortRows);
			header.addEventListener('keydown', (event) => {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					sortRows();
				}
			});
		});
	}
};

initializeClientSideTableSorting();

const initializeUiSelects = () => {
	if (typeof window.jQuery?.fn?.select2 !== 'function') {
		return 0;
	}

	const uiSelects = document.querySelectorAll('select[data-ui-select2="true"]');
	let initializedCount = 0;

	for (const element of uiSelects) {
		const select = window.jQuery(element);

		if (select.hasClass('select2-hidden-accessible')) {
			continue;
		}

		const searchMode = element.dataset.search;
		let minForSearch = 8;

		if (searchMode === 'off') {
			minForSearch = Number.POSITIVE_INFINITY;
		} else if (searchMode === 'on') {
			minForSearch = 0;
		}
		const dropdownParentSelector = element.dataset.dropdownParent;
		const dropdownParent = dropdownParentSelector ? document.querySelector(dropdownParentSelector) : null;
		const placeholder = element.dataset.placeholder;
		const ajaxUrl = element.dataset.ajaxUrl;
		const minimumInputLength = Number.parseInt(element.dataset.minimumInputLength ?? '', 10);
		const select2Options = {
			width: '100%',
			minimumResultsForSearch: minForSearch,
			placeholder,
			language: select2Language,
			allowClear: element.dataset.allowClear === 'true',
			dropdownParent: dropdownParent ? window.jQuery(dropdownParent) : undefined,
		};

		if (ajaxUrl) {
			select2Options.minimumInputLength = Number.isNaN(minimumInputLength) ? 1 : minimumInputLength;
			select2Options.ajax = {
				url: ajaxUrl,
				dataType: 'json',
				delay: 250,
				data: (params) => ({
					q: params.term ?? '',
					page: params.page ?? 1,
				}),
				processResults: (data) => {
					return {
						results: Array.isArray(data?.results) ? data.results : [],
						pagination: {
							more: Boolean(data?.pagination?.more),
						},
					};
				},
				cache: true,
			};
		}

		select.select2(select2Options);
		initializedCount += 1;
	}

	return initializedCount;
};

window.initializeUiSelects = initializeUiSelects;

const observeDynamicUiSelects = () => {
	const root = document.body;

	if (!root || typeof window.MutationObserver !== 'function') {
		return;
	}

	const observer = new MutationObserver((mutations) => {
		for (const mutation of mutations) {
			for (const node of mutation.addedNodes) {
				if (!(node instanceof HTMLElement)) {
					continue;
				}

				if (node.matches('select[data-ui-select2="true"]') || node.querySelector('select[data-ui-select2="true"]')) {
					initializeUiSelects();
					return;
				}
			}
		}
	});

	observer.observe(root, { childList: true, subtree: true });
};

const patchSelect2AjaxNormalizeForStrictMode = () => {
	const select2Amd = window.jQuery?.fn?.select2?.amd;

	if (!select2Amd || typeof select2Amd.require !== 'function') {
		return;
	}

	const SelectDataAdapter = select2Amd.require('select2/data/select');

	if (!SelectDataAdapter || SelectDataAdapter.__beyondNormalizePatched === true) {
		return;
	}

	SelectDataAdapter.prototype._normalizeItem = function (item) {
		let normalizedItem = item;

		if (normalizedItem !== Object(normalizedItem)) {
			normalizedItem = { id: normalizedItem, text: normalizedItem };
		}

		normalizedItem = window.jQuery.extend({}, { text: '' }, normalizedItem);

		if (normalizedItem.id != null) {
			normalizedItem.id = normalizedItem.id.toString();
		}

		if (normalizedItem.text != null) {
			normalizedItem.text = normalizedItem.text.toString();
		}

		const hasContext = this !== undefined && this !== null;
		const container = hasContext ? this.container : null;

		if (normalizedItem._resultId == null && normalizedItem.id && container != null && typeof this.generateResultId === 'function') {
			normalizedItem._resultId = this.generateResultId(container, normalizedItem);
		}

		if (Array.isArray(normalizedItem.children)) {
			normalizedItem.children = normalizedItem.children.map((child) => SelectDataAdapter.prototype._normalizeItem.call(this, child));
		}

		return window.jQuery.extend({}, { selected: false, disabled: false }, normalizedItem);
	};

	SelectDataAdapter.__beyondNormalizePatched = true;
};

try {
	const select2Module = await import('select2/dist/js/select2.full.min.js');

	if (typeof window.jQuery?.fn?.select2 !== 'function') {
		const select2Factory = select2Module?.default;

		if (typeof select2Factory === 'function') {
			select2Factory(window, window.jQuery);
		}
	}

	if (typeof window.jQuery?.fn?.select2 !== 'function') {
		throw new Error('Select2 plugin did not attach to jQuery.');
	}

	patchSelect2AjaxNormalizeForStrictMode();

	const initializedSelects = initializeUiSelects();
	console.info(`[UI] Select2 loaded successfully. Initialized ${initializedSelects} select(s).`);
	observeDynamicUiSelects();
} catch (error) {
	console.warn('[UI] Select2 failed to load. Native selects will be used.', error);
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

const adminReverseForms = document.querySelectorAll('form[data-admin-reverse-confirm]');

for (const form of adminReverseForms) {
	form.addEventListener('submit', async (event) => {
		if (form.dataset.confirmed === 'true') {
			return;
		}

		event.preventDefault();

		const title = form.dataset.reverseTitle ?? 'Confirm reversal';
		const bodyText = form.dataset.reverseText ?? 'This operation will reverse posted effects.';
		const confirmLabel = form.dataset.reverseConfirm ?? 'Reverse';
		const cancelLabel = form.dataset.reverseCancel ?? 'Cancel';
		const reasonLabel = form.dataset.reverseReasonLabel ?? 'Reason';
		const reasonPlaceholder = form.dataset.reverseReasonPlaceholder ?? 'Describe why this reversal is needed';
		const requiredMessage = form.dataset.reverseReasonRequired ?? 'Reason is required.';
		const categoryLabel = form.dataset.reverseCategoryLabel ?? 'Category';
		const categoryRequiredMessage = form.dataset.reverseCategoryRequired ?? 'Category is required.';
		const categoryQuality = form.dataset.reverseCategoryQuality ?? 'Quality';
		const categoryFiscal = form.dataset.reverseCategoryFiscal ?? 'Fiscal';
		const categorySupplier = form.dataset.reverseCategorySupplier ?? 'Supplier';
		const categoryMasterData = form.dataset.reverseCategoryMasterData ?? 'Master Data';

		const result = await Swal.fire({
			title,
			html: `
				<p class="mb-3">${bodyText}</p>
				<label for="swal-reverse-category" class="mb-1 block text-left text-sm font-medium">${categoryLabel}</label>
				<select id="swal-reverse-category" class="swal2-select" style="display:block;width:100%;margin:0 0 12px 0;">
					<option value="">---</option>
					<option value="quality">${categoryQuality}</option>
					<option value="fiscal">${categoryFiscal}</option>
					<option value="supplier">${categorySupplier}</option>
					<option value="master_data">${categoryMasterData}</option>
				</select>
				<label for="swal-reverse-reason" class="mb-1 block text-left text-sm font-medium">${reasonLabel}</label>
				<textarea id="swal-reverse-reason" class="swal2-textarea" placeholder="${reasonPlaceholder}" maxlength="1000" style="display:block;width:100%;margin:0;"> </textarea>
			`,
			icon: 'warning',
			preConfirm: () => {
				const categoryElement = document.getElementById('swal-reverse-category');
				const reasonElement = document.getElementById('swal-reverse-reason');

				const category = categoryElement instanceof HTMLSelectElement ? categoryElement.value.trim() : '';
				const reason = reasonElement instanceof HTMLTextAreaElement ? reasonElement.value.trim() : '';

				if (category === '') {
					Swal.showValidationMessage(categoryRequiredMessage);
					return false;
				}

				if (reason === '') {
					Swal.showValidationMessage(requiredMessage);
					return false;
				}

				return { category, reason };
			},
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
			let hiddenReason = form.querySelector('input[name="reverse_reason"]');
			let hiddenCategory = form.querySelector('input[name="reverse_category"]');

			if (!(hiddenReason instanceof HTMLInputElement)) {
				hiddenReason = document.createElement('input');
				hiddenReason.type = 'hidden';
				hiddenReason.name = 'reverse_reason';
				form.appendChild(hiddenReason);
			}

			if (!(hiddenCategory instanceof HTMLInputElement)) {
				hiddenCategory = document.createElement('input');
				hiddenCategory.type = 'hidden';
				hiddenCategory.name = 'reverse_category';
				form.appendChild(hiddenCategory);
			}

			hiddenCategory.value = String(result.value?.category ?? '').trim();
			hiddenReason.value = String(result.value?.reason ?? '').trim();
			form.dataset.confirmed = 'true';
			HTMLFormElement.prototype.submit.call(form);
		}
	});
}
