import './bootstrap';
import jQuery from 'jquery';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import chevronDownIconSvg from '../icons/tabler/chevron-down.svg?raw';
import circleCheckIconSvg from '../icons/tabler/circle-check.svg?raw';
import closeIconSvg from '../icons/tabler/x.svg?raw';

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

// Shared light/dark/system preference control used by public and application layouts.
{
	const storageKey = 'beyond-mrp.theme';
	const media = window.matchMedia('(prefers-color-scheme: dark)');
	const selectors = [...document.querySelectorAll('[data-ui-theme-select]')];
	const options = [...document.querySelectorAll('[data-theme-option]')];

	const applyTheme = (preference, persist = true) => {
		const normalized = ['light', 'dark', 'system'].includes(preference) ? preference : 'system';
		const resolved = normalized === 'system' ? (media.matches ? 'dark' : 'light') : normalized;

		document.documentElement.dataset.theme = resolved;
		document.documentElement.dataset.themePreference = normalized;
		document.documentElement.style.colorScheme = resolved;
		selectors.forEach((selector) => { selector.value = normalized; });
		options.forEach((option) => {
			option.setAttribute('aria-pressed', String(option.dataset.themeOption === normalized));
		});

		if (persist) {
			try { window.localStorage.setItem(storageKey, normalized); } catch (_) {}
		}
	};

	selectors.forEach((selector) => {
		selector.addEventListener('change', () => applyTheme(selector.value));
	});
	options.forEach((option) => {
		option.addEventListener('click', () => applyTheme(option.dataset.themeOption ?? 'system'));
	});

	media.addEventListener('change', () => {
		if (document.documentElement.dataset.themePreference === 'system') applyTheme('system', false);
	});

	applyTheme(document.documentElement.dataset.themePreference ?? 'system', false);
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
	removeItem: () => uiTranslations.selectRemoveItem ?? 'Remover item',
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

		if (dataRows.length < 1) {
			continue;
		}

		Array.from(headerRow.cells).forEach((header, columnIndex) => {
			const label = header.textContent.trim();
			const sampleCell = dataRows[0]?.cells[columnIndex];
			const hasBlockingControl = sampleCell
				? [...sampleCell.querySelectorAll('a, button, form, input, select')]
					.some((control) => !control.matches('.ui-table-cell-action, .ui-table-cell-input'))
				: false;

			if (label === '' || header.querySelector('a, button, input, select') || hasBlockingControl) {
				return;
			}

			const icon = document.createElement('span');
			icon.className = 'ui-table-sort-icon';
			icon.setAttribute('aria-hidden', 'true');
			icon.dataset.sortState = 'none';
			icon.innerHTML = chevronDownIconSvg;
			header.append(icon);
			header.classList.add('ui-table-sortable');
			header.setAttribute('role', 'button');
			header.setAttribute('tabindex', '0');
			header.setAttribute('aria-sort', 'none');
			header.setAttribute('title', String(uiTranslations.sortBy ?? '').replace(':column', label));

			const sortRows = () => {
				const ascending = header.getAttribute('aria-sort') !== 'ascending';

				for (const otherHeader of headerRow.cells) {
					if (otherHeader !== header && otherHeader.dataset.clientSortable === 'true') {
						otherHeader.setAttribute('aria-sort', 'none');
						const otherIcon = otherHeader.querySelector('[data-sort-icon]');
						if (otherIcon instanceof HTMLElement) otherIcon.dataset.sortState = 'none';
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

				table.dispatchEvent(new CustomEvent('ui:table-sorted'));

				header.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
				header.setAttribute('aria-label', `${label}. ${ascending ? uiTranslations.sortedAscending : uiTranslations.sortedDescending}`);
				icon.dataset.sortState = ascending ? 'ascending' : 'descending';
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

		if (element.multiple) {
			select2Options.closeOnSelect = false;
			select2Options.templateResult = (data) => {
				if (!data.id) {
					return data.text;
				}

				const option = document.createElement('span');
				const label = document.createElement('span');
				option.className = 'ui-select2-option';
				label.textContent = data.text;
				option.append(label);

				if (data.element?.selected) {
					const check = document.createElement('span');
					check.className = 'ui-select2-option-check';
					check.setAttribute('aria-hidden', 'true');
					check.innerHTML = circleCheckIconSvg;
					option.append(check);
				}

				return option;
			};
		}

		select.select2(select2Options);

		if (element.multiple) {
			const decorateChoicesAsBadges = () => {
				window.requestAnimationFrame(() => {
					const container = select.next('.select2-container');
					container
						.find('.select2-selection__choice')
						.addClass('ui-badge ui-badge-primary ui-badge-md');

					container.find('.select2-selection__choice__remove').each((_, button) => {
						if (button.dataset.uiIcon === 'x') {
							return;
						}

						button.dataset.uiIcon = 'x';
						button.innerHTML = closeIconSvg;
						button.querySelector('svg')?.setAttribute('aria-hidden', 'true');
					});
				});
			};

			select.on('select2:select select2:unselect', decorateChoicesAsBadges);
			decorateChoicesAsBadges();
		}

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

const designSystemSidebarShell = document.querySelector('[data-ds-sidebar-shell]');
const designSystemSidebar = document.querySelector('[data-ds-sidebar]');
const designSystemSidebarToggle = document.querySelector('[data-ds-sidebar-toggle]');
const designSystemMobileToggle = document.querySelector('[data-ds-sidebar-mobile-toggle]');
const designSystemSidebarOverlay = document.querySelector('[data-ds-sidebar-overlay]');

if (designSystemSidebarShell && designSystemSidebar && designSystemSidebarToggle) {
	const storageKey = 'design-system.sidebar-collapsed';
	const desktopMedia = window.matchMedia('(min-width: 768px)');
	let collapsed = false;

	try {
		collapsed = window.localStorage.getItem(storageKey) === '1';
	} catch (_) {
		collapsed = false;
	}

	const applyDesignSystemSidebarState = () => {
		designSystemSidebarShell.classList.toggle('is-collapsed', collapsed);
		designSystemSidebar.classList.toggle('is-collapsed', collapsed);
		designSystemSidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
		designSystemSidebarToggle.setAttribute(
			'aria-label',
			collapsed
				? (designSystemSidebarToggle.dataset.expandLabel ?? 'Expandir menu lateral')
				: (designSystemSidebarToggle.dataset.collapseLabel ?? 'Recolher menu lateral'),
		);
	};

	const setDesignSystemMobileMenu = (isOpen) => {
		designSystemSidebar.classList.toggle('is-mobile-open', isOpen);
		designSystemSidebarOverlay?.classList.toggle('is-open', isOpen);
		designSystemMobileToggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	};

	designSystemSidebarToggle.addEventListener('click', () => {
		if (!desktopMedia.matches) {
			setDesignSystemMobileMenu(false);
			return;
		}

		collapsed = !collapsed;
		applyDesignSystemSidebarState();

		try {
			window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
		} catch (_) {}
	});

	designSystemMobileToggle?.addEventListener('click', () => {
		setDesignSystemMobileMenu(!designSystemSidebar.classList.contains('is-mobile-open'));
	});

	designSystemSidebarOverlay?.addEventListener('click', () => setDesignSystemMobileMenu(false));

	const closeOtherDesignSystemSubmenus = (currentToggle = null) => {
		for (const toggle of designSystemSidebar.querySelectorAll('[data-ds-sidebar-submenu-toggle]')) {
			if (toggle === currentToggle) continue;

			const submenuId = toggle.getAttribute('aria-controls');
			const submenu = submenuId ? document.getElementById(submenuId) : null;
			toggle.setAttribute('aria-expanded', 'false');
			submenu?.classList.add('hidden');
		}
	};

	for (const toggle of designSystemSidebar.querySelectorAll('[data-ds-sidebar-submenu-toggle]')) {
		toggle.addEventListener('click', () => {
			if (desktopMedia.matches && collapsed) {
				collapsed = false;
				applyDesignSystemSidebarState();

				try {
					window.localStorage.setItem(storageKey, '0');
				} catch (_) {}
			}

			const submenuId = toggle.getAttribute('aria-controls');
			const submenu = submenuId ? document.getElementById(submenuId) : null;
			const shouldOpen = toggle.getAttribute('aria-expanded') !== 'true';

			closeOtherDesignSystemSubmenus(toggle);
			toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
			submenu?.classList.toggle('hidden', !shouldOpen);
		});
	}

	for (const link of designSystemSidebar.querySelectorAll('[data-ds-sidebar-link]')) {
		link.addEventListener('click', () => {
			if (link.getAttribute('href')?.startsWith('#')) {
				for (const sidebarLink of designSystemSidebar.querySelectorAll('[data-ds-sidebar-link]')) {
					const isCurrent = sidebarLink === link;
					sidebarLink.classList.toggle('is-active', isCurrent);

					if (isCurrent) {
						sidebarLink.setAttribute('aria-current', 'page');
					} else {
						sidebarLink.removeAttribute('aria-current');
					}
				}
			}

			setDesignSystemMobileMenu(false);
		});
	}

	desktopMedia.addEventListener('change', () => setDesignSystemMobileMenu(false));
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			setDesignSystemMobileMenu(false);
		}
	});

	applyDesignSystemSidebarState();
}

// Generic destructive-confirmation for the Layout System: any form or link carrying
// data-ui-confirm shows an accessible SweetAlert2 dialog themed with --ui-* tokens
// before it submits/navigates. Replaces one-off confirm() calls and admin-only variants.
const uiConfirmForms = document.querySelectorAll('form[data-ui-confirm]');

for (const form of uiConfirmForms) {
	form.addEventListener('submit', async (event) => {
		if (form.dataset.uiConfirmed === 'true') {
			return;
		}

		event.preventDefault();

		const result = await Swal.fire({
			title: form.dataset.uiConfirm || 'Confirmar ação',
			text: form.dataset.uiConfirmText || '',
			icon: form.dataset.uiConfirmIcon || 'warning',
			showCancelButton: true,
			focusCancel: true,
			confirmButtonText: form.dataset.uiConfirmConfirm || 'Confirmar',
			cancelButtonText: form.dataset.uiConfirmCancel || 'Cancelar',
			customClass: {
				popup: 'ui-swal-popup',
				title: 'ui-swal-title',
				htmlContainer: 'ui-swal-body',
				actions: 'ui-swal-actions',
				confirmButton: 'ui-swal-confirm',
				cancelButton: 'ui-swal-cancel',
			},
			buttonsStyling: false,
		});

		if (result.isConfirmed) {
			form.dataset.uiConfirmed = 'true';
			HTMLFormElement.prototype.submit.call(form);
		}
	});
}

// x-ui.field wires label, hint and error to its control via aria-describedby without
// requiring every view to repeat the id manually. Error ids are already appended by
// x-ui.input/select/textarea when the field name has a validation error; this covers
// the hint id, which has no equivalent server-side signal to hook into.
for (const field of document.querySelectorAll('[data-ui-field][data-for]')) {
	const forId = field.getAttribute('data-for');
	const control = forId ? document.getElementById(forId) : null;

	if (!(control instanceof HTMLElement)) {
		continue;
	}

	const describedIds = new Set((control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));

	if (field.querySelector(`#${CSS.escape(forId)}-hint`)) {
		describedIds.add(`${forId}-hint`);
	}

	if (field.querySelector(`#${CSS.escape(forId)}-error`)) {
		describedIds.add(`${forId}-error`);
	}

	if (describedIds.size > 0) {
		control.setAttribute('aria-describedby', [...describedIds].join(' '));
	}
}

const closeUiDropdown = (dropdown, restoreFocus = false) => {
	const trigger = dropdown?.querySelector('[data-ui-dropdown-trigger]');
	const menu = dropdown?.querySelector('[data-ui-dropdown-menu]');

	if (!(trigger instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
		return;
	}

	trigger.setAttribute('aria-expanded', 'false');
	menu.classList.add('hidden');

	if (restoreFocus) {
		trigger.focus();
	}
};

const closeOtherUiDropdowns = (currentDropdown = null) => {
	for (const dropdown of document.querySelectorAll('[data-ui-dropdown]')) {
		if (dropdown !== currentDropdown) {
			closeUiDropdown(dropdown);
		}
	}
};

for (const dropdown of document.querySelectorAll('[data-ui-dropdown]')) {
	const trigger = dropdown.querySelector('[data-ui-dropdown-trigger]');
	const menu = dropdown.querySelector('[data-ui-dropdown-menu]');

	if (!(trigger instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
		continue;
	}

	trigger.addEventListener('click', () => {
		const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';
		closeOtherUiDropdowns(dropdown);
		trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
		menu.classList.toggle('hidden', !shouldOpen);
	});

	trigger.addEventListener('keydown', (event) => {
		if (event.key !== 'ArrowDown') {
			return;
		}

		event.preventDefault();
		closeOtherUiDropdowns(dropdown);
		trigger.setAttribute('aria-expanded', 'true');
		menu.classList.remove('hidden');
		menu.querySelector('[role="menuitem"]:not([disabled])')?.focus();
	});

	menu.addEventListener('click', (event) => {
		if (event.target instanceof Element && event.target.closest('[role="menuitem"]')) {
			closeUiDropdown(dropdown, true);
		}
	});
}

for (const dismissButton of document.querySelectorAll('[data-ui-alert-dismiss]')) {
	dismissButton.addEventListener('click', () => {
		const alert = dismissButton.closest('[data-ui-alert]');

		if (alert instanceof HTMLElement) {
			if (alert.hasAttribute('data-ui-demo-alert')) {
				alert.classList.add('hidden');
			} else {
				alert.remove();
			}
		}
	});
}

for (const trigger of document.querySelectorAll('[data-ui-alert-trigger]')) {
	trigger.addEventListener('click', () => {
		const requestedVariant = trigger.getAttribute('data-ui-alert-trigger');

		for (const alert of document.querySelectorAll('[data-ui-demo-alert]')) {
			const shouldShow = requestedVariant === 'all' || alert.getAttribute('data-ui-demo-alert') === requestedVariant;

			if (shouldShow) {
				alert.classList.remove('hidden');
			}
		}
	});
}

const activateUiTab = (tabs, selectedTab, moveFocus = false) => {
	const tabButtons = [...tabs.querySelectorAll('[data-ui-tab]')];

	for (const tab of tabButtons) {
		const isSelected = tab === selectedTab;
		const panelId = tab.getAttribute('aria-controls');
		const panel = panelId ? document.getElementById(panelId) : null;

		tab.setAttribute('aria-selected', isSelected ? 'true' : 'false');
		tab.setAttribute('tabindex', isSelected ? '0' : '-1');

		if (panel instanceof HTMLElement) {
			panel.hidden = !isSelected;
		}
	}

	if (moveFocus) {
		selectedTab.focus();
	}
};

for (const tabs of document.querySelectorAll('[data-ui-tabs]')) {
	const tabButtons = [...tabs.querySelectorAll('[data-ui-tab]')];
	const enabledTabs = tabButtons.filter((tab) => !tab.disabled);

	for (const tab of tabButtons) {
		tab.addEventListener('click', () => activateUiTab(tabs, tab));

		tab.addEventListener('keydown', (event) => {
			if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key) || enabledTabs.length === 0) {
				return;
			}

			event.preventDefault();
			const currentIndex = enabledTabs.indexOf(tab);
			let nextIndex = currentIndex;

			if (event.key === 'Home') nextIndex = 0;
			if (event.key === 'End') nextIndex = enabledTabs.length - 1;
			if (event.key === 'ArrowRight') nextIndex = (currentIndex + 1) % enabledTabs.length;
			if (event.key === 'ArrowLeft') nextIndex = (currentIndex - 1 + enabledTabs.length) % enabledTabs.length;

			activateUiTab(tabs, enabledTabs[nextIndex], true);
		});
	}

	const initialTab = tabButtons.find((tab) => tab.getAttribute('aria-selected') === 'true') ?? enabledTabs[0];

	if (initialTab) {
		activateUiTab(tabs, initialTab);
	}
}

let activeUiModal = null;
let uiModalPreviousFocus = null;
let uiModalPreviousOverflow = '';

const uiModalFocusableSelector = [
	'a[href]',
	'button:not([disabled])',
	'input:not([disabled])',
	'select:not([disabled])',
	'textarea:not([disabled])',
	'[tabindex]:not([tabindex="-1"])',
].join(',');

const closeUiModal = (modal, restoreFocus = true) => {
	if (!(modal instanceof HTMLElement)) {
		return;
	}

	modal.classList.add('hidden');
	modal.setAttribute('aria-hidden', 'true');

	if (activeUiModal === modal) {
		activeUiModal = null;
		document.body.style.overflow = uiModalPreviousOverflow;

		if (restoreFocus && uiModalPreviousFocus instanceof HTMLElement) {
			uiModalPreviousFocus.focus();
		}

		uiModalPreviousFocus = null;
	}
};

const openUiModal = (modal, trigger = null) => {
	if (!(modal instanceof HTMLElement)) {
		return;
	}

	if (activeUiModal) {
		closeUiModal(activeUiModal, false);
	}

	uiModalPreviousFocus = trigger instanceof HTMLElement ? trigger : document.activeElement;
	uiModalPreviousOverflow = document.body.style.overflow;
	activeUiModal = modal;
	modal.classList.remove('hidden');
	modal.setAttribute('aria-hidden', 'false');
	document.body.style.overflow = 'hidden';

	const panel = modal.querySelector('[role="dialog"]');
	const firstFocusable = panel?.querySelector(uiModalFocusableSelector);
	(firstFocusable ?? panel)?.focus();
};

for (const trigger of document.querySelectorAll('[data-ui-modal-open]')) {
	trigger.addEventListener('click', () => {
		const modalId = trigger.getAttribute('data-ui-modal-open');
		openUiModal(modalId ? document.getElementById(modalId) : null, trigger);
	});
}

for (const closeButton of document.querySelectorAll('[data-ui-modal-close]')) {
	closeButton.addEventListener('click', () => closeUiModal(closeButton.closest('[data-ui-modal]')));
}

document.addEventListener('click', (event) => {
	if (event.target instanceof Element && !event.target.closest('[data-ui-dropdown]')) {
		closeOtherUiDropdowns();
	}
});

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') {
		if (activeUiModal) {
			event.preventDefault();
			closeUiModal(activeUiModal);
			return;
		}

		const openDropdown = document.querySelector('[data-ui-dropdown-trigger][aria-expanded="true"]')?.closest('[data-ui-dropdown]');

		if (openDropdown) {
			event.preventDefault();
			closeUiDropdown(openDropdown, true);
		}
	}

	if (event.key !== 'Tab' || !activeUiModal) {
		return;
	}

	const focusableElements = [...activeUiModal.querySelectorAll(uiModalFocusableSelector)]
		.filter((element) => element instanceof HTMLElement && element.offsetParent !== null);

	if (focusableElements.length === 0) {
		event.preventDefault();
		activeUiModal.querySelector('[role="dialog"]')?.focus();
		return;
	}

	const firstElement = focusableElements[0];
	const lastElement = focusableElements[focusableElements.length - 1];

	if (event.shiftKey && document.activeElement === firstElement) {
		event.preventDefault();
		lastElement.focus();
	} else if (!event.shiftKey && document.activeElement === lastElement) {
		event.preventDefault();
		firstElement.focus();
	}
});

