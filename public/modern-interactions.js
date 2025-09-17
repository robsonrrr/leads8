/**
 * Modern Interactions JavaScript
 * Enhanced user interactions for the modernized lead management system
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeModernInteractions();
});

function initializeModernInteractions() {
    // Initialize Bootstrap tooltips
    initializeTooltips();
    
    // Initialize modern table interactions
    initializeTableInteractions();
    
    // Initialize cart interactions
    initializeCartInteractions();
    
    // Initialize modal interactions
    initializeModalInteractions();
    
    // Initialize form enhancements
    initializeFormEnhancements();
    
    // Initialize loading states
    initializeLoadingStates();
    
    // Initialize responsive behaviors
    initializeResponsiveBehaviors();
    
    // Initialize collapse toggle interactions
    initializeCollapseToggles();
    
    // Hide collapsible sections on page load
    hideCollapsibleSectionsOnLoad();
}

/**
 * Initialize Bootstrap 5 tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus'
        });
    });
}

/**
 * Enhanced table interactions
 */
function initializeTableInteractions() {
    // Row selection with visual feedback
    const checkboxes = document.querySelectorAll('.modern-table input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('.product-row');
            if (row) {
                row.classList.toggle('row-selected', this.checked);
                updateSelectionCounter();
            }
        });
    });
    
    // Select all functionality
    const selectAllCheckbox = document.querySelector('#selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const productCheckboxes = document.querySelectorAll('.product-row input[type="checkbox"]');
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const row = checkbox.closest('.product-row');
                if (row) {
                    row.classList.toggle('row-selected', this.checked);
                }
            });
            updateSelectionCounter();
        });
    }
    
    // Quantity input enhancements
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateQuantityInput(this);
        });
        
        input.addEventListener('blur', function() {
            formatQuantityInput(this);
        });
    });
}

/**
 * Cart-specific interactions
 */
function initializeCartInteractions() {
    // Price input formatting
    const priceInputs = document.querySelectorAll('.price-input');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            formatCurrencyInput(this);
        });
    });
    
    // Discount input validation
    const discountInputs = document.querySelectorAll('.discount-input');
    discountInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateDiscountInput(this);
        });
    });
    
    // Product discount inputs - select all content on click
    const productDiscountInputs = document.querySelectorAll('.product-discount');
    productDiscountInputs.forEach(input => {
        input.addEventListener('click', function() {
            this.select();
        });
    });
    
    // Update discount buttons - set value to base price * multiplier
    const updateDiscountButtons = document.querySelectorAll('.update-discount-btn');
    updateDiscountButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const basePrice = parseFloat(this.getAttribute('data-base-price'));
            const multiplier = parseFloat(this.getAttribute('data-multiplier')) || 10; // Default to 10 if not specified
            const targetInput = document.getElementById(targetId);
            
            if (targetInput && !isNaN(basePrice)) {
                const newValue = (basePrice * multiplier).toFixed(2);
                targetInput.value = newValue;
                
                // Trigger change event to update the discount
                const changeEvent = new Event('change', { bubbles: true });
                targetInput.dispatchEvent(changeEvent);
                
                // Visual feedback
                targetInput.focus();
                targetInput.select();
            }
        });
    });
    
    // Remove item confirmation
    const removeButtons = document.querySelectorAll('.remove-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showRemoveConfirmation(this);
        });
    });
    
    // Hide empty discount functionality
    initializeHideEmptyDiscountButtons();
}

/**
 * Modal enhancements
 */
function initializeModalInteractions() {
    // Auto-focus first input in modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input:not([type="hidden"]), textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
    
    // Form validation in modals
    const modalForms = document.querySelectorAll('.modal form');
    modalForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateModalForm(this)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Form enhancements
 */
function initializeFormEnhancements() {
    // Real-time validation
    const inputs = document.querySelectorAll('.modern-input, .modern-textarea, .modern-select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });
        
        input.addEventListener('input', function() {
            clearValidationState(this);
        });
    });
    
    // Auto-resize textareas
    const textareas = document.querySelectorAll('.modern-textarea');
    textareas.forEach(textarea => {
        autoResizeTextarea(textarea);
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
    });
}

/**
 * Loading states management
 */
function initializeLoadingStates() {
    // Button loading states
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        const form = button.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                showButtonLoading(button);
            });
        }
    });
    
    // AJAX loading indicators
    document.addEventListener('ajaxStart', function() {
        showGlobalLoading();
    });
    
    document.addEventListener('ajaxComplete', function() {
        hideGlobalLoading();
    });
}

/**
 * Responsive behaviors
 */
