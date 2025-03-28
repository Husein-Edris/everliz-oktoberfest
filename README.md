# Oktoberfest VIP Booking

A WordPress plugin that provides an elegant booking system for Oktoberfest VIP experiences. Built with Elementor, this plugin offers a seamless booking experience with modern UI components and robust functionality.

## Features

- **Search Form Widget**: A sleek search interface with:
  - Date picker with custom calendar
  - Tent selection dropdown
  - Base64 encoded URL parameters
  - SVG icons for enhanced UI

- **Booking Form Widget**: A comprehensive booking form including:
  - Interactive tent selection gallery
  - Date picker with valid date ranges
  - Session selection (Day/Evening)
  - Contact information collection
  - Newsletter subscription option

- **Admin Settings**:
  - Configurable date ranges for multiple years
  - Booking page selection
  - API configuration
  - General settings management

## Requirements

- WordPress 5.0 or higher
- Elementor 3.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the `everliz-oktoberfest` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'Oktoberfest' in the admin menu

## Configuration

### General Settings
1. Navigate to WordPress admin → Oktoberfest → General Settings
2. Select or create a booking page
3. Save your settings

### Date Ranges
1. Go to Oktoberfest → Date Ranges
2. Add date ranges for each Oktoberfest year
3. Configure start and end dates

### API Settings
1. Access Oktoberfest → API Settings
2. Enter your API credentials
3. Configure the API endpoint

## Usage

### Adding the Search Form
1. Edit a page with Elementor
2. Drag the 'Oktoberfest VIP Search' widget
3. Configure widget settings:
   - Date placeholder
   - Location placeholder
   - Button text

### Adding the Booking Form
1. Create or edit your booking page
2. Add the 'Oktoberfest VIP Booking Form' widget
3. The form will automatically handle URL parameters from the search form

## Development

### Styles
The plugin uses SCSS for styling:
```
assets/scss/
├── _variables.scss    # Global variables
├── _mixins.scss      # Utility mixins
├── main.scss         # Main stylesheet
└── widgets/
    ├── _search-form.scss
    ├── _booking-form.scss
    └── _calendar.scss
```

To compile SCSS:
1. Install dependencies: `npm install`
2. Run build: `npm run build`

### File Structure
```
everliz-oktoberfest/
├── assets/
│   ├── css/
│   ├── js/
│   ├── scss/
│   └── images/
├── widgets/
│   ├── search-form-widget.php
│   └── booking-form-widget.php
└── everliz-oktoberfest.php
```

## Support

For support inquiries, please contact [edrishusein.com](https://edrishusein.com)

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Created by: Edris Husein
- Version: 1.5
- Icons by: Custom SVG designs 