const uiDemoToast = document.querySelector('[data-ui-demo-toast]');
const uiDemoToastMessage = uiDemoToast?.querySelector('[data-ui-demo-toast-message]');
let uiDemoToastTimeout = null;

for (const trigger of document.querySelectorAll('[data-ui-demo-message]')) {
	trigger.addEventListener('click', () => {
		if (!(uiDemoToast instanceof HTMLElement) || !(uiDemoToastMessage instanceof HTMLElement)) {
			return;
		}

		uiDemoToastMessage.textContent = trigger.getAttribute('data-ui-demo-message') ?? 'Ação executada.';
		uiDemoToast.classList.remove('hidden');

		if (uiDemoToastTimeout !== null) {
			window.clearTimeout(uiDemoToastTimeout);
		}

		uiDemoToastTimeout = window.setTimeout(() => {
			uiDemoToast.classList.add('hidden');
			uiDemoToastTimeout = null;
		}, 3200);
	});
}

for (const copyButton of document.querySelectorAll('[data-ui-copy-code]')) {
	copyButton.addEventListener('click', async () => {
		const code = copyButton.closest('.ui-code-example')?.querySelector('[data-ui-code]')?.textContent ?? '';

		if (code.trim() === '') {
			return;
		}

		try {
			await navigator.clipboard.writeText(code.trim());
		} catch (_) {
			const textarea = document.createElement('textarea');
			textarea.value = code.trim();
			textarea.style.position = 'fixed';
			textarea.style.opacity = '0';
			document.body.appendChild(textarea);
			textarea.select();
			document.execCommand('copy');
			textarea.remove();
		}

		copyButton.textContent = copyButton.dataset.copiedLabel ?? 'Copiado';
		window.setTimeout(() => {
			copyButton.textContent = copyButton.dataset.copyLabel ?? 'Copiar';
		}, 1600);
	});
}

