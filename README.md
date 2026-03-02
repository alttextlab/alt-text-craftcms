# AI Alt Text Generator for CraftCMS

The **[AI Alt Text Generator](https://alttextlab.com?utm_medium=integration&utm_source=craftcms&utm_campaign=github) for CraftCMS** is a plugin that automatically creates high-quality, descriptive alt text for images in your CraftCMS media library. Powered by the [AltTextLab](https://alttextlab.com) API, it uses advanced AI to generate accessible and SEO-friendly image descriptions, saving you time and improving your website’s accessibility compliance.

**[GitHub Repository](https://github.com/alttextlab/alt-text-craftcms)** - **[Plugin Store](https://plugins.craftcms.com/alt-text-lab)**

## Features

- **Automatic alt text generation** – Instantly generate alt text for newly uploaded images without any manual work.
- **Bulk alt text generation for your media library** – Quickly create alt text for all existing images in your CraftCMS media library in one go.
- **Multi-language support** – Generate alt text in over 130 languages.
- **Multi-site support** – Automatically generate alt text in each site's language. One API call per unique language, with results shared across sites that use the same language.
- **Easy setup & Free trial** – Simple installation process with a free trial so you can start improving accessibility right away.  

## Requirements

This plugin requires:
- Craft CMS 5.0.0 or later
- PHP 8.2 or later
- AltTextLab API key (You can create one [here](https://app.alttextlab.com/settings/api-keys?utm_medium=integration&utm_source=craftcms&utm_campaign=github))

## Installation

You can install this plugin from the [Plugin Store](https://plugins.craftcms.com/alt-text-lab) or with Composer.

## Setup Alt Text Generator

1. Make sure the plugin is installed in your CraftCMS.
2. If you don’t have an AltTextLab account yet, create one [here](https://www.alttextlab.com/?utm_medium=integration&utm_source=craftcms&utm_campaign=github).
3. Go to the [API Keys page](https://app.alttextlab.com/settings/api-keys?utm_medium=integration&utm_source=craftcms&utm_campaign=github) and create a new API key by clicking **Create API Key**. Copy the generated key.
4. In CraftCMS, open the plugin settings (**AltTextLab → Settings**) and paste the API key into the **AltTextLab API Key** field. Save the changes.
5. The plugin is ready to use! 

## How to use Alt Text Generator

- **Automatic Alt Text Generation**  
  Enable the **Auto-generate alt text for new assets** switch in the plugin settings. Once enabled, the plugin will automatically generate alt text for all newly uploaded images.

- **Bulk Generation for the Entire Media Library**  
  To generate alt text for all images in your CraftCMS media library, go to **AltTextLab → Bulk Generation** and click **Run generation**.  
  This will create a new Job, which you can track in **Queue Manager** or under **AltTextLab → Bulk Generation History**.

- **Bulk Generation for Selected Media**  
  If you only want to generate alt text for specific images, select them in the **Assets** tab, open the menu, and choose **Bulk generation**.  

## Settings

All settings are available under **AltTextLab → Settings**.

### Auto-generate alt text for new assets

When enabled, the plugin automatically generates alt text for every newly uploaded image. Generation takes a few seconds, so the alt text may not appear immediately — simply check again after a short delay.

### Automatically use each site's language (multi-site)

Enable this switch to generate alt text in the language configured for each Craft site (under **Settings → Sites**) instead of using a single language.

When this mode is active:

1. The plugin reads the language from each site's locale (e.g. `en-US`, `de`, `fr`).
2. Sites that share the same language are grouped together — only one API call is made per unique language, keeping usage efficient.
3. Alt text is saved separately for every site, so each localized version of an asset gets its own description.

**Important:** The alt text field (native `alt` or your custom field) must have its **Translation Method** set to *Translate for each site* or *Translate for each language*. You can configure this in **Settings → Assets** (for the native `alt` field) or **Settings → Fields** (for custom fields). The plugin will show a warning if the field is not translatable.

### Language

Choose a language for generated alt text from the **Language** dropdown (130+ languages available). When the multi-site switch above is off, every asset receives alt text in this language regardless of which site it belongs to. When the multi-site switch is on, this dropdown is ignored.

### Model type

Select the style of generated descriptions:

- **Use account default** — uses the style configured in your AltTextLab account.
- **Descriptive** — rich, detailed descriptions.
- **Neutral** — balanced tone without subjective language.
- **Matter-of-fact** — concise, objective descriptions.
- **Minimal** — shortest possible descriptions.

### Field for Alt Text

Choose which field the plugin writes alt text to. By default, the native **Alt** field is used. You can also select any **Plain Text** custom field attached to your Asset volume's field layout.

If you change this setting after assets have already been processed, use **Bulk Generation** to re-generate alt text into the new field.

### This site is reachable over the public internet

Once your site is publicly accessible and each image can be reached over the internet, we highly recommend enabling this setting. It makes the generation process faster and ensures that images are not transferred or processed on our servers.

If you are developing locally or your site is not publicly accessible, keep this setting disabled.

### Disable volumes (Exclude Specific Images)

Select one or more Asset Volumes to exclude from alt text generation. Assets stored in disabled volumes will be skipped during both automatic and bulk generation.

### Exclude Images by regex

For finer control, you can exclude files by matching their full file system path with a regular expression. Define the following environment variable in your `.env` file:

```bash
# Examples (pick one and adjust to your needs)
ALT_TEXT_LAB_EXCLUDE_REGEX=~/(thumbnails|icons)/~i
# Or exclude anything in /uploads/tmp/
# ALT_TEXT_LAB_EXCLUDE_REGEX=~/uploads/tmp/~
# Or exclude all SVGs inside "branding" folder
# ALT_TEXT_LAB_EXCLUDE_REGEX=~branding/.+\.svg$~i
```

Notes:
- The pattern is applied against the asset's full local path, which includes the volume file system path, optional `subpath`, and the asset path.
- You may provide a PCRE pattern with delimiters (e.g., `~/pattern/~i`). If you omit delimiters, the plugin will attempt to wrap your pattern automatically.
- Regex-based exclusions apply to both automatic generation and bulk operations.

## Supported File Types

The Alt Text Generator for CraftCMS supports the following image formats:

- **JPEG / JPG**
- **PNG**
- **WebP**
- **AVIF**
- **SVG**

## Troubleshooting

- The plugin includes a **AltTextLab → Logs** page where you can view any errors related to alt text generation.
- Alt text generation for bulk operations is processed through Craft's queue system, so if generation seems to be taking a long time, check the **Queue Manager**.
- All errors should also be logged, review your `queue.log` files for more details.  

## Support & Feedback

If you enjoy the plugin, please [leave a review on the Plugin Store](https://plugins.craftcms.com/alt-text-lab/reviews) — it helps other Craft developers discover it.

Found a bug or have an idea for improvement? [Open an issue on GitHub](https://github.com/alttextlab/alt-text-craftcms/issues).

For general questions or help getting started:

- **Website:** [https://www.alttextlab.com](https://www.alttextlab.com?utm_medium=integration&utm_source=craftcms&utm_campaign=github)
- **Support Email:** contact@alttextlab.com
- [Contact us](https://www.alttextlab.com/contact?utm_medium=integration&utm_source=craftcms&utm_campaign=github)
