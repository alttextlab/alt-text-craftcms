1.5.2 - 2026-09-09
Fixed "Setting unknown property: ...::siteId" job failures on fresh installs where the siteId column was missing from the plugin tables
Install migration now creates the siteId column and index, and an added safety-net migration back-fills it on existing installs

1.5.1 - 2026-06-08
Fixed a bug with excessive API requests

1.5.0 - 2026-05-26
Added Craft Commerce integration — when an image is linked to a product or variant, product context (name, brand, color, material) is automatically sent to the API to generate more accurate alt text.
Configurable via the new Commerce section in plugin settings: choose whether to resolve the product name, color, and material from the product or variant level, and specify custom field handles for brand, color, and material.

1.4.1 - 2026-03-16
Added support for zh-Hans (Simplified Chinese) and zh-Hant (Traditional Chinese).
zh is now used as a fallback language for Chinese dialects that are not explicitly supported.

1.4.0 - 2026-03-02
Added multi-site support — automatically generate alt text in each site's language
Set default language from the primary site on install

1.3.0 - 2025-10-07
Add regex-based exclusion via ALT_TEXT_LAB_EXCLUDE_REGEX to skip assets by path

1.2.1 - 2025-09-25
Minor link fixes

1.2.0 - 2025-09-23
Implemented the ability to ignore specific asset volumes during alt text generation.

1.1.0 - 2025-09-15
fix SQL error when clearing garbage

1.0.3 - 2025-08-12
update readme

1.0.2 - 2025-08-11
small fixes

1.0.1 - 2025-08-10
small fixes

1.0.0 - 2025-08-10
Initial release
AI alt text generator for AltTextLab API
Bulk generation for existing media library images
Queue integration for background processing