document.addEventListener('click', (event) => {
	if (!(event.target instanceof Element)) {
		return;
	}

	const readonlyButton = event.target.closest('[data-ui-readonly="true"]');

	if (!readonlyButton) {
		return;
	}

	event.preventDefault();
	event.stopImmediatePropagation();

	const playgroundStatus = readonlyButton.closest('[data-ui-button-playground]')?.querySelector('[data-ui-button-playground-status]');

	if (playgroundStatus instanceof HTMLElement) {
		playgroundStatus.textContent = 'Somente leitura: a ação foi bloqueada, mas o botão continua acessível pelo teclado.';
	}
}, true);

const updateUiProgress = (progress, value) => {
	if (!(progress instanceof HTMLElement)) {
		return;
	}

	const max = Number(progress.getAttribute('aria-valuemax')) || 100;
	const normalizedValue = Math.min(max, Math.max(0, Number(value) || 0));
	const percent = (normalizedValue / max) * 100;
	const suffix = progress.dataset.uiProgressSuffix ?? '%';

	progress.setAttribute('aria-valuenow', String(normalizedValue));
	const fill = progress.querySelector('[data-ui-progress-fill]');
	const valueLabel = progress.querySelector('[data-ui-progress-value]');

	if (fill instanceof HTMLElement) {
		fill.style.width = `${percent}%`;
	}

	if (valueLabel instanceof HTMLElement) {
		valueLabel.textContent = `${normalizedValue}${suffix}`;
	}
};

