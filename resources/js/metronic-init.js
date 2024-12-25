// resources/js/metronic-init.js
export function initializeMetronic() { 
    // Initialize KTApp
    if (window.KTApp) { 
        window.KTApp.init();
    }

    // Initialize Metronic Menu
    if (typeof KTMenu !== 'undefined') { 
        // Initialize the aside menu specifically
        const asideMenu = document.querySelector('#kt_aside_menu');
        if (asideMenu) {
            try {
                new KTMenu(asideMenu, {
                    // Submenu setup
                    submenu: {
                        desktop: {
                            default: 'dropdown',
                            state: {
                                body: 'aside-minimize',
                                mode: 'dropdown'
                            }
                        },
                        tablet: 'accordion', // Mode on tablet devices
                        mobile: 'accordion'  // Mode on mobile devices
                    },
                    // Accordion submenu settings
                    accordion: {
                        expandAll: false // Allow having multiple expanded accordions in the menu
                    }
                });
                console.log('Aside menu initialized successfully');
            } catch (error) { 
            }
        } else {
            console.warn('Aside menu element not found');
        }
    } else {
        console.warn('KTMenu is not defined');
    }

    // Initialize hover functionality for menu items
    const menuItems = document.querySelectorAll('.menu-item-submenu');
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (window.KTUtil && window.KTUtil.isDesktopDevice()) {
                this.classList.add('menu-item-hover');
            }
        });
        
        item.addEventListener('mouseleave', function() {
            if (window.KTUtil && window.KTUtil.isDesktopDevice()) {
                this.classList.remove('menu-item-hover');
            }
        });
    });

    // Initialize tooltips if needed
    if (window.KTUtil && window.bootstrap) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
}

// Enhanced debug function
export function debugMetronic() {
     
    
    const asideMenu = document.querySelector('#kt_aside_menu');
    
    if (asideMenu) {
      
    }
    
     
}