@php
    $currentLocale = app()->getLocale();
    $isRtl = in_array($currentLocale, ['ar', 'he', 'fa', 'ur']);
@endphp

@if($isRtl)
    <script>
        document.documentElement.setAttribute('dir', 'rtl');
        document.body.classList.add('rtl');
        
        // Advanced RTL fixes for dynamic elements
        document.addEventListener('DOMContentLoaded', function() {
            // Fix dropdown positioning for dynamically created elements
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                // Fix dropdown panels
                                const dropdowns = node.querySelectorAll ? node.querySelectorAll('.fi-dropdown-panel, [data-dropdown-content], .fi-fo-select-menu, .fi-ta-filters-dropdown') : [];
                                dropdowns.forEach(function(dropdown) {
                                    dropdown.style.right = '0';
                                    dropdown.style.left = 'auto';
                                    dropdown.style.transformOrigin = 'top right';
                                });
                                
                                // Fix select menus
                                const selectMenus = node.querySelectorAll ? node.querySelectorAll('[x-data*="selectField"] .fi-fo-select-menu') : [];
                                selectMenus.forEach(function(menu) {
                                    menu.style.right = '0';
                                    menu.style.left = 'auto';
                                });
                                
                                // Fix choices.js dropdowns
                                const choicesDropdowns = node.querySelectorAll ? node.querySelectorAll('.choices__list--dropdown') : [];
                                choicesDropdowns.forEach(function(dropdown) {
                                    dropdown.style.right = '0';
                                    dropdown.style.left = 'auto';
                                });
                                
                                // Fix filter form dropdowns
                                const filterDropdowns = node.querySelectorAll ? node.querySelectorAll('.fi-ta-filters [data-dropdown-content], .fi-fo-filter [data-dropdown-content]') : [];
                                filterDropdowns.forEach(function(dropdown) {
                                    dropdown.style.right = '0';
                                    dropdown.style.left = 'auto';
                                    dropdown.style.transformOrigin = 'top right';
                                });
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Fix existing elements
            const existingDropdowns = document.querySelectorAll('.fi-dropdown-panel, [data-dropdown-content], .fi-fo-select-menu');
            existingDropdowns.forEach(function(dropdown) {
                dropdown.style.right = '0';
                dropdown.style.left = 'auto';
                dropdown.style.transformOrigin = 'top right';
            });
        });
        
        // Fix Livewire updates
        document.addEventListener('livewire:update', function() {
            setTimeout(function() {
                const dropdowns = document.querySelectorAll('.fi-dropdown-panel, [data-dropdown-content], .fi-fo-select-menu');
                dropdowns.forEach(function(dropdown) {
                    dropdown.style.right = '0';
                    dropdown.style.left = 'auto';
                    dropdown.style.transformOrigin = 'top right';
                });
            }, 100);
        });
        
        // Fix Alpine.js updates
        document.addEventListener('alpine:init', function() {
            setTimeout(function() {
                const dropdowns = document.querySelectorAll('.fi-dropdown-panel, [data-dropdown-content], .fi-fo-select-menu');
                dropdowns.forEach(function(dropdown) {
                    dropdown.style.right = '0';
                    dropdown.style.left = 'auto';
                    dropdown.style.transformOrigin = 'top right';
                });
            }, 100);
        });
    </script>
    <style>
        /* Inline RTL styles for immediate application */
        html[dir="rtl"] {
            direction: rtl;
        }
        
        [dir="rtl"] * {
            font-family: 'Segoe UI', 'Tahoma', 'Arial', 'Noto Sans Arabic', sans-serif !important;
        }
        
        [dir="rtl"] .fi-sidebar-nav-item-label {
            text-align: right;
        }
        
        [dir="rtl"] .fi-input, 
        [dir="rtl"] .fi-select {
            text-align: right;
        }
        
        [dir="rtl"] .fi-table-cell,
        [dir="rtl"] .fi-table-header-cell {
            text-align: right;
        }
        
        [dir="rtl"] .fi-breadcrumb-item::after {
            content: "‹";
        }
        
        /* Fix dropdown positioning for RTL */
        [dir="rtl"] .fi-dropdown-panel {
            left: auto !important;
            right: 0 !important;
        }
        
        /* Fix select dropdown positioning */
        [dir="rtl"] [x-data*="selectField"] .fi-fo-select-menu,
        [dir="rtl"] [data-select-dropdown] {
            right: 0 !important;
            left: auto !important;
            transform-origin: top right !important;
        }
        
        /* Fix table filters positioning */
        [dir="rtl"] .fi-ta-filters-trigger ~ div,
        [dir="rtl"] .fi-ta-header-toolbar .fi-dropdown-panel,
        [dir="rtl"] [data-dropdown-boundary] [data-dropdown-content],
        [dir="rtl"] .fi-ta-filters [data-dropdown-content] {
            right: 0 !important;
            left: auto !important;
            transform-origin: top right !important;
        }
        
        /* Fix specific filter dropdown positioning */
        [dir="rtl"] .fi-ta-filters-dropdown,
        [dir="rtl"] .fi-ta-filters-form {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Enhanced filter dropdown positioning */
        [dir="rtl"] .fi-ta-header-actions .fi-dropdown-panel,
        [dir="rtl"] .fi-ta-header-toolbar [data-dropdown-panel],
        [dir="rtl"] .fi-ta-filter-item [data-dropdown-content],
        [dir="rtl"] .fi-ta-filter [data-dropdown-content] {
            right: 0 !important;
            left: auto !important;
            transform: translateX(0) !important;
        }
        
        /* Fix filter panel content alignment */
        [dir="rtl"] .fi-ta-filters-panel {
            text-align: right;
        }
        
        /* Fix filter labels and inputs */
        [dir="rtl"] .fi-ta-filter .fi-fo-field-wrp {
            text-align: right;
        }
        
        /* Fix bulk actions dropdown */
        [dir="rtl"] .fi-ta-bulk-actions [data-dropdown-content] {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Fix action dropdown positioning */
        [dir="rtl"] .fi-ac-btn .fi-dropdown-panel,
        [dir="rtl"] .fi-ta-actions .fi-dropdown-panel {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Fix form select dropdowns */
        [dir="rtl"] .fi-fo-select-menu {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Fix multiselect dropdowns */
        [dir="rtl"] .choices__list--dropdown {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Fix notification positioning */
        [dir="rtl"] .fi-no-notifications {
            right: 1rem !important;
            left: auto !important;
        }
        
        /* Fix user menu dropdown */
        [dir="rtl"] .fi-user-menu .fi-dropdown-panel {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Fix language switcher dropdown */
        [dir="rtl"] .language-switcher .fi-dropdown-panel {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Table column sorting indicators */
        [dir="rtl"] .fi-ta-header-cell-sort-btn {
            padding-left: 0.5rem !important;
            padding-right: 0 !important;
        }
        
        /* Fix pagination */
        [dir="rtl"] .fi-pagination-nav {
            direction: ltr;
        }
        
        /* Fix search input */
        [dir="rtl"] .fi-ta-search-field input {
            text-align: right;
            padding-right: 2.5rem;
            padding-left: 0.75rem;
        }
        
        /* Fix search icon positioning */
        [dir="rtl"] .fi-ta-search-field .fi-icon {
            right: auto !important;
            left: 0.75rem !important;
        }
        
        /* Fix modal positioning */
        [dir="rtl"] .fi-modal {
            text-align: right;
        }
        
        /* Fix form field icons */
        [dir="rtl"] .fi-fo-field-wrp .fi-icon {
            right: auto !important;
            left: 0.75rem !important;
        }
        
        /* Fix sidebar toggle */
        [dir="rtl"] .fi-sidebar-open-btn,
        [dir="rtl"] .fi-sidebar-close-btn {
            right: auto !important;
            left: 1rem !important;
        }
    </style>
@else
    <script>
        document.documentElement.setAttribute('dir', 'ltr');
        document.body.classList.remove('rtl');
    </script>
@endif