for (const slider of document.querySelectorAll('[data-ui-slider]')) {
	const input = slider.querySelector('[data-ui-slider-input]');
	const output = slider.querySelector('[data-ui-slider-output]');

	if (!(input instanceof HTMLInputElement)) {
		continue;
	}

	const updateSlider = () => {
		const min = Number(input.min) || 0;
		const max = Number(input.max) || 100;
		const value = Number(input.value);
		const percent = max === min ? 0 : ((value - min) / (max - min)) * 100;
		const suffix = slider.dataset.uiSliderSuffix ?? '';

		slider.style.setProperty('--ui-slider-progress', `${percent}%`);

		if (output instanceof HTMLOutputElement) {
			output.value = `${input.value}${suffix}`;
		}

		const progressSelector = slider.dataset.uiProgressTarget;
		const progress = progressSelector ? document.querySelector(progressSelector) : null;
		updateUiProgress(progress, value);
	};

	input.addEventListener('input', updateSlider);
	updateSlider();
}

const buttonVariantClasses = [
	'ui-button-primary',
	'ui-button-neutral',
	'ui-button-info',
	'ui-button-success',
	'ui-button-warning',
	'ui-button-danger',
	'ui-button-secondary',
	'ui-button-outline',
	'ui-button-ghost',
];
const buttonSizeClasses = [
	'min-h-9', 'px-3', 'py-2', 'text-xs',
	'min-h-10', 'px-4', 'text-sm',
	'min-h-12', 'px-6', 'py-3', 'text-base',
];
const buttonSizeClassMap = {
	sm: ['min-h-9', 'px-3', 'py-2', 'text-xs'],
	md: ['min-h-10', 'px-4', 'py-2', 'text-sm'],
	lg: ['min-h-12', 'px-6', 'py-3', 'text-base'],
};