function initializeResponsiveBehaviors() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            toggleMobileMenu();
        });
    }
    
    // Responsive table scrolling
    const tables = document.querySelectorAll('.modern-table-container');
    tables.forEach(container => {
        addHorizontalScrollIndicators(container);
    });
    
    // Window resize handler
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            handleWindowResize();
        }, 250);
    });
}

/**
 * Utility Functions
 */

function updateSelectionCounter() {
    const selectedCount = document.querySelectorAll('.product-row.row-selected').length;
    const counter = document.querySelector('.selection-counter');
    if (counter) {
        counter.textContent = `${selectedCount} item(s) selecionado(s)`;
        counter.style.display = selectedCount > 0 ? 'block' : 'none';
    }
}

function validateQuantityInput(input) {
    const value = parseFloat(input.value);
    const min = parseFloat(input.getAttribute('min')) || 0;
    const max = parseFloat(input.getAttribute('max')) || Infinity;
    
    if (isNaN(value) || value < min || value > max) {
        input.classList.add('is-invalid');
        return false;
    }
    
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    return true;
}

function formatQuantityInput(input) {
    const value = parseFloat(input.value);
    if (!isNaN(value)) {
        input.value = value.toFixed(0);
    }
}

function formatCurrencyInput(input) {
    const value = parseFloat(input.value.replace(/[^\d.,]/g, '').replace(',', '.'));
    if (!isNaN(value)) {
        input.value = value.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).replace('R$\u00A0', 'R$ ');
    }
}

function validateDiscountInput(input) {
    const value = parseFloat(input.value);
    const maxDiscount = parseFloat(input.dataset.maxDiscount) || 100;
    
    if (isNaN(value) || value < 0 || value > maxDiscount) {
        input.classList.add('is-invalid');
        return false;
    }
    
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    return true;
}

function showRemoveConfirmation(button) {
    const productName = button.closest('.product-row')?.querySelector('.product-name')?.textContent || 'este item';
    
    if (confirm(`Tem certeza que deseja remover ${productName} do carrinho?`)) {
        // Proceed with removal
        const form = button.closest('form');
        if (form) {
            form.submit();
        }
    }
}

function validateModalForm(form) {
    const requiredInputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

function validateInput(input) {
    if (input.hasAttribute('required') && !input.value.trim()) {
        input.classList.add('is-invalid');
        return false;
    }
    
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    return true;
}

function clearValidationState(input) {
    input.classList.remove('is-invalid', 'is-valid');
}

function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

function showButtonLoading(button) {
    const originalText = button.innerHTML;
    button.dataset.originalText = originalText;
    button.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i>Processando...';
    button.disabled = true;
}

function hideButtonLoading(button) {
    if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
        button.disabled = false;
    }
}

function showGlobalLoading() {
    const loader = document.querySelector('.global-loader');
    if (loader) {
        loader.style.display = 'flex';
    }
}

