# SSV File Manager
This is a plugin to let the members manage files in the frontend. This plugin connects to a DigitalOcean Stack and stores the files uploaded with this plugin there. The later

## Installation
1. Upload the plugin files to the `/wp-content/plugins/ssv-file-manager` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the SSV Options->Frontend Members screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)

## Frequently Asked Questions
### How do I request a feature?
The best way is to add an issue on GitHub (https://github.com/Moridrin/ssv-file-manager/issues). But you can also send an email to J.Berkvens@Moridrin.com (the lead developer).

### How do I report a bug?
The best way is to add an issue on GitHub (https://github.com/Moridrin/ssv-file-manager/issues). But you can also send an email to J.Berkvens@Moridrin.com (the lead developer).

### Where can I find the RoadMap?
The best way is to add an issue on GitHub (https://github.com/Moridrin/ssv-file-manager/issues). But you can also send an email to J.Berkvens@Moridrin.com (the lead developer).

## Changelog
### 1.0.0
* Connection with DigitalOcean Stack
* Customizable Icon Colors for Folder and File Icons
* Customizable Rights for Guest Users (not logged in)
* File Management
  * Upload Files
  * Edit Files
    * Currently you edit the full path, with this you can also move files
  * Delete Files
* Folder Management
  * Upload Folders (drag and drop)
    * This recreates the structure of the uploaded folder with all subfolders and uploads all files in the correct (sub)folders.
  * Delete Folders