for (const playground of document.querySelectorAll('[data-ui-button-playground]')) {
	const preview = playground.querySelector('[data-ui-button-playground-preview]');
	const code = playground.querySelector('[data-ui-code]');
	const status = playground.querySelector('[data-ui-button-playground-status]');
	const getControl = (name) => playground.querySelector(`[data-ui-button-control="${name}"]`);

	if (!(preview instanceof HTMLButtonElement) || !(code instanceof HTMLElement)) {
		continue;
	}

	const renderPlaygroundButton = () => {
		const variant = getControl('variant')?.value ?? 'primary';
		const size = getControl('size')?.value ?? 'md';
		const text = getControl('text')?.value?.trim() || 'Botão';
		const icon = getControl('icon')?.value ?? '';
		const disabled = Boolean(getControl('disabled')?.checked);
		const readonly = Boolean(getControl('readonly')?.checked);
		const loading = Boolean(getControl('loading')?.checked);
		const unavailable = disabled || loading;

		preview.classList.remove(...buttonVariantClasses, ...buttonSizeClasses, 'ui-button-readonly');
		preview.classList.add(`ui-button-${variant}`, ...(buttonSizeClassMap[size] ?? buttonSizeClassMap.md));
		preview.disabled = unavailable;
		preview.toggleAttribute('aria-busy', loading);

		if (readonly && !unavailable) {
			preview.setAttribute('aria-disabled', 'true');
			preview.dataset.uiReadonly = 'true';
			preview.classList.add('ui-button-readonly');
		} else {
			preview.removeAttribute('aria-disabled');
			delete preview.dataset.uiReadonly;
		}

		preview.replaceChildren();

		if (loading) {
			const spinner = document.createElement('span');
			spinner.className = 'ui-spinner';
			spinner.setAttribute('aria-hidden', 'true');
			preview.append(spinner, document.createTextNode('Carregando'));
		} else {
			const iconTemplate = icon ? playground.querySelector(`[data-ui-button-icon-template="${icon}"]`) : null;

			if (iconTemplate instanceof HTMLTemplateElement) {
				preview.append(iconTemplate.content.cloneNode(true));
			}

			preview.append(document.createTextNode(text));
		}

		const bladeAttributes = [`variant="${variant}"`, `size="${size}"`];
		if (disabled) bladeAttributes.push(':disabled="true"');
		if (readonly) bladeAttributes.push(':readonly="true"');
		if (loading) bladeAttributes.push(':loading="true"', 'loading-label="Carregando"');
		const safeText = text.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
		const contents = icon
			? `\n    <x-ui.icon name="${icon}" size="sm" /> ${safeText}\n`
			: safeText;

		code.textContent = `<x-ui.button ${bladeAttributes.join(' ')}>${contents}</x-ui.button>`;

		if (status instanceof HTMLElement) {
			status.textContent = loading
				? 'Loading ativo: o botão fica desabilitado enquanto processa.'
				: disabled
					? 'Disabled ativo: o botão não recebe foco nem executa a ação.'
					: readonly
						? 'Somente leitura: continua focável, mas a ação é bloqueada.'
						: 'Pronto para testar. Clique no botão da prévia.';
		}
	};

	for (const control of playground.querySelectorAll('[data-ui-button-control]')) {
		control.addEventListener('input', renderPlaygroundButton);
		control.addEventListener('change', renderPlaygroundButton);
	}

	preview.addEventListener('click', () => {
		if (status instanceof HTMLElement) {
			status.textContent = 'Botão acionado com sucesso.';
		}
	});

	renderPlaygroundButton();
}

