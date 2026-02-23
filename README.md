# Everliz Oktoberfest VIP Booking

A WordPress plugin that provides an Elementor-based booking flow for Oktoberfest VIP experiences. It includes:

- A **search widget** (date + preferences) that forwards selection to a booking page
- A **booking widget** (tent/session selection + contact details) with AJAX submission
- An **admin settings area** for date ranges, page mapping, and optional external API configuration

## Features

### Search widget

- Date picker constrained to **admin-configured availability ranges**
- Tent/preference selection with validation
- URL parameter forwarding for a clean booking flow (encoded parameters)
- Responsive UI with SVG icon assets

### Booking widget

- Visual tent selection gallery
- Day/Evening session selection
- Contact details collection (name, email, phone, company)
- Client-side + server-side validation
- AJAX submission and configurable success/thank-you redirect

### Admin settings

- Multi-year date range management (e.g., 2024/2025/…)
- Booking page and thank-you page selection
- Optional external API credentials/endpoints with connection testing

## Requirements

| Component | Minimum | Recommended |
|---|---:|---:|
| WordPress | 5.0 | 6.x |
| Elementor | 3.0 | 3.15+ |
| PHP | 7.4 | 8.1+ |
| MySQL | 5.6 | 8.0 |

Asset builds require a local Node.js + npm environment (see `package.json`).

## Installation

### Option A: Upload plugin

1. Copy the `everliz-oktoberfest` folder into `wp-content/plugins/`.
2. In the plugin directory, install and build assets:

```bash
npm install
npm run build
```

3. Activate the plugin in **WordPress Admin → Plugins**.
4. Configure settings under **WordPress Admin → Oktoberfest**.

### Option B: Clone repository (developers)

```bash
cd wp-content/plugins/
git clone <repository-url> everliz-oktoberfest
cd everliz-oktoberfest
npm install
npm run build
```

## Setup (WordPress + Elementor)

### 1) Configure plugin settings

Go to **WordPress Admin → Oktoberfest**.

- **General**: select the booking page and thank-you/confirmation page.
- **Date ranges**: add availability ranges for each event year.
- **API (optional)**: enter credentials/endpoints and test connectivity.

### 2) Add Elementor widgets

- On any page, add the **Oktoberfest VIP Search** widget.
- On your booking page, add the **Oktoberfest VIP Booking Form** widget.

### 3) Verify end-to-end flow

1. Open the page containing the search widget
2. Select a date and preferences
3. Submit → you should be redirected to the booking page with the selection applied
4. Complete the booking → you should be redirected to the configured thank-you page

## Development

### Build commands

```bash
# Install dependencies
npm install

# One-time build (production assets)
npm run build

# Watch SCSS (development)
npm run sass
```

### Styling (SCSS)

SCSS source files live under `assets/scss/`:

```
assets/scss/
├── _variables.scss
├── _mixins.scss
├── main.scss
├── admin.scss
└── widgets/
    ├── _search-form.scss
    ├── _booking-form.scss
    ├── _calendar.scss
    └── _error-styles.scss
```

To adjust the color palette, update `assets/scss/_variables.scss`.

## Architecture

```
everliz-oktoberfest/
├── everliz-oktoberfest.php
├── assets/
│   ├── css/        # compiled
│   ├── js/
│   ├── scss/       # source
│   └── images/
├── includes/
│   └── class-api-handler.php
├── widgets/
│   ├── search-form-widget.php
│   └── booking-form-widget.php
└── package.json
```

## Integration

### Actions and filters

```php
do_action('oktoberfest_before_booking_submit', $booking_data);
do_action('oktoberfest_after_booking_success', $booking_id);

apply_filters('oktoberfest_tent_data', $tents);
apply_filters('oktoberfest_date_ranges', $dates);
```

### AJAX endpoints

Requests are handled via `admin-ajax.php`:

```
POST /wp-admin/admin-ajax.php
  action=oktoberfest_submit_booking
  action=oktoberfest_test_api_connection
  action=oktoberfest_get_availability
```

## Troubleshooting

- **Styles not loading**: run `npm run build`, then clear any caching plugin/CDN caches.
- **Settings not saving**: verify user permissions, then check PHP logs for errors.
- **Calendar shows no dates**: confirm date ranges are configured in the admin settings.
- **Submission fails**: verify the AJAX URL, nonce validation, and server error logs.

### Enable debug logging

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

- Report issues via your project issue tracker.
- **Author**: [Edris Husein](https://edrishusein.com)
- **Version**: 1.5.0

## License

GPL v2 or later.