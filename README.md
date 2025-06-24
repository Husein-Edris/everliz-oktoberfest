# 🍺 Oktoberfest VIP Booking

A premium WordPress plugin designed specifically for Oktoberfest VIP experiences and event bookings. Built with modern web technologies and seamless Elementor integration, this plugin delivers a sophisticated booking system with intuitive user experience and comprehensive admin management.

## ✨ Key Features

### 🔍 **Advanced Search Form Widget**
- **Interactive Date Picker**: Custom calendar with admin-defined date ranges
- **Smart Tent Selection**: Dynamic dropdown with real-time availability
- **Secure URL Parameters**: Base64 encoded parameters for clean URLs
- **Modern UI Elements**: SVG icons and responsive design
- **Quick Search**: Instant filtering and validation

### 📝 **Comprehensive Booking Form Widget**  
- **Visual Tent Gallery**: Interactive tent selection with high-quality images
- **Smart Date Validation**: Respects backend-configured availability periods
- **Session Management**: Day/Evening session selection with time details
- **Complete Contact Collection**: Name, email, phone, company fields
- **Form Validation**: Real-time client-side and server-side validation
- **AJAX Submission**: Seamless form processing without page refresh
- **Thank You Redirection**: Configurable success page routing

### ⚙️ **Powerful Admin Dashboard**
- **Multi-Year Date Management**: Configure event dates for multiple years
- **Page Integration**: Easy booking and thank you page selection  
- **API Configuration**: External service integration capabilities
- **Settings Persistence**: Reliable data storage across all admin tabs
- **Tabbed Interface**: Clean, organized admin experience

## 🔧 Technical Requirements

| Component | Minimum Version | Recommended |
|-----------|----------------|-------------|
| **WordPress** | 5.0+ | 6.0+ |
| **Elementor** | 3.0+ | 3.15+ |
| **PHP** | 7.4+ | 8.1+ |
| **MySQL** | 5.6+ | 8.0+ |

## 🚀 Quick Installation

### Method 1: Direct Upload
1. Download the plugin files from this repository
2. Upload the `everliz-oktoberfest` folder to `/wp-content/plugins/`
3. Run `npm install && npm run build` in the plugin directory
4. Activate through WordPress admin → Plugins
5. Configure settings under **Oktoberfest** menu

### Method 2: Git Clone (Developers)
```bash
cd wp-content/plugins/
git clone [repository-url] everliz-oktoberfest
cd everliz-oktoberfest
npm install
npm run build
```

## ⚡ Quick Setup Guide

### 1️⃣ **Initial Configuration**
Navigate to **WordPress Admin → Oktoberfest**

**General Settings:**
- Select your main booking page (or create new)
- Choose thank you/confirmation page
- Save settings

**Date Ranges:**
- Add Oktoberfest dates for each year (2024, 2025, etc.)
- Set precise start and end dates
- Configure multiple year ranges

**API Settings (Optional):**
- Enter external API credentials
- Configure booking service endpoints
- Test API connections

### 2️⃣ **Adding Widgets**

**Search Form Widget:**
1. Edit any page with Elementor
2. Search for "Oktoberfest VIP Search" widget
3. Drag to desired location
4. Customize styling and labels

**Booking Form Widget:**
1. Edit your booking page with Elementor  
2. Add "Oktoberfest VIP Booking Form" widget
3. Form automatically inherits search parameters
4. Customize tent gallery and form fields

### 3️⃣ **Testing the Flow**
1. Visit search form page
2. Select date and preferences
3. Submit search → redirects to booking page
4. Complete booking → redirects to thank you page

## 🎨 Customization & Styling

### **SCSS Development**
The plugin uses a modular SCSS architecture for easy customization:

```
assets/scss/
├── _variables.scss           # Colors, fonts, spacing
├── _mixins.scss             # Reusable CSS patterns  
├── main.scss                # Frontend styles
├── admin.scss               # Admin interface styles
└── widgets/
    ├── _search-form.scss    # Search widget styles
    ├── _booking-form.scss   # Booking form styles
    ├── _calendar.scss       # Calendar component
    └── _error-styles.scss   # Validation & errors
```

### **Build Commands**
```bash
# Install dependencies
npm install

# One-time build
npm run build

# Watch for changes (development)
npm run sass
```

### **Color Customization**
Edit `assets/scss/_variables.scss` to customize the color scheme:
```scss
$primary-color: #f3a735;      // Oktoberfest gold
$highlight-color: #f3a735;    // Accent color
$dark-bg: #111827;            // Dark backgrounds
$error-color: #e74c3c;        // Error states
```

## 📁 Plugin Architecture

```
everliz-oktoberfest/
├── 📄 everliz-oktoberfest.php    # Main plugin file
├── 📂 assets/
│   ├── 🎨 css/                   # Compiled stylesheets
│   ├── ⚡ js/                    # JavaScript files
│   ├── 🎨 scss/                  # Source SCSS files
│   └── 🖼️ images/                # SVG icons & images
├── 📂 includes/
│   └── 🔗 class-api-handler.php  # API integration
├── 📂 widgets/
│   ├── 🔍 search-form-widget.php
│   └── 📝 booking-form-widget.php
├── 📄 package.json               # Node.js dependencies
├── 📄 .gitignore                 # Git ignore rules
└── 📖 README.md                  # Documentation
```

## 🔌 Integration & APIs

### **Elementor Integration**
- Custom widget categories
- Drag-and-drop interface
- Live preview capabilities
- Responsive controls

### **WordPress Hooks**
```php
// Custom actions available
do_action('oktoberfest_before_booking_submit', $booking_data);
do_action('oktoberfest_after_booking_success', $booking_id);

// Filters for customization  
apply_filters('oktoberfest_tent_data', $tents);
apply_filters('oktoberfest_date_ranges', $dates);
```

### **REST API Endpoints**
```
POST /wp-admin/admin-ajax.php
├── action=oktoberfest_submit_booking
├── action=oktoberfest_test_api_connection
└── action=oktoberfest_get_availability
```

## 🐛 Troubleshooting

### **Common Issues**

**Problem**: Styles not loading  
**Solution**: Run `npm run build` and clear cache

**Problem**: Settings not saving  
**Solution**: Check file permissions and PHP error logs

**Problem**: Calendar not showing dates  
**Solution**: Verify date ranges are configured in admin

**Problem**: Form submission fails  
**Solution**: Check AJAX URL and nonce verification

### **Debug Mode**
Enable WordPress debug mode for detailed error logging:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🚀 Performance Features

- **Optimized Assets**: Compressed CSS/JS files
- **Lazy Loading**: Calendar loads dates on demand
- **AJAX Processing**: No page refreshes during booking
- **Caching Ready**: Compatible with WordPress caching plugins
- **Mobile Optimized**: Responsive design for all devices

## 📞 Support & Documentation

- **Issues**: Report bugs via GitHub issues
- **Documentation**: Comprehensive inline code documentation
- **Author**: [Edris Husein](https://edrishusein.com)
- **Version**: 1.5.0

## 📜 License

This plugin is licensed under **GPL v2 or later**.

## 🏆 Credits

- **Development**: Edris Husein
- **Design**: Custom Oktoberfest-themed UI
- **Icons**: Custom SVG icon set
- **Framework**: WordPress + Elementor integration 