for (const accordion of document.querySelectorAll('[data-ui-accordion]')) {
	const items = [...accordion.querySelectorAll('.ui-accordion-item')];
	const allowsMultiple = accordion.dataset.uiAccordionMultiple === 'true';

	for (const item of items) {
		const trigger = item.querySelector('.ui-accordion-trigger');

		if (item.dataset.disabled === 'true') {
			trigger?.addEventListener('click', (event) => event.preventDefault());
			continue;
		}

		item.addEventListener('toggle', () => {
			if (!item.open || allowsMultiple) {
				return;
			}

			for (const otherItem of items) {
				if (otherItem !== item) otherItem.open = false;
			}
		});
	}
}

for (const buttonGroup of document.querySelectorAll('.ui-button-group')) {
	for (const button of buttonGroup.querySelectorAll('.ui-button[aria-pressed]')) {
		button.addEventListener('click', () => {
			for (const groupButton of buttonGroup.querySelectorAll('.ui-button[aria-pressed]')) {
				groupButton.setAttribute('aria-pressed', groupButton === button ? 'true' : 'false');
			}
		});
	}
}

for (const removeButton of document.querySelectorAll('[data-ui-attachment-remove]')) {
	removeButton.addEventListener('click', () => {
		const attachment = removeButton.closest('[data-ui-attachment]');

		if (attachment instanceof HTMLElement) {
			attachment.remove();
		}
	});
}

const calendarDateToIso = (date) => {
	const year = date.getUTCFullYear();
	const month = String(date.getUTCMonth() + 1).padStart(2, '0');
	const day = String(date.getUTCDate()).padStart(2, '0');

	return `${year}-${month}-${day}`;
};