function hideGlobalLoading() {
    const loader = document.querySelector('.global-loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

function toggleMobileMenu() {
    const menu = document.querySelector('.modern-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (menu) {
        menu.classList.toggle('show');
    }
    
    if (overlay) {
        overlay.classList.toggle('show');
    }
}

function addHorizontalScrollIndicators(container) {
    const table = container.querySelector('.modern-table');
    if (!table) return;
    
    const updateScrollIndicators = () => {
        const isScrollable = table.scrollWidth > container.clientWidth;
        const isScrolledLeft = table.scrollLeft > 0;
        const isScrolledRight = table.scrollLeft < (table.scrollWidth - container.clientWidth);
        
        container.classList.toggle('scrollable', isScrollable);
        container.classList.toggle('scrolled-left', isScrolledLeft);
        container.classList.toggle('scrolled-right', isScrolledRight);
    };
    
    table.addEventListener('scroll', updateScrollIndicators);
    window.addEventListener('resize', updateScrollIndicators);
    updateScrollIndicators();
}

function handleWindowResize() {
    // Update table scroll indicators
    const tableContainers = document.querySelectorAll('.modern-table-container');
    tableContainers.forEach(container => {
        const table = container.querySelector('.modern-table');
        if (table) {
            const event = new Event('scroll');
            table.dispatchEvent(event);
        }
    });
    
    // Update mobile menu state
    if (window.innerWidth > 768) {
        const menu = document.querySelector('.modern-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (menu) menu.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    }
}

/**
 * Export functions for external use
 */
/**
 * Initialize hide empty discount buttons with 3-stage system
 */
function initializeHideEmptyDiscountButtons() {
    // Initialize discount visibility stage (1: hide value=0, 2: hide value>0, 3: show all)
    let discountStage = 1;
    
    const hideAllButton = document.querySelector('.hide-all-empty-discounts');
    if (hideAllButton) {
        // Set initial button state
        updateDiscountButtonState(hideAllButton, discountStage);
        
        hideAllButton.addEventListener('click', function() {
            discountStage = discountStage >= 3 ? 1 : discountStage + 1;
            applyDiscountVisibilityStage(discountStage);
            updateDiscountButtonState(hideAllButton, discountStage);
        });
        
        // Apply initial stage
        applyDiscountVisibilityStage(discountStage);
     }
     
     // Handle search collapse toggle
     const searchCollapse = document.getElementById('searchCollapse');
     if (searchCollapse) {
         searchCollapse.addEventListener('show.bs.collapse', function () {
             const toggleBtn = document.querySelector('[href="#searchCollapse"]');
             if (toggleBtn) {
                 const icon = toggleBtn.querySelector('i');
                 const text = toggleBtn.querySelector('.toggle-text');
                 if (icon) icon.className = 'bi bi-chevron-up me-1';
                 if (text) text.textContent = 'Ocultar';
             }
         });
         
         searchCollapse.addEventListener('hide.bs.collapse', function () {
             const toggleBtn = document.querySelector('[href="#searchCollapse"]');
             if (toggleBtn) {
                 const icon = toggleBtn.querySelector('i');
                 const text = toggleBtn.querySelector('.toggle-text');
                 if (icon) icon.className = 'bi bi-chevron-down me-1';
                 if (text) text.textContent = 'Mostrar';
             }
         });
     }
 }

/**
 * Apply discount visibility based on current stage
 * Stage 1: Hide rows with value = 0 (default)
 * Stage 2: Hide rows with value > 0
 * Stage 3: Show all rows
 */
function applyDiscountVisibilityStage(stage) {
    const discountInputs = document.querySelectorAll('.product-discount');
    
    discountInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        // Check for both desktop table row and mobile card container
        const row = input.closest('tr');
        const mobileCard = input.closest('.cart-product-card');
        const container = row || mobileCard;
        
        if (container) {
            let shouldHide = false;
            
            switch(stage) {
                case 1: // Hide value = 0
                    shouldHide = value === 0;
                    break;
                case 2: // Hide value > 0
                    shouldHide = value > 0;
                    break;
                case 3: // Show all
                    shouldHide = false;
                    break;
            }
            
            if (shouldHide) {
                container.classList.add('fading-out');
                setTimeout(() => {
                    container.style.display = 'none';
                }, 300);
            } else {
                container.classList.remove('fading-out');
                container.style.display = '';
            }
        }
    });
}

/**
 * Update button appearance based on current stage
 */
function updateDiscountButtonState(button, stage) {
    const icon = button.querySelector('i');
    const textNode = button.childNodes[button.childNodes.length - 1];
    
    switch(stage) {
        case 1:
            button.className = 'btn btn-outline-danger btn-sm hide-all-empty-discounts';
            if (icon) icon.className = 'bi bi-eye-slash';
            if (textNode) textNode.textContent = ' Hide Empty (=0)';
            button.title = 'Stage 1: Hide rows with discount = 0';
            break;
        case 2:
            button.className = 'btn btn-outline-warning btn-sm hide-all-empty-discounts';
            if (icon) icon.className = 'bi bi-eye';
            if (textNode) textNode.textContent = ' Hide Discounted (>0)';
            button.title = 'Stage 2: Hide rows with discount > 0';
            break;
        case 3:
            button.className = 'btn btn-outline-success btn-sm hide-all-empty-discounts';
            if (icon) icon.className = 'bi bi-eye';
            if (textNode) textNode.textContent = ' Show All';
            button.title = 'Stage 3: Show all rows';
            break;
    }
}

/**
 * Hide the discount row when value is empty or zero
 */
function hideEmptyDiscountRow(input) {
    // Find the closest table row
    const row = input.closest('tr');
    if (row) {
        // Add fade out animation
        row.style.transition = 'opacity 0.3s ease-out';
        row.style.opacity = '0';
        
        // Hide the row after animation
        setTimeout(() => {
            row.style.display = 'none';
        }, 300);
    }
}

/**
 * Initialize collapse toggle interactions
 */
function initializeCollapseToggles() {
    // Handle lead info collapse toggle
    const leadInfoToggle = document.querySelector('[data-bs-toggle="collapse"][href="#leadInfoCollapse"]');
    const leadInfoCollapse = document.getElementById('leadInfoCollapse');
    
    if (leadInfoToggle && leadInfoCollapse) {
        leadInfoCollapse.addEventListener('show.bs.collapse', function() {
            const icon = leadInfoToggle.querySelector('i');
            const text = leadInfoToggle.querySelector('.toggle-text');
            
            if (icon) {
                icon.className = 'bi bi-chevron-up me-1';
            }
            if (text) {
                text.textContent = 'Ocultar';
            }
            leadInfoToggle.setAttribute('aria-expanded', 'true');
        });
        
        leadInfoCollapse.addEventListener('hide.bs.collapse', function() {
            const icon = leadInfoToggle.querySelector('i');
            const text = leadInfoToggle.querySelector('.toggle-text');
            
            if (icon) {
                icon.className = 'bi bi-chevron-down me-1';
            }
            if (text) {
                text.textContent = 'Mostrar';
            }
            leadInfoToggle.setAttribute('aria-expanded', 'false');
        });
    }
    
    // Handle config collapse toggle
    const configToggle = document.querySelector('[data-bs-toggle="collapse"][href="#configCollapse"]');
    const configCollapse = document.getElementById('configCollapse');
    
    if (configToggle && configCollapse) {
        configCollapse.addEventListener('show.bs.collapse', function() {
            const icon = configToggle.querySelector('i');
            const text = configToggle.querySelector('.toggle-text');
            
            if (icon) {
                icon.className = 'bi bi-chevron-up me-1';
            }
            if (text) {
                text.textContent = 'Ocultar';
            }
            configToggle.setAttribute('aria-expanded', 'true');
        });
        
        configCollapse.addEventListener('hide.bs.collapse', function() {
            const icon = configToggle.querySelector('i');
            const text = configToggle.querySelector('.toggle-text');
            
            if (icon) {
                icon.className = 'bi bi-chevron-down me-1';
            }
            if (text) {
                text.textContent = 'Mostrar';
            }
            configToggle.setAttribute('aria-expanded', 'false');
        });
    }
    
    // Handle order summary collapse toggle
    const orderSummaryToggle = document.querySelector('[data-bs-toggle="collapse"][href="#orderSummaryCollapse"]');
    const orderSummaryCollapse = document.getElementById('orderSummaryCollapse');
    
    if (orderSummaryToggle && orderSummaryCollapse) {
        orderSummaryCollapse.addEventListener('show.bs.collapse', function() {
            const icon = orderSummaryToggle.querySelector('i');
            const text = orderSummaryToggle.querySelector('.toggle-text');
            
            if (icon) {
                icon.className = 'bi bi-chevron-up me-1';
            }
            if (text) {
                text.textContent = 'Ocultar';
            }
            orderSummaryToggle.setAttribute('aria-expanded', 'true');
        });
        
        orderSummaryCollapse.addEventListener('hide.bs.collapse', function() {
            const icon = orderSummaryToggle.querySelector('i');
            const text = orderSummaryToggle.querySelector('.toggle-text');
            
            if (icon) {
                icon.className = 'bi bi-chevron-down me-1';
            }
            if (text) {
                text.textContent = 'Mostrar';
            }
            orderSummaryToggle.setAttribute('aria-expanded', 'false');
        });
    }
}

/**
 * Hide collapsible sections on page load
 */
function hideCollapsibleSectionsOnLoad() {
    // Add a small delay to ensure DOM is fully loaded and Bootstrap is initialized
    setTimeout(() => {
        // List of collapsible section IDs to hide on page load
        const sectionsToHide = ['leadInfoCollapse', 'configCollapse', 'searchCollapse', 'orderSummaryCollapse'];
        
        sectionsToHide.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            const toggle = document.querySelector(`[data-bs-toggle="collapse"][href="#${sectionId}"]`);
            
            if (section && toggle) {
                // Force hide the section by removing show class and setting display none temporarily
                section.classList.remove('show');
                section.style.display = 'none';
                
                // Reset display after a brief moment to allow Bootstrap to take control
                setTimeout(() => {
                    section.style.display = '';
                }, 50);
                
                // Update toggle button state
                const icon = toggle.querySelector('i');
                const text = toggle.querySelector('.toggle-text');
                
                if (icon) {
                    icon.className = 'bi bi-chevron-down me-1';
                }
                if (text) {
                    text.textContent = 'Mostrar';
                }
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }, 100);
}

window.ModernInteractions = {
    init: initializeModernInteractions,
    updateSelectionCounter,
    validateQuantityInput,
    formatCurrencyInput,
    validateDiscountInput,
    showButtonLoading,
    hideButtonLoading,
    showGlobalLoading,
    hideGlobalLoading,
    hideEmptyDiscountRow
};