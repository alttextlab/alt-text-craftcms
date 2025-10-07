# AI Alt Text Generator for CraftCMS

The **[AI Alt Text Generator](https://alttextlab.com?utm_medium=integration&utm_source=craftcms&utm_campaign=github) for CraftCMS** is a plugin that automatically creates high-quality, descriptive alt text for images in your CraftCMS media library. Powered by the [AltTextLab](https://alttextlab.com) API, it uses advanced AI to generate accessible and SEO-friendly image descriptions, saving you time and improving your website’s accessibility compliance.

**[GitHub Repository](https://github.com/alttextlab/alt-text-craftcms)** - **[Plugin Store](https://plugins.craftcms.com/alt-text-lab)**

## Features

- **Automatic alt text generation** – Instantly generate alt text for newly uploaded images without any manual work.
- **Bulk alt text generation for your media library** – Quickly create alt text for all existing images in your CraftCMS media library in one go.
- **Multi-language support** – Generate alt text in over 130 languages.
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
  *Generation takes a few seconds, so the alt text may not appear immediately — simply check again after a short delay.*

- **Bulk Generation for the Entire Media Library**  
  To generate alt text for all images in your CraftCMS media library, go to **AltTextLab → Bulk Generation** and click **Run generation**.  
  This will create a new Job, which you can track in **Queue Manager** or under **AltTextLab → Bulk Generation History**.

- **Bulk Generation for Selected Media**  
  If you only want to generate alt text for specific images, select them in the **Assets** tab, open the menu, and choose **Bulk generation**.  

## This site is reachable over the public internet

Once your site is publicly accessible and each image can be reached over the internet, we highly recommend enabling the **This site is reachable over the public internet** setting.  
Enabling this will make the generation process faster and ensure that images are not transferred or processed on our servers.

If you are developing locally or your site is not publicly accessible, keep this setting disabled.  

## Supported File Types

The Alt Text Generator for CraftCMS supports the following image formats:

- **JPEG / JPG**
- **PNG**
- **WebP**
- **AVIF**
- **SVG**

## Exclude Specific Images

You can prevent certain assets from being processed in two ways:

- Disable entire Asset Volumes in plugin settings (recommended)
- Use a regex rule in your `.env` to exclude files by path

### Method 1: Disable Asset Volumes (Recommended)

1. In Craft, go to `AltTextLab → Settings`.
2. Find the "Disable volumes" option.
3. Select one or more volumes to exclude. Assets stored in these volumes will be skipped during both automatic and bulk alt text generation.

This method is simple, safe, and easy to maintain.

### Method 2: Advanced exclusion via regex (Environment variable)

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

## Troubleshooting

- The plugin includes a **AltTextLab → Logs** page where you can view any errors related to alt text generation.
- Alt text generation for bulk operations is processed through Craft's queue system, so if generation seems to be taking a long time, check the **Queue Manager**.
- All errors should also be logged, review your `queue.log` files for more details.  

## Support & Feedback

If you encounter any issues, have feature requests, or need help getting started, feel free to reach out:

- **Website:** [https://www.alttextlab.com](https://www.alttextlab.com?utm_medium=integration&utm_source=craftcms&utm_campaign=github)
- **Support Email:** contact@alttextlab.com
- [Contact us](https://www.alttextlab.com/contact?utm_medium=integration&utm_source=craftcms&utm_campaign=github)