const initializeUiCalendar = (calendar) => {
	const grid = calendar.querySelector('[data-ui-calendar-grid]');
	const heading = calendar.querySelector('[data-ui-calendar-heading]');
	const input = calendar.querySelector('[data-ui-calendar-input]');
	const [initialYear, initialMonth] = (calendar.dataset.uiCalendarMonth || '').split('-').map(Number);
	let visibleDate = new Date(Date.UTC(initialYear || new Date().getFullYear(), (initialMonth || (new Date().getMonth() + 1)) - 1, 1));
	let selected = calendar.dataset.uiCalendarSelected || '';

	if (!(grid instanceof HTMLElement) || !(heading instanceof HTMLElement)) {
		return;
	}

	const renderCalendar = () => {
		const year = visibleDate.getUTCFullYear();
		const month = visibleDate.getUTCMonth();
		const firstWeekday = new Date(Date.UTC(year, month, 1)).getUTCDay();
		const firstCellDate = new Date(Date.UTC(year, month, 1 - firstWeekday));
		const today = new Date();
		const todayIso = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

		heading.textContent = new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(visibleDate);
		grid.replaceChildren();

		for (let index = 0; index < 42; index += 1) {
			const date = new Date(firstCellDate);
			date.setUTCDate(firstCellDate.getUTCDate() + index);
			const iso = calendarDateToIso(date);
			const dayButton = document.createElement('button');
			const isOutside = date.getUTCMonth() !== month;
			const isDisabled = (calendar.dataset.uiCalendarMin && iso < calendar.dataset.uiCalendarMin)
				|| (calendar.dataset.uiCalendarMax && iso > calendar.dataset.uiCalendarMax);

			dayButton.type = 'button';
			dayButton.className = 'ui-calendar-day';
			dayButton.textContent = String(date.getUTCDate());
			dayButton.dataset.date = iso;
			dayButton.setAttribute('role', 'gridcell');
			dayButton.setAttribute('aria-label', new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeZone: 'UTC' }).format(date));
			dayButton.classList.toggle('is-outside', isOutside);
			dayButton.classList.toggle('is-today', iso === todayIso);
			dayButton.classList.toggle('is-selected', iso === selected);
			dayButton.setAttribute('aria-selected', iso === selected ? 'true' : 'false');
			dayButton.disabled = Boolean(isDisabled);

			dayButton.addEventListener('click', () => {
				selected = iso;
				calendar.dataset.uiCalendarSelected = iso;
				visibleDate = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), 1));

				if (input instanceof HTMLInputElement) {
					input.value = iso;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}

				calendar.dispatchEvent(new CustomEvent('ui:date-selected', { bubbles: true, detail: { value: iso } }));
				renderCalendar();
			});

			grid.append(dayButton);
		}
	};

	calendar.querySelector('[data-ui-calendar-previous]')?.addEventListener('click', () => {
		visibleDate = new Date(Date.UTC(visibleDate.getUTCFullYear(), visibleDate.getUTCMonth() - 1, 1));
		renderCalendar();
	});
	calendar.querySelector('[data-ui-calendar-next]')?.addEventListener('click', () => {
		visibleDate = new Date(Date.UTC(visibleDate.getUTCFullYear(), visibleDate.getUTCMonth() + 1, 1));
		renderCalendar();
	});

	renderCalendar();
};

for (const calendar of document.querySelectorAll('[data-ui-calendar]')) {
	initializeUiCalendar(calendar);
}

const closeUiDatePicker = (datePicker) => {
	const trigger = datePicker?.querySelector('[data-ui-date-picker-trigger]');
	const panel = datePicker?.querySelector('[data-ui-date-picker-panel]');

	if (trigger instanceof HTMLElement && panel instanceof HTMLElement) {
		trigger.setAttribute('aria-expanded', 'false');
		panel.classList.add('hidden');
	}
};

for (const datePicker of document.querySelectorAll('[data-ui-date-picker]')) {
	const trigger = datePicker.querySelector('[data-ui-date-picker-trigger]');
	const panel = datePicker.querySelector('[data-ui-date-picker-panel]');
	const label = datePicker.querySelector('[data-ui-date-picker-label]');

	trigger?.addEventListener('click', () => {
		const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';

		for (const otherPicker of document.querySelectorAll('[data-ui-date-picker]')) {
			if (otherPicker !== datePicker) closeUiDatePicker(otherPicker);
		}

		trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
		panel?.classList.toggle('hidden', !shouldOpen);
	});

	datePicker.addEventListener('ui:date-selected', (event) => {
		const value = event.detail?.value;

		if (value && label instanceof HTMLElement) {
			const date = new Date(`${value}T00:00:00Z`);
			label.textContent = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'medium', timeZone: 'UTC' }).format(date);
		}

		closeUiDatePicker(datePicker);
		trigger?.focus();
	});
}

document.addEventListener('click', (event) => {
	if (!(event.target instanceof Element)) return;

	for (const datePicker of document.querySelectorAll('[data-ui-date-picker]')) {
		if (!datePicker.contains(event.target)) closeUiDatePicker(datePicker);
	}
});

document.addEventListener('keydown', (event) => {
	if (event.key !== 'Escape') return;

	for (const datePicker of document.querySelectorAll('[data-ui-date-picker]')) {
		const trigger = datePicker.querySelector('[data-ui-date-picker-trigger][aria-expanded="true"]');

		if (trigger instanceof HTMLElement) {
			closeUiDatePicker(datePicker);
			trigger.focus();
		}
	}
});

for (const dataTable of document.querySelectorAll('[data-ui-data-table]')) {
	const table = dataTable.querySelector('table');
	const body = table?.tBodies[0];
	const filter = dataTable.querySelector('[data-ui-data-table-filter]');
	const previous = dataTable.querySelector('[data-ui-data-table-previous]');
	const next = dataTable.querySelector('[data-ui-data-table-next]');
	const status = dataTable.querySelector('[data-ui-data-table-status]');
	const pageSize = Number(dataTable.dataset.uiDataTablePageSize) || 5;
	let page = 0;
	let rows = body ? [...body.rows] : [];

	if (!table || !body) continue;

	const renderDataTable = () => {
		rows = [...body.rows];
		const term = filter instanceof HTMLInputElement ? filter.value.trim().toLocaleLowerCase() : '';
		const filteredRows = rows.filter((row) => row.textContent.toLocaleLowerCase().includes(term));
		const pageCount = Math.max(1, Math.ceil(filteredRows.length / pageSize));
		page = Math.min(page, pageCount - 1);

		for (const row of rows) {
			const filteredIndex = filteredRows.indexOf(row);
			row.hidden = filteredIndex < page * pageSize || filteredIndex >= (page + 1) * pageSize;
		}

		if (status instanceof HTMLElement) {
			status.textContent = `${filteredRows.length} resultado(s) · Página ${page + 1} de ${pageCount}`;
		}

		if (previous instanceof HTMLButtonElement) previous.disabled = page === 0;
		if (next instanceof HTMLButtonElement) next.disabled = page >= pageCount - 1;
	};

	filter?.addEventListener('input', () => { page = 0; renderDataTable(); });
	previous?.addEventListener('click', () => { page = Math.max(0, page - 1); renderDataTable(); });
	next?.addEventListener('click', () => { page += 1; renderDataTable(); });
	table.addEventListener('ui:table-sorted', renderDataTable);
	table.addEventListener('ui:table-cell-updated', renderDataTable);
	renderDataTable();
}

