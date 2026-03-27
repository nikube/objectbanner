# Object Banner

Floating navigation banner for Dolibarr that links Proposals, Orders, Invoices, and Shipments on document cards.

## Features

- **Floating banner** on Proposal, Order, Invoice, and Shipment detail pages
- **Document chain visualization** — see all linked documents at a glance
- **Clickable links** — jump directly to related documents
- **Status badges** — see document status without leaving the page
- **Sticky positioning** — banner follows you while scrolling
- **Configurable** — enable/disable the banner per document type
- **Responsive** — works on mobile devices
- **Bilingual** — English and French

## Requirements

- Dolibarr 16.0+
- PHP 7.1+

## Installation

1. Copy the `objectbanner/` directory to your Dolibarr `htdocs/custom/` folder
2. Activate the module from **Home > Setup > Modules**

## Configuration

Go to **Module settings** to toggle which document types show in the banner:
- Proposals
- Orders
- Invoices
- Shipments

## DMM Compatible

This module includes a `dmm.json` manifest and can be managed via [DoliModuleManager](https://github.com/nikube/DMM).

## License

GPL-3.0-or-later

## Author

Nicolas - [AnatoleConseil.com](https://anatoleconseil.com/) — nz@anatoleconseil.com
