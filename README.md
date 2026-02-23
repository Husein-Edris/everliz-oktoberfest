# Everliz Oktoberfest VIP Booking

A WordPress plugin that provides an Elementor-based VIP booking flow for Oktoberfest: a search widget, a booking widget, and admin settings for date ranges and page mapping.

## Requirements

| Component | Minimum | Recommended |
|---|---:|---:|
| WordPress | 5.0 | 6.x |
| Elementor | 3.0 | 3.15+ |
| PHP | 7.4 | 8.1+ |
| MySQL | 5.6 | 8.0 |

Asset builds require a local Node.js + npm environment (see `package.json`).

## Installation

1. Copy the `everliz-oktoberfest` folder into `wp-content/plugins/`.
2. In the plugin directory, install dependencies and build assets:

```bash
npm install
npm run build
```

3. Activate the plugin in **WordPress Admin → Plugins**.

## Setup (WordPress + Elementor)

Go to **WordPress Admin → Oktoberfest**.

- Select the booking page and thank-you/confirmation page
- Add availability date ranges (per year)
- Configure API settings (optional)

In Elementor:

- On any page, add the **Oktoberfest VIP Search** widget.
- On your booking page, add the **Oktoberfest VIP Booking Form** widget.

## Development

```bash
npm install
npm run build
npm run sass
```

## Support

- Report issues via your project issue tracker.
- **Author**: [Edris Husein](https://edrishusein.com)
- **Version**: 1.5.0

## License

GPL v2 or later.