const writeUiClipboardText = async (value) => {
	if (navigator.clipboard?.writeText) {
		try {
			await navigator.clipboard.writeText(value);
			return;
		} catch (_) {
			// Use the selection fallback when clipboard permission is unavailable.
		}
	}

	const textarea = document.createElement('textarea');
	textarea.value = value;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.append(textarea);
	textarea.select();
	document.execCommand('copy');
	textarea.remove();
};

const showUiCopyFeedback = (button) => {
	const originalLabel = button.getAttribute('aria-label') ?? '';
	button.classList.add('is-copied');
	button.setAttribute('aria-label', uiTranslations.copied ?? 'Copiado');
	window.setTimeout(() => {
		button.classList.remove('is-copied');
		button.setAttribute('aria-label', originalLabel);
	}, 1400);
};

const setUiTableCellEditing = (cell, editing) => {
	const display = cell.querySelector('[data-ui-table-cell-display]');
	const input = cell.querySelector('[data-ui-table-cell-input]');
	const edit = cell.querySelector('[data-ui-table-cell-edit]');
	const save = cell.querySelector('[data-ui-table-cell-save]');
	const cancel = cell.querySelector('[data-ui-table-cell-cancel]');

	if (!(input instanceof HTMLInputElement)) return;

	cell.classList.toggle('is-editing', editing);
	display?.classList.toggle('hidden', editing);
	input.classList.toggle('hidden', !editing);
	edit?.classList.toggle('hidden', editing);
	save?.classList.toggle('hidden', !editing);
	cancel?.classList.toggle('hidden', !editing);

	if (editing) {
		input.dataset.originalValue = input.value;
		input.focus();
		input.select();
	}
};

for (const cell of document.querySelectorAll('[data-ui-table-cell]')) {
	const input = cell.querySelector('[data-ui-table-cell-input]');
	const display = cell.querySelector('[data-ui-table-cell-display]');
	const edit = cell.querySelector('[data-ui-table-cell-edit]');
	const save = cell.querySelector('[data-ui-table-cell-save]');
	const cancel = cell.querySelector('[data-ui-table-cell-cancel]');
	const copy = cell.querySelector('[data-ui-table-cell-copy]');

	const cancelEditing = () => {
		if (input instanceof HTMLInputElement) input.value = input.dataset.originalValue ?? cell.dataset.uiTableCellValue ?? '';
		setUiTableCellEditing(cell, false);
	};

	const saveEditing = () => {
		if (!(input instanceof HTMLInputElement) || !(display instanceof HTMLElement)) return;

		const previousValue = cell.dataset.uiTableCellValue ?? '';
		const value = input.value.trim();
		cell.dataset.uiTableCellValue = value;
		display.textContent = value === '' ? '—' : value;
		copy?.setAttribute('aria-label', `${uiTranslations.copy ?? 'Copiar'}: ${value}`);
		edit?.setAttribute('aria-label', `${uiTranslations.edit ?? 'Editar'}: ${value}`);
		setUiTableCellEditing(cell, false);
		cell.dispatchEvent(new CustomEvent('ui:table-cell-updated', {
			bubbles: true,
			detail: { previousValue, value, name: input.name || null },
		}));
	};

	edit?.addEventListener('click', () => setUiTableCellEditing(cell, true));
	save?.addEventListener('click', saveEditing);
	cancel?.addEventListener('click', cancelEditing);
	copy?.addEventListener('click', async () => {
		await writeUiClipboardText(cell.dataset.uiTableCellValue ?? '');
		showUiCopyFeedback(copy);
	});
	input?.addEventListener('keydown', (event) => {
		if (event.key === 'Enter') saveEditing();
		if (event.key === 'Escape') cancelEditing();
	});
}

for (const button of document.querySelectorAll('[data-ui-copy-text]')) {
	button.addEventListener('click', async () => {
		await writeUiClipboardText(button.dataset.uiCopyText ?? '');
		showUiCopyFeedback(button);
	});
}

for (const button of document.querySelectorAll('[data-ui-table-row-edit]')) {
	button.addEventListener('click', () => {
		button.closest('tr')?.querySelector('[data-ui-table-cell-edit]')?.click();
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
				popup: 'ui-swal-popup',
				title: 'ui-swal-title',
				htmlContainer: 'ui-swal-body',
				actions: 'ui-swal-actions',
				confirmButton: 'ui-swal-confirm',
				cancelButton: 'ui-swal-cancel',
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
