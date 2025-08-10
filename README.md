# AI Alt Text Generator for CraftCMS

The **[AI Alt Text Generator](https://alttextlab.com?utm_source=craftcms-directory&utm_medium=craftcms-overview-block) for CraftCMS** is a plugin that automatically creates high-quality, descriptive alt text for images in your CraftCMS media library. Powered by the [AltTextLab](https://alttextlab.com) API, it uses advanced AI to generate accessible and SEO-friendly image descriptions, saving you time and improving your website’s accessibility compliance.

## Features

- **Automatic alt text generation** – Instantly generate alt text for newly uploaded images without any manual work.
- **Bulk alt text generation for your media library** – Quickly create alt text for all existing images in your CraftCMS media library in one go.
- **Multi-language support** – Generate alt text in over 130 languages.
- **Easy setup & Free trial** – Simple installation process with a free trial so you can start improving accessibility right away.  

## Requirements

This plugin requires:
- Craft CMS 5.0.0 or later
- PHP 8.2 or later
- AltTextLab API key (You can create one [here](https://app.alttextlab.com/settings/api-keys?utm_source=craftcms-directory&utm_medium=craftcms-requirements-block))

## Installation

You can install this plugin from the Plugin Store or with Composer.

## Setup Alt Text Generator

1. Make sure the plugin is installed in your CraftCMS.
2. If you don’t have an AltTextLab account yet, create one [here](https://www.alttextlab.com/?utm_source=craftcms-directory&utm_medium=craftcms-setup-block).
3. Go to the [API Keys page](https://app.alttextlab.com/settings/api-keys?utm_source=craftcms-directory&utm_medium=craftcms-setup-block) and create a new API key by clicking **Create API Key**. Copy the generated key.
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

## Troubleshooting

- The plugin includes a **AltTextLab → Logs** page where you can view any errors related to alt text generation.
- Alt text generation for bulk operations is processed through Craft's queue system, so if generation seems to be taking a long time, check the **Queue Manager**.
- All errors should also be logged, review your `queue.log` files for more details.  

## Support & Feedback

If you encounter any issues, have feature requests, or need help getting started, feel free to reach out:

- **Website:** [https://www.alttextlab.com](https://www.alttextlab.com?utm_source=craftcms-directory&utm_medium=craftcms-support-block)
- **Support Email:** contact@alttextlab.com
- [Contact us](https://www.alttextlab.com/contact?utm_source=craftcms-directory&utm_medium=craftcms-support-block)
