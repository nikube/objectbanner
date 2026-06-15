# CHANGELOG MODULE OBJECTBANNER FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## 0.3

- Fix: shipments (expeditions) were never detected in the document chain. Dolibarr's Expedition object uses the element type `shipping`, but the module compared against `expedition`, so the Shipment section stayed empty and the banner did not appear on shipment cards. All element-type checks now use `shipping`.

## 0.2

- Add light padding and rounded bottom corners to banner

## 0.1

Initial release
- Floating navigation banner on Proposal, Order, and Invoice cards
- Automatic display of linked document chain (Proposal > Order > Invoice)
- Direct clickable links to related documents
- Document status display with badges
- Sticky positioning for easy access while scrolling
- Responsive design for mobile devices
- Full bilingual support (English & French)
- Admin setup and about pages
