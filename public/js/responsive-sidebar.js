/**
 * 🚀 Enhanced Responsive Sidebar System
 * Smart sidebar that adapts to all screen sizes
 */

class ResponsiveSidebar {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.mainContent = document.getElementById('main-content');
        this.sidebarToggle = document.getElementById('sidebar-toggle');
        this.sidebarTexts = document.querySelectorAll('.sidebar-text');
        this.mobileOverlay = document.getElementById('mobile-overlay');
        
        this.sidebarExpanded = true;
        this.currentBreakpoint = this.getBreakpoint();
        
        this.init();
    }
    
    // Get current screen breakpoint
    getBreakpoint() {
        const width = window.innerWidth;
        if (width >= 1024) return 'desktop';
        if (width >= 768) return 'tablet';
        return 'mobile';
    }
    
    // Initialize the sidebar system
    init() {
        this.updateSidebarState();
        this.attachEventListeners();
        
        // Close mobile sidebar by default
        if (this.currentBreakpoint === 'mobile') {
            this.closeMobileSidebar();
        }
        
        console.log('🚀 Responsive Sidebar initialized for:', this.currentBreakpoint);
    }
    
    // Smart toggle function
    toggleSidebar() {
        const breakpoint = this.getBreakpoint();
        
        if (breakpoint === 'mobile') {
            // Mobile: Show/Hide sidebar with overlay
            if (this.sidebar.classList.contains('sidebar-mobile-visible')) {
                this.closeMobileSidebar();
            } else {
                this.openMobileSidebar();
            }
        } else {
            // Desktop/Tablet: Expand/Collapse sidebar
            this.sidebarExpanded = !this.sidebarExpanded;
            this.updateSidebarState();
            
            // Save preference
            localStorage.setItem('sidebarExpanded', this.sidebarExpanded);
        }
    }
    
    // Update sidebar state based on current settings
    updateSidebarState() {
        const breakpoint = this.getBreakpoint();
        
        // Clear all classes first
        this.sidebar.classList.remove('sidebar-expanded', 'sidebar-collapsed', 'sidebar-mobile-visible');
        this.mainContent.classList.remove('content-expanded', 'content-collapsed');
        
        if (breakpoint === 'mobile') {
            // Mobile: Always full width when visible, hidden by default
            this.sidebar.classList.add('sidebar-expanded');
            // Content always full width on mobile
        } else {
            // Desktop/Tablet: Expandable sidebar
            if (this.sidebarExpanded) {
                this.sidebar.classList.add('sidebar-expanded');
                this.mainContent.classList.add('content-expanded');
                this.showSidebarTexts();
            } else {
                this.sidebar.classList.add('sidebar-collapsed');
                this.mainContent.classList.add('content-collapsed');
                this.hideSidebarTexts();
            }
        }
    }
    
    // Mobile sidebar functions
    openMobileSidebar() {
        this.sidebar.classList.add('sidebar-mobile-visible');
        this.mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Add animation class
        this.sidebar.style.transform = 'translateX(0)';
    }
    
    closeMobileSidebar() {
        this.sidebar.classList.remove('sidebar-mobile-visible');
        this.mobileOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Add animation class
        this.sidebar.style.transform = 'translateX(100%)';
    }
    
    // Show/Hide sidebar texts
    showSidebarTexts() {
        this.sidebarTexts.forEach(text => {
            text.style.display = 'block';
            text.style.opacity = '1';
        });
    }
    
    hideSidebarTexts() {
        this.sidebarTexts.forEach(text => {
            text.style.display = 'none';
            text.style.opacity = '0';
        });
    }
    
    // Handle window resize
    handleResize() {
        const newBreakpoint = this.getBreakpoint();
        
        if (newBreakpoint !== this.currentBreakpoint) {
            console.log('📱 Breakpoint changed:', this.currentBreakpoint, '→', newBreakpoint);
            this.currentBreakpoint = newBreakpoint;
            
            // Close mobile sidebar if switching away from mobile
            if (newBreakpoint !== 'mobile') {
                this.closeMobileSidebar();
            }
            
            // Update sidebar state for new breakpoint
            this.updateSidebarState();
        }
    }
    
    // Attach event listeners
    attachEventListeners() {
        // Sidebar toggle
        this.sidebarToggle?.addEventListener('click', () => this.toggleSidebar());
        
        // Mobile overlay
        this.mobileOverlay?.addEventListener('click', () => this.closeMobileSidebar());
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.getBreakpoint() === 'mobile' && 
                this.sidebar.classList.contains('sidebar-mobile-visible')) {
                this.closeMobileSidebar();
            }
        });
        
        // Window resize
        window.addEventListener('resize', () => this.handleResize());
        
        // Load saved preference
        const savedExpanded = localStorage.getItem('sidebarExpanded');
        if (savedExpanded !== null) {
            this.sidebarExpanded = savedExpanded === 'true';
        }
    }
    
    // Public methods for external use
    expand() {
        if (this.getBreakpoint() !== 'mobile') {
            this.sidebarExpanded = true;
            this.updateSidebarState();
        }
    }
    
    collapse() {
        if (this.getBreakpoint() !== 'mobile') {
            this.sidebarExpanded = false;
            this.updateSidebarState();
        }
    }
    
    isExpanded() {
        return this.sidebarExpanded;
    }
    
    getCurrentBreakpoint() {
        return this.currentBreakpoint;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure all elements are loaded
    setTimeout(() => {
        window.responsiveSidebar = new ResponsiveSidebar();
    }, 100);
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ResponsiveSidebar;
}