# Media Entity GoogleDocs
This module provides GoogleDocs integration (i.e. media type
provider plugin) for [Media Entity Module](https://www.drupal.org/project/media_entity).

![media-entity-googledocs](_documentation/images/4-googledocs-media.jpg)

## Installation
1. Enable the media_entity and media_entity_googledocs module.
2. Go to `/admin/structure/media` and click 'Add media bundle' to create a new bundle.
3. Under **Type provider** select GoogleDocs.
4. Save the bundle.
5. Add a field to the bundle to store the googledocs source. (this should be a plain text field).
6. Edit the bundle again, and select the field created above as the **GoogleDocs source field**.

## Usage
[Check Usage Guide](_documentation/USAGE.md)

## Integration with Lightning Media
**Media Entity GoogleDocs** can be used with its companion module for Lightning Media. More information can be found at https://www.drupal.org/project/lightning_media_googledocs and https://github.com/dakkusingh/lightning_media_googledocs

## Project Code
* GitHub
[lightning_media_googledocs](https://github.com/dakkusingh/lightning_media_googledocs) | [media_entity_googledocs](https://github.com/dakkusingh/media_entity_googledocs)

* Drupal.org
[lightning_media_googledocs](https://www.drupal.org/project/lightning_media_googledocs) | [media_entity_googledocs](https://www.drupal.org/project/media_entity_